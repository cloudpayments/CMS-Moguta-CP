<?php

/*
	Plugin Name: Чек доставки
	Description: Плагин позволяет отправлять второй чек прихода при доставке заказа. Используется совместно с платежной системой Cloudpayments. Отправка второго чека возможна при следующих способах расчета: Предоплата, Предоплата 100%, Аванс.
	Author: CloudPayments
	Version: 1.0.0
 */

new receipt_delivered_cp;

class receipt_delivered_cp
{
    private static $pluginName = ''; // название плагина (соответствует названию папки)
    private static $path = '';//путь к плагину

    private static $curl; //CURL ресурс

    public function __construct()
    {
        mgAddAction('Models_Order_updateOrder', array(__CLASS__, 'receipt'), 1);//хук плагина
        self::$pluginName = PM::getFolderPlugin(__FILE__);//имя плагина
        self::$path = PLUGIN_DIR . self::$pluginName;//папка плагина
    }

    static function receipt($args)
    {
        $order = DB::query("SELECT * FROM `" . PREFIX . "order` WHERE `id` = " . $args['args'][0]['id']);
        $order = DB::fetchAssoc($order);
        $order_payment_id = $order['payment_id'];
        
        $cloudpayments = DB::query("SELECT * FROM `" . PREFIX . "payment` WHERE `name` = 'CloudPayments'");
        $cloudpayments = DB::fetchAssoc($cloudpayments);
        $cloudpayments_id = $cloudpayments['id'];
        
        $cloudpaymentsInfo = new Models_Order();
        $cloudpayments_settings = $cloudpaymentsInfo->getParamArray($cloudpayments_id, null, null);
        
        
        if ($args['args'][0]['status_id'] == $cloudpayments_settings[12]['value'] && $order_payment_id == $cloudpayments_id && $cloudpayments_settings[5]['value'] == true && 
        ($cloudpayments_settings[10]['value'] == 1 || $cloudpayments_settings[10]['value'] == 2 ||$cloudpayments_settings[10]['value'] == 3)) {

            $order['order_content'] = unserialize(stripslashes($order['order_content']));

            $order['phone'] = substr(preg_replace('~\D~', '', $order['phone']), 1);
    
            $receipt = array(
                'Items' => array(),
                'taxationSystem' => substr($cloudpayments_settings[7]['value'], 3),
                'calculationPlace'=>'www.'.$_SERVER['SERVER_NAME'],
                'email' => $order['user_email'],
                'phone' => $order['phone']
            );
            if (floatval($order['delivery_cost']) > 0) {
                $amount = floatval($order['delivery_cost']) + floatval($order['summ']);
            }
            else $amount = floatval($order['summ']);
            
            $kassa_method = 4;
            $Payment_sign = 'Income';
            $receipt['amounts']['advancePayment'] = $amount;
            
    
            $vat = substr($cloudpayments_settings[9]['value'], 4); //Удаляем vat_
            if ($vat == 'none') {
                $vat = '';
            }
            $vat_delivery = substr($cloudpayments_settings[10]['value'], 4); //Удаляем vat_
            if ($vat_delivery == 'none') {
                $vat_delivery = '';
            }
    
            foreach ($order['order_content'] as $item) {

                $tmp = explode(PHP_EOL, $item['name']);

                $item = array(
                    'label' => MG::textMore($tmp[0], 125),
                    'price' => floatval($item['price']),
                    'quantity' => floatval($item['count']),
                    'amount' => floatval($item['price']) * floatval($item['count']),
                    'vat' => $vat,
                    'method' => $kassa_method,
                    'object' => (float)$cloudpayments_settings[12]['value'],
                );

                $receipt['Items'][] = $item;
            }

            if (floatval($order['delivery_cost']) > 0) {
                $item = array(
                    'label' => 'Доставка',
                    'price' => floatval($order['delivery_cost']),
                    'quantity' => 1,
                    'amount' => floatval($order['delivery_cost']),
                    'vat' => $vat_delivery,
                    'method' => $kassa_method,
                    'object' => 4,
                );
            };
            $receipt['Items'][] = $item;
            
            $data = array(
                'Inn' => $cloudpayments_settings[6]['value'],
                'Type' => $Payment_sign,
                'CustomerReceipt' => $receipt,
                'InvoiceId' => $order['number'],
                'AccountId' => $order['user_email'],
            );
            
            if (!self::$curl) {
                $auth = $cloudpayments_settings[0]['value'] . ':' . $cloudpayments_settings[1]['value'];
                self::$curl = curl_init();
                curl_setopt(self::$curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt(self::$curl, CURLOPT_CONNECTTIMEOUT, 30);
                curl_setopt(self::$curl, CURLOPT_TIMEOUT, 30);
                curl_setopt(self::$curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                curl_setopt(self::$curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt(self::$curl, CURLOPT_USERPWD, $auth);
            }

            curl_setopt(self::$curl, CURLOPT_URL, 'https://api.cloudpayments.ru/kkt/receipt');
            curl_setopt(self::$curl, CURLOPT_HTTPHEADER, array(
                "content-type: application/json"
            ));
            curl_setopt(self::$curl, CURLOPT_POST, true);
            curl_setopt(self::$curl, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec(self::$curl);
        }
        return $args;
    }
}

?>