<?php
/**
 * Контроллер: Payment
 *
 * Класс Controllers_Payment предназначен для приема и обработки платежей.
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Payment extends BaseController {
    
  public $msg = "";

  function __construct() {
      
    $this->msg = "";
    $paymentID = $_GET['id'];
    $paymentStatus = $_GET['pay'];
    $_POST['url'] = URL::getUrl();
    $modelOrder = new Models_Order();
    if (!$paymentID) {
      $paymentID = $_REQUEST['id'];
    }    
    if (!$paymentStatus) {
      $paymentStatus = $_REQUEST['pay'];
    }
     //MG::loger($_REQUEST);
    $msg = '';
    switch ($paymentID) {
      case 1: //webmoney
        $msg = $this->webmoney($paymentID, $paymentStatus);
        break;
      case 2: //ЯндексДеньги    
        $msg = $this->yandex($paymentID, $paymentStatus);
        break;
      case 5: //robokassa
        $msg = $this->robokassa($paymentID, $paymentStatus);
        break;
      case 6: //qiwi
        $msg = $this->qiwi($paymentID, $paymentStatus);
        break;
      case 8: //interkassa
        $msg = $this->interkassa($paymentID, $paymentStatus);
        break;
      case 9: //PayAnyWay
        $msg = $this->payanyway($paymentID, $paymentStatus);
        break;
      case 10: //PayMaster
        $msg = $this->paymaster($paymentID, $paymentStatus);
        break;
      case 11: //alfabank
        $msg = $this->alfabank($paymentID, $paymentStatus);
        break;
      case 14: //Яндекс.Касса
        $msg = $this->yandexKassa($paymentID, $paymentStatus);
        break;
      case 15: //privat24
        $msg = $this->privat24($paymentID, $paymentStatus);
        break;
      case 16: //LiqPay
        $msg = $this->liqpay($paymentID, $paymentStatus);
        break;
      case 17: //Sberbank
        $msg = $this->sberbank($paymentID, $paymentStatus);
        break;
      case 18: //Tinkoff
        $msg = $this->tinkoff($paymentID, $paymentStatus);
        break;
      case 20: //ComePay
        $paymentStatus = $this->comepay($paymentID, $paymentStatus);
        $msg = $this->msg;
        break;
      case 21: //paykeeper
        $msg = $this->paykeeper($paymentID, $paymentStatus);
        break;
      case 22: //CloudPayments
        $msg = $this->cloudpayments($paymentID, $paymentStatus);
        break;
      case 24: //Новая Яндекс Касса
        $paymentStatus = $this->yandexKassaNew($paymentID);
        $msg = $this->msg;
        break;
      case 26: //Фри-касса
        $paymentStatus = $this->freeKassa($paymentID, $paymentStatus);
        $msg = $this->msg;
        break;
      case 27: //Мегакасса
        $msg = $this->megaKassa($paymentID, $paymentStatus);
        break;
       case 28: //Qiwi API
        $msg = $this->qiwiApi($paymentID, $paymentStatus);
        break;

      case 19: //PayPal
        $paymentStatus = $this->paypal($paymentID, $paymentStatus);
        $msg = $this->msg;
        break;

    }

    $this->data = array(
      'payment' => $paymentID, //id способа оплаты
      'status' => $paymentStatus, //статус ответа платежной системы (result, success, fail)
      'message' => $msg, //статус ответа платежной системы (result, success, fail)
    );
  }

  /**
   * Действие при оплате заказа.
   * Обновляет статус заказа на Оплачен, отправляет письма оповещения, генерирует хук.
   * @param array $args массив с результатом оплаты
   * @return array
   */
  public function actionWhenPayment($args) {
    $result = true;
    ob_start();

    $order = new Models_Order();
    if(method_exists($order, 'updateOrder')) {
      $order->updateOrder(array('id' => $args['paymentOrderId'], 'status_id' => 2, 'paided' => 1));
    }
    if(method_exists($order, 'sendMailOfPayed')) {
      $order->sendMailOfPayed($args['paymentOrderId'], $args['paymentAmount'], $args['paymentID']);
    }
    if(method_exists($order, 'sendLinkForElectro')) {
      $order->sendLinkForElectro($args['paymentOrderId']);
    }

    $content = ob_get_contents();
    ob_end_clean();

    // если в ходе работы метода допущен вывод контента, то записать в лог ошибку.
    if(!empty($content)) {
      MG::loger('ERROR PAYMENT: ' . $content);
    }

    return MG::createHook(__CLASS__ . "_" . __FUNCTION__, $result, $args);
  }

  /**
   * Проверка платежа через WebMoney.
   * @param int $paymentID ID способа оплаты
   * @param string $paymentStatus статус платежа
   * @return string
   */
  public function webmoney($paymentID, $paymentStatus) {
    $order = new Models_Order();

    if('success' == $paymentStatus) {
      if(empty($_POST['LMI_PAYMENT_NO'])) {
        echo "ERR: НЕКОРРЕКТНЫЕ ДАННЫЕ ЗАКАЗА";
          exit;
      }
    
      $orderInfo = $order->getOrder(" id = " . DB::quoteInt(intval($_POST['LMI_PAYMENT_NO']), 1));
      $msg = 'Вы успешно оплатили заказ №' . $orderInfo[$_POST['LMI_PAYMENT_NO']]['number']; 
      $msg .= $this->msg;
    } elseif('result' == $paymentStatus && count($_POST) > 1) {      
      $paymentAmount = trim($_POST['LMI_PAYMENT_AMOUNT']);
      //$paymentAmount = $paymentAmount*1;
      $paymentOrderId = trim($_POST['LMI_PAYMENT_NO']);
      if(!empty($paymentAmount) && !empty($paymentOrderId)) {
        $orderInfo = $order->getOrder(" id = " . DB::quoteInt(intval($paymentOrderId), 1) . " and summ+delivery_cost = " . DB::quoteFloat($paymentAmount));
      }

      $paymentInfo = $order->getParamArray($paymentID);
      $payeePurse = trim($paymentInfo[0]['value']);
      $secretKey = trim($paymentInfo[1]['value']);
      $alg = $paymentInfo[3]['value'];
      // предварительная проверка платежа
      if($_POST['LMI_PREREQUEST'] == 1) {
        $error = false;

        if(empty($orderInfo)) {
          echo "ERR: НЕКОРРЕКТНЫЕ ДАННЫЕ ЗАКАЗА";
          exit;
        }

        if(trim($_POST['LMI_PAYEE_PURSE']) != $payeePurse) {
          echo "ERR: НЕВЕРНЫЙ КОШЕЛЕК ПОЛУЧАТЕЛЯ " . $_POST['LMI_PAYEE_PURSE'];
          exit;
        }
        echo "YES";
        exit;
      } else {
        // проверка хэша, присвоение нового статуса заказу
        $chkstring = $_POST['LMI_PAYEE_PURSE'] .
          $_POST['LMI_PAYMENT_AMOUNT'] .
          $_POST['LMI_PAYMENT_NO'] .
          $_POST['LMI_MODE'] .
          $_POST["LMI_SYS_INVS_NO"] .
          $_POST["LMI_SYS_TRANS_NO"] .
          $_POST["LMI_SYS_TRANS_DATE"] .
          $secretKey .
          $_POST["LMI_PAYER_PURSE"] .
          $_POST["LMI_PAYER_WM"];
        
        $md5sum = strtoupper(hash($alg, $chkstring));

        if($_POST['LMI_HASH'] == $md5sum) {
          $this->actionWhenPayment(
            array(
              'paymentOrderId' => $paymentOrderId,
              'paymentAmount' => $paymentAmount,
              'paymentID' => $paymentID
            )
          );
          echo "YES";
          exit;
        } else {
          echo "ERR: Произошла ошибка или подмена параметров.";
          exit;
        }
      }
    } else {
      $msg = 'Оплата не удалась';
    }

    return $msg;
  }

  /**
   * Проверка платежа через paymaster.
   * @param int $paymentID ID способа оплаты
   * @param string $paymentStatus статус платежа
   * @return string
   */
  public function paymaster($paymentID, $paymentStatus) {
    $order = new Models_Order();
    $msg = '';

    if(empty($_POST['LMI_PAYMENT_NO'])) {
      echo "ERR: НЕКОРРЕКТНЫЕ ДАННЫЕ ЗАКАЗА";
      exit;
    }

    if('success' == $paymentStatus) {
      $orderInfo = $order->getOrder(" id = " . DB::quoteInt(intval($_POST['LMI_PAYMENT_NO']), 1));
      $msg = 'Вы успешно оплатили заказ №' . $orderInfo[$_POST['LMI_PAYMENT_NO']]['number']; 
      $msg .= $this->msg;
    } elseif('result' == $paymentStatus && $_POST) {
      $paymentAmount = trim($_POST['LMI_PAYMENT_AMOUNT']);
      //$paymentAmount = $paymentAmount*1;
      $paymentOrderId = trim($_POST['LMI_PAYMENT_NO']);
      if(!empty($paymentAmount) && !empty($paymentOrderId)) {
        $orderInfo = $order->getOrder(" id = " . DB::quoteInt(intval($paymentOrderId), 1) . " and summ+delivery_cost = " . DB::quoteFloat($paymentAmount));
      }

      $paymentInfo = $order->getParamArray($paymentID);
      $payeePurse = trim($paymentInfo[0]['value']);
      $secretKey = trim($paymentInfo[1]['value']);
      $alg =  $paymentInfo[2]['value'];
      // предварительная проверка платежа
      if($_POST['LMI_PREREQUEST'] == 1) {
        $error = false;

        if(empty($orderInfo)) {
          echo "ERR: НЕКОРРЕКТНЫЕ ДАННЫЕ ЗАКАЗА";
          exit;
        }

        echo "YES";
        exit;
      } else {

        $chkstring = $_POST['LMI_MERCHANT_ID'] . ";" .
          $_POST['LMI_PAYMENT_NO'] . ";" .
          $_POST['LMI_SYS_PAYMENT_ID'] . ";" .
          $_POST['LMI_SYS_PAYMENT_DATE'] . ";" .
          $_POST['LMI_PAYMENT_AMOUNT'] . ";" .
          $_POST['LMI_CURRENCY'] . ";" .
          $_POST['LMI_PAID_AMOUNT'] . ";" .
          $_POST['LMI_PAID_CURRENCY'] . ";" .
          $_POST['LMI_PAYMENT_SYSTEM'] . ";" .
          $_POST['LMI_SIM_MODE'] . ";" .
          $secretKey;

        $md5sum = base64_encode(hash($alg,$chkstring, true));

        if($_POST['LMI_HASH'] == $md5sum) {

          $this->actionWhenPayment(
            array(
              'paymentOrderId' => $paymentOrderId,
              'paymentAmount' => $paymentAmount,
              'paymentID' => $paymentID
            )
          );
          echo "YES";
          exit;
        } else {
          echo "ERR: Произошла ошибка или подмена параметров.";
          exit;
        }
        $msg = 'Оплата не удалась';
      }
    }

    return $msg;
  }

  /**
   * Проверка платежа через ROBOKASSA.
   * @param int $paymentID ID способа оплаты
   * @param string $paymentStatus статус платежа
   * @return string
   */
  public function robokassa($paymentID, $paymentStatus) {
    $order = new Models_Order();
    $msg = '';
    if('success' == $paymentStatus) {
      if(!empty($_POST['InvId'])) {
        $orderInfo = $order->getOrder(" id = " . DB::quoteInt(intval($_POST['InvId']), 1));
        $msg = 'Вы успешно оплатили заказ №' . $orderInfo[$_POST['InvId']]['number']; 
      } else {
        $msg = 'Не указан номер заказа!';
      }
      
      $msg .= $this->msg;
    } elseif('result' == $paymentStatus && count($_POST) > 1) {    
      $paymentAmount = trim($_POST['OutSum']);
      $paymentOrderId = trim($_POST['InvId']);
      if(!empty($paymentAmount) && !empty($paymentOrderId)) {
        $orderInfo = $order->getOrder(" id = " . DB::quoteInt(intval($paymentOrderId), 1) . " and summ+delivery_cost = " . DB::quoteFloat($paymentAmount));
        $paymentInfo = $order->getParamArray($paymentID, $orderInfo['id'], $orderInfo['summ']+$orderInfo['delivery_cost']);
      } else {
        echo "ERR: НЕКОРРЕКТНЫЕ ДАННЫЕ ЗАКАЗА";
        exit;
      }
      // предварительная проверка платежа
      if(empty($orderInfo) || empty($paymentInfo)) {
        echo "ERR: НЕКОРРЕКТНЫЕ ДАННЫЕ ЗАКАЗА";
        exit;
      }

      $sMerchantPass2 = trim($paymentInfo[2]['value']);
      $alg = $paymentInfo[3]['value'];
      $sSignatureValue = $paymentAmount . ':' . $paymentOrderId . ':' . $sMerchantPass2;
      $md5sum = strtoupper(hash($alg,$sSignatureValue));

      if($_POST['SignatureValue'] == $md5sum) {
        $this->actionWhenPayment(
          array(
            'paymentOrderId' => $paymentOrderId,
            'paymentAmount' => $paymentAmount,
            'paymentID' => $paymentID
          )
        );

        echo "OK" . $paymentOrderId;
        exit;
      }
    } else {
      $msg = 'Оплата не удалась';
    }

    return $msg;
  }

  /**
   * Проверка платежа через QIWI.
   * @param int $paymentID ID способа оплаты
   * @param string $paymentStatus статус платежа
   * @return string
   */
  public function qiwi($paymentID, $paymentStatus) {
    $order = new Models_Order();
    $msg = '';
    if('success' == $paymentStatus) {
      if (!empty($_GET['order'])) {
        $orderInfo = $order->getOrder(" id = " . DB::quoteInt(intval($_GET['order']), 1));
        $msg = 'Вы успешно оплатили заказ №' . $orderInfo[$_GET['order']]['number'];
      } else {
        $msg = 'Не указан номер заказа!';
      }
      $msg .= $this->msg;
    } elseif('result' == $paymentStatus && $i = file_get_contents('php://input')) {

      $l = array('/<login>(.*)?<\/login>/', '/<password>(.*)?<\/password>/');
      $s = array('/<txn>(.*)?<\/txn>/', '/<status>(.*)?<\/status>/');

      preg_match($l[0], $i, $m1);
      preg_match($l[1], $i, $m2);

      preg_match($s[0], $i, $m3);
      preg_match($s[1], $i, $m4);

      $paymentOrderId = $m3[1];

      $statusQiwi = $m4[1];


      if(!empty($paymentOrderId)) {
        $orderInfo = $order->getOrder(" id = " . DB::quoteInt(intval($paymentOrderId), 1));
      } else {
        $orderInfo = NULL;
        echo "Ошибка обработки";
        exit();
      }

      $paymentInfo = $order->getParamArray($paymentID, $paymentOrderId, $orderInfo[$paymentOrderId]['summ']);
      $password = trim($paymentInfo[1]['value']);
      $alg = $paymentInfo[2]['value'];
      $parseLog =
        ' status=' . $statusQiwi .
        ' paymentOrderId=' . $paymentOrderId .
        ' paymentID=' . $paymentID .
        ' summ=' . $orderInfo[$paymentOrderId]['summ'];

      // если заказа не существует то отправляем код 150
      if(empty($orderInfo)) {
        $resultCode = 300;
      } else {

        $hash = strtoupper(hash($alg,$m3[1] . strtoupper(hash($alg,$password))));

        if($hash !== $m2[1]) { //сравнение хешей
          $resultCode = 150;
        } else {
          if($statusQiwi == 60) {// заказ оплачен         
            $this->actionWhenPayment(
              array(
                'paymentOrderId' => $paymentOrderId,
                'paymentAmount' => $orderInfo[$paymentOrderId]['summ'],
                'paymentID' => $paymentID
              )
            );
          }
          $resultCode = 0; // все прошло успешно оправляем "0"
        }
      }
      header('content-type: text/xml; charset=UTF-8');
      echo '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://client.ishop.mw.ru/"><SOAP-ENV:Body><ns1:updateBillResponse><updateBillResult>' . $resultCode . '</updateBillResult></ns1:updateBillResponse></SOAP-ENV:Body></SOAP-ENV:Envelope>';
      exit;
    } else {
      $msg = 'Оплата не удалась';
    }

    return $msg;
  }

  /**
   * Проверка платежа через Interkassa.
   * @param int $paymentID ID способа оплаты
   * @param string $paymentStatus статус платежа
   * @return string
   */
  public function interkassa($paymentID, $paymentStatus) {
    $order = new Models_Order();
    $msg = '';
    if('success' == $paymentStatus) {
      if (!empty($_POST['ik_pm_no'])) {
      $orderInfo = $order->getOrder(" id = " . DB::quoteInt(intval($_POST['ik_pm_no']), 1));
      $msg = 'Вы успешно оплатили заказ №' . $orderInfo[$_POST['ik_pm_no']]['number'];
      } else {
        $msg = 'Не указан номер заказа!';
      }
      $msg .= $this->msg;
    } elseif('result' == $paymentStatus && count($_POST) > 1) {
  
      $paymentAmount = trim($_POST['ik_am']);
      $paymentOrderId = trim($_POST['ik_pm_no']);
      if(!empty($paymentAmount) && !empty($paymentOrderId)) {
        $orderInfo = $order->getOrder(" id = " . DB::quoteInt(intval($paymentOrderId), 1) . " and summ+delivery_cost = " . DB::quoteFloat($paymentAmount));
      }
      // предварительная проверка платежа
      if(empty($orderInfo)) {
        echo "ERR: НЕКОРРЕКТНЫЕ ДАННЫЕ ЗАКАЗА";
        exit;
      }

      $paymentInfo = $order->getParamArray($paymentID);
      $testKey = '*****';
      $normKey = trim($paymentInfo[1]['value']);
      $alg = $paymentInfo[3]['value'];
      $signString = $_POST['ik_co_id'];
      $key = $normKey;
      if(!empty($_POST['ik_pw_via']) && $_POST['ik_pw_via'] == 'test_interkassa_test_xts') {
        $key = $testKey;
      }

      $dataSet = $_POST;
      unset($dataSet['url']);
      unset($dataSet['ik_sign']);
      ksort($dataSet, SORT_STRING); // сортируем по ключам в алфавитном порядке элементы массива 
      array_push($dataSet, $key); // добавляем в конец массива "секретный ключ"    
      $signString = implode(':', $dataSet); // конкатенируем значения через символ ":" 
      $sign = base64_encode(hash($alg,$signString, true)); // берем MD5 хэш в бинарном виде по

      if($sign == $_POST['ik_sign']) {
        $this->actionWhenPayment(
          array(
            'paymentOrderId' => $paymentOrderId,
            'paymentAmount' => $orderInfo[$paymentOrderId]['summ'],
            'paymentID' => $paymentID
          )
        );
        echo "200 OK";
        exit;
      } else {
        echo "Подписи не совпадают!";
        exit;
      }
    } else {
      $msg = 'Оплата не удалась';
    }
    return $msg;
  }

  /**
   * Проверка платежа через payanyway.
   * @param int $paymentID ID способа оплаты
   * @param string $paymentStatus статус платежа
   * @return string
   */
  public function payanyway($paymentID, $paymentStatus) {
    $order = new Models_Order();
    $msg = '';
    if('success' == $paymentStatus) {
      $paymentOrderId = trim(URL::getQueryParametr('MNT_TRANSACTION_ID'));
      if (!empty($paymentOrderId)) {
        $orderInfo = $order->getOrder(" id = " . DB::quoteInt(intval($paymentOrderId), 1));
        $msg = 'Вы успешно оплатили заказ №' . $orderInfo[$paymentOrderId]['number'];
      } else {
        $msg = 'Не указан номер заказа!';
      }
      $msg .= $this->msg;
            
      $this->actionWhenPayment(
        array(
          'paymentOrderId' => $paymentOrderId,
          'paymentAmount' => $orderInfo[$paymentOrderId]['summ'] + $orderInfo[$paymentOrderId]['delivery_cost'],
          'paymentID' => $paymentID
        )
      );
    } elseif('result' == $paymentStatus && count($_POST) > 1) {
      $paymentAmount = trim($_POST['MNT_AMOUNT']);
      $paymentOrderId = trim($_POST['MNT_TRANSACTION_ID']);

      if(!empty($paymentAmount) && !empty($paymentOrderId)) {
        $orderInfo = $order->getOrder(" id = " . DB::quoteInt(intval($paymentOrderId), 1) . " and summ+delivery_cost = " . DB::quoteFloat($paymentAmount));
        $paymentInfo = $order->getParamArray($paymentID, $paymentOrderId, $orderInfo[$paymentOrderId]['summ'] + $orderInfo[$paymentOrderId]['delivery_cost']);
      } else {
        echo "FAIL";
        exit;
      }

      // предварительная проверка платежа
      if(empty($orderInfo)) {
        echo "FAIL";
        exit;
      }

      $testmode = 0;

      if($paymentInfo[2]['value'] == 'true') {
        $testmode = 1;
      }

      $account = trim($paymentInfo[0]['value']);
      $securityCode = trim($paymentInfo[1]['value']);

      // предварительная проверка платежа обработка команды CHECK
      if($_POST['MNT_COMMAND'] == 'CHECK') {
        $summ = sprintf("%01.2f", $orderInfo[$paymentOrderId]['summ'] + $orderInfo[$paymentOrderId]['delivery_cost']);
        $currency = (MG::getSetting('currencyShopIso') == "RUR") ? "RUB" : MG::getSetting('currencyShopIso');
        $alg = $paymentInfo[3]['value'];
        $sign = hash($alg, $_POST['MNT_COMMAND'] . $account . $paymentOrderId . $summ . $currency . $testmode . $securityCode);
        
        if($sign == $_POST['MNT_SIGNATURE']) {
          $signNew = hash($alg, '402' . $account . $paymentOrderId . $securityCode);
          $responseXml = '<?xml version="1.0" encoding="UTF-8"?>
            <MNT_RESPONSE>
            <MNT_ID>' . $account . '</MNT_ID>
            <MNT_TRANSACTION_ID>' . $paymentOrderId . '</MNT_TRANSACTION_ID>
            <MNT_RESULT_CODE>402</MNT_RESULT_CODE>
            <MNT_DESCRIPTION>Оплата заказа ' . $paymentOrderId . '</MNT_DESCRIPTION>
            <MNT_AMOUNT>' . ($orderInfo[$paymentOrderId]['summ'] + $orderInfo[$paymentOrderId]['delivery_cost']) . '</MNT_AMOUNT>
            <MNT_SIGNATURE>' . $signNew . '</MNT_SIGNATURE>
            </MNT_RESPONSE>';
          header("Content-type: text/xml");
          echo $responseXml;
        } else {
          echo "Подписи не совпадают!";
        }
        
        exit;
      } elseif(isset($_POST['MNT_OPERATION_ID'])) {
        $summ = sprintf("%01.2f", $orderInfo[$paymentOrderId]['summ'] + $orderInfo[$paymentOrderId]['delivery_cost']);
        $currency = (MG::getSetting('currencyShopIso') == "RUR") ? "RUB" : MG::getSetting('currencyShopIso');
        $alg = $paymentInfo[3]['value'];
        $sign = hash($alg, $_POST['MNT_COMMAND'] . $account . $paymentOrderId . $_POST['MNT_OPERATION_ID'] . $summ . $currency . $testmode . $securityCode);

        if($sign == $_POST['MNT_SIGNATURE']) {
          $signNew = hash($alg, '200' . $account . $paymentOrderId . $securityCode);
          $responseXml = '<?xml version="1.0" encoding="UTF-8"?>
            <MNT_RESPONSE>
            <MNT_ID>' . $account . '</MNT_ID>
            <MNT_TRANSACTION_ID>' . $paymentOrderId . '</MNT_TRANSACTION_ID>
            <MNT_RESULT_CODE>200</MNT_RESULT_CODE>
            <MNT_SIGNATURE>' . $signNew . '</MNT_SIGNATURE>
            </MNT_RESPONSE>';

          header("Content-type: text/xml");
          echo $responseXml;
        } else {
          echo "Подписи не совпадают!";
        }
        
        exit;
      }
    } else {
      $msg = 'Оплата не удалась';
    }
    
    return $msg;
  }

  /**
   * Проверка платежа через Yandex.
   * @param int $paymentID ID способа оплаты
   * @param string $paymentStatus статус платежа
   * @return string
   */
  public function yandex($paymentID, $paymentStatus) {
    $order = new Models_Order();
    $msg = '';
    if('success' == $paymentStatus) {
      if (!empty($_POST['label'])) {
        $orderInfo = $order->getOrder(" id = " . DB::quoteInt(intval($_POST['label']), 1));
        $msg = 'Вы успешно оплатили заказ №' . $orderInfo[$_POST['label']]['number'];
      } else {
        $msg = 'Не указан номер заказа!';
      }
      $msg .= $this->msg;
    } elseif('result' == $paymentStatus && count($_POST) > 2) {     
      $paymentAmount = trim($_POST['withdraw_amount']);
      $paymentOrderId = trim($_POST['label']);
      if(!empty($paymentAmount) && !empty($paymentOrderId)) {
        $orderInfo = $order->getOrder(" id = " . DB::quoteInt(intval($paymentOrderId), 1) . " and summ+delivery_cost = "
          . DB::quoteFloat($paymentAmount));
      }
      // предварительная проверка платежа
      if(empty($orderInfo)) {
        echo "ERR: НЕКОРРЕКТНЫЕ ДАННЫЕ ЗАКАЗА";
        exit;
      }

      $paymentInfo = $order->getParamArray($paymentID);
      $secret = trim($paymentInfo[1]['value']);
      $alg = $paymentInfo[3]['value'];
      $pre_sha = $_POST['notification_type'] . '&' .
        $_POST['operation_id'] . '&' .
        $_POST['amount'] . '&' .
        $_POST['currency'] . '&' .
        $_POST['datetime'] . '&' .
        $_POST['sender'] . '&' .
        $_POST['codepro'] . '&' .
        $secret . '&' .
        $_POST['label'];

      $sha = hash($alg,$pre_sha);
      if($sha == $_POST['sha1_hash']) {
        $this->actionWhenPayment(
          array(
            'paymentOrderId' => $paymentOrderId,
            'paymentAmount' => $orderInfo[$paymentOrderId]['summ'],
            'paymentID' => $paymentID
          )
        );
        echo "0";
        exit;
      } else {
        echo "1";
        exit;
      }
    } else {
      $msg = 'Оплата не удалась';
    }

    return $msg;
  }

  /**
   * Проверка платежа через Яндекс.Кассу.
   * @param int $paymentID ID способа оплаты
   * @param string $paymentStatus статус платежа
   */
  public function yandexKassa($paymentID, $paymentStatus) {
    $order = new Models_Order();
    $action = URL::getQueryParametr('action');
    $orderNumber = URL::getQueryParametr('orderNumber');
    $orderId = URL::getQueryParametr('orderMId');

    if (empty($orderNumber)) {
      echo 'Не указан номер заказа!';
      exit();
    }
    
    if($paymentStatus == 'success') {
      //$orderInfo = $order->getOrder(" number = " . DB::quote($orderNumber));
      $msg = 'Вы успешно оплатили заказ №' . $orderNumber;
      $msg .= $this->msg;
      return $msg;
    } elseif($paymentStatus == 'fail') {
      //$orderInfo = $order->getOrder(" number = " . DB::quote($orderNumber));
      $msg = 'При попытке оплаты заказа №'.$orderNumber.' произошла ошибка.<br />Пожалуйста, попробуйте позже или используйте другой способ оплаты';
      $msg .= $this->msg;
      return $msg;
    }
    
    $error = false;
    
    $orderSumAmount = URL::getQueryParametr('orderSumAmount');
    $orderSumCurrencyPaycash = URL::getQueryParametr('orderSumCurrencyPaycash');
    $orderSumBankPaycash = URL::getQueryParametr('orderSumBankPaycash');
    $shopId = URL::getQueryParametr('shopId');
    $invoiceId = URL::getQueryParametr('invoiceId');
    $customerNumber = URL::getQueryParametr('customerNumber');
    $key = URL::getQueryParametr('md5');
    
    $responseXml = '<?xml version="1.0" encoding="UTF-8"?> ';
    
    if($action == 'paymentAviso') {
      $responseXml .= '<paymentAvisoResponse ';
    } else {
      $responseXml .= '<checkOrderResponse ';
    }
    
    $responseXml .= 'performedDatetime="'.date('c').'" ';
    
    if(!empty($orderSumAmount) && !empty($orderNumber) && !empty($orderId)) {
      $orderInfo = $order->getOrder(" number = " . DB::quote($orderNumber) . " and summ+delivery_cost = " . DB::quoteFloat($orderSumAmount));
    } else {
      $error = true;
      $responseXml .= 'code="200"
        message="не пришла сумма или номер"';
    }
    
    //action;orderSumAmount;orderSumCurrencyPaycash;orderSumBankPaycash;shopId;invoiceId;customerNumber;shopPassword 
    if(!empty($orderInfo)) {
      $paymentInfo = $order->getParamArray($paymentID);
      $shopPassword = trim($paymentInfo[3]['value']);
      $alg= $paymentInfo[4]['value'];

      $hash = strtoupper(hash($alg,$action.';'.$orderSumAmount.';'.$orderSumCurrencyPaycash.';'.$orderSumBankPaycash.';'.$shopId.';'.$invoiceId.';'.$customerNumber.';'.$shopPassword));
      
      if($action == 'checkOrder') {
        if($hash == $key) {
          $responseXml .= 'code="0" ';
        } else {
          $responseXml .= 'code="1" ';
        }
      } elseif($action == 'paymentAviso') {
        if($hash == $key) {
          $responseXml .= 'code="0" ';
        } else {
          $responseXml .= 'code="1" paymentAviso ';
        }
        
        if($orderInfo[$orderId]['status_id']!=2 && $orderInfo[$orderId]['status_id']!=4 && $orderInfo[$orderId]['status_id']!=5) {
          $orderInfo = $order->getOrder(" number = " . DB::quote($orderNumber));
          $this->actionWhenPayment(
            array(
              'paymentOrderId' => $orderId,
              'paymentAmount' => $orderInfo[$orderId]['summ'],
              'paymentID' => $paymentID
            )
          );
        }
      } else {
        $responseXml .= 'code="200"
          message="Неизвестное действие"';
      } 
    } elseif(!$error) {
      $responseXml .= '
        code="200"
        message="Указаны неверные параметры заказа"';
    }
    
    $responseXml .= '
      invoiceId="'.$invoiceId.'" 
      shopId="'.$shopId.'" />';

    header('content-type: text/xml; charset=UTF-8');
    echo $responseXml;
    exit;
  }


  /**
   * Проверка платежа через AlfaBank.
   * @param int $paymentID ID способа оплаты
   * @param string $paymentStatus статус платежа
   * @return string
   */
  public function alfabank($paymentID, $paymentStatus) {
    $order = new Models_Order();
    $msg = '';
    if('result' == $paymentStatus && isset($_POST)) {     
      // если пользователь вернулся на страницу после оплаты, проверяем статус заказа
      if(isset($_REQUEST['orderId'])) {
        $paymentInfo = $order->getParamArray($paymentID, null, null);
   
        $serverUrl = (empty($paymentInfo[2]['value'])) 
                ? "https://engine.paymentgate.ru/payment/rest" : $paymentInfo[2]['value'];       

        $jsondata = file_get_contents($serverUrl.'/getOrderStatusExtended.do?language=ru&orderId='
          . $_REQUEST['orderId'] . '&userName=' . urlencode(trim($paymentInfo[0]['value'])) . '&password='
          . urlencode(trim($paymentInfo[1]['value'])));
        $obj = json_decode($jsondata);
     

        // приводим сумму заказа к нормальному виду
        $obj->amount = substr($obj->amount, 0, - 2) . "." . substr($obj->amount, -2);

        // приводим номер заказа к нормальному виду
        $orderNumber = explode('/', $obj->orderNumber);
        $obj->orderNumber = $orderNumber[0];

        $paymentAmount = trim($obj->amount);
        $paymentOrderId = trim($obj->orderNumber);

        // проверяем имеется ли в базе заказ с такими параметрами
        if(!empty($paymentAmount) && !empty($paymentOrderId)) {
          $orderInfo = $order->getOrder(" id = " . DB::quoteInt(intval($paymentOrderId), 1) . " and summ+delivery_cost = "
            . DB::quoteFloat($paymentAmount));
        }

        // если заказа с таким номером и стоимостью нет, то возвращаем ошибку
        if(empty($orderInfo)) {
          echo "ERR: НЕКОРРЕКТНЫЕ ДАННЫЕ (Заказа с таким номером не существует)";
          exit;
        }

        // если заказ есть и он успешно оплачен в банке
        if($obj->errorCode == 0 && $obj->actionCode==0) {
          // высылаем письма админу и пользователю об успешной оплате заказа, 
      // только если его действующий статус не равен "оплачен" или "выполнен" или "отменен"   
      if($orderInfo[$paymentOrderId]['status_id']!=2 && $orderInfo[$paymentOrderId]['status_id']!=4 && $orderInfo[$paymentOrderId]['status_id']!=5) {
        $this->actionWhenPayment(
        array(
          'paymentOrderId' => $paymentOrderId,
          'paymentAmount' => $orderInfo[$paymentOrderId]['summ'],
          'paymentID' => $paymentID
        )
        );
      }
      $msg = 'Вы успешно оплатили заказ №' . $orderInfo[$paymentOrderId]['number'];
      $msg .= $this->msg;
    } else {
      $msg = $obj->actionCodeDescription;
    }
    
      } else {
        //Запрос в альфабанк на формирование ссылки для перенаправления клиента к платежной форме
        if(!empty($_POST['paymentAlfaBank'])) {
          $paymentAmount = trim($_POST['amount']);
          $paymentOrderId = trim($_POST['orderNumber']);
          if(!empty($paymentAmount) && !empty($paymentOrderId)) {
            $orderInfo = $order->getOrder(" id = " . DB::quoteInt(intval($paymentOrderId), 1) . " and summ+delivery_cost = " . DB::quoteFloat($paymentAmount));
          }
          // предварительная проверка платежа
          if(empty($orderInfo)) {
            echo "ERR: НЕКОРРЕКТНЫЕ ДАННЫЕ ЗАКАЗА";
            exit;
          }

          $paymentInfo = $order->getParamArray($paymentID);
          $_POST['orderNumber'] = $_POST['orderNumber'] . '/' . time();
          $_POST['userName'] = urlencode(trim($paymentInfo[0]['value']));
          $_POST['password'] = urlencode(trim($paymentInfo[1]['value']));
          $_POST['amount'] = number_format($_POST['amount'], 2, '', '');
          $serverUrl = (empty($paymentInfo[2]['value'])) 
                ? "https://engine.paymentgate.ru/payment/rest" : $paymentInfo[2]['value'];
          $jsondata = file_get_contents($serverUrl.'/register.do?amount=' . $_POST['amount'] . '&currency='
            . $_POST['currency'] . '&language=' . $_POST['language'] . '&orderNumber=' . $_POST['orderNumber']
            . '&returnUrl=' . urlencode($_POST['returnUrl']) . '&userName=' . $_POST['userName'] . '&password='
            . $_POST['password']. '&description=' . $_POST['description']);
       

          $obj = json_decode($jsondata);
    
          // если произошла ошибка
          if(!empty($obj->errorCode)) {
            echo "ERR: " . $obj->errorMessage;
            exit;
          }

          // если ссылка сформированна, то отправляем клиента в альфабанк
          if(!empty($obj->orderId) && !empty($obj->formUrl)) {
            header('Location: ' . $obj->formUrl);
          }
          echo "ERR: не удалось получить ответ с сервера эквайринга!";
          exit;
        }
      }
    }
    return $msg;
  }
    
  /**
   * Проверка платежа через liqpay.
   * @param int $paymentID ID способа оплаты
   * @param string $paymentStatus статус платежа
   * @return string
   */
  private function liqpay($paymentID, $paymentStatus) {
    if (!empty($_POST['data'])) {
      $data = json_decode(base64_decode($_POST['data']));
      $orderId = URL::getQueryParametr('order_id');
    } else {
      $orderId = 0;
    }

    if(intval($orderId) > 0) {
      $orderId = intval($orderId);
      $order = new Models_Order(); 
      $orderInfo = $order->getOrder(" id = " . DB::quoteInt($orderId, 1));
      
      if(!empty($orderInfo)) {
        if(in_array($orderInfo[$orderId]['status_id'], array(2,5))) {
          $msg = 'Вы успешно оплатили заказ №' . $orderInfo[$orderId]['number'];
          $msg .= $this->msg;
          $pay = 'success';
        } else {
          $msg = 'Неудалось произвести оплату заказа №' . $orderInfo[$orderId]['number'].'. Используйте другой способ оплаты, или попробуте позже.';
          $pay = 'fail';    
        }
      } else {
        $msg = 'Заказа, с указанным идентификатором не существует с системе';       
        $pay = 'fail';
      }      

      if(empty($paymentStatus)) {
        MG::redirect(URL::getUri().'&pay='.$pay);
      }
      
      return $msg;
    }    
    
    if('result' == $paymentStatus && count($_POST) > 1) {
      
      if(empty($_POST['data']) || empty($_POST['signature'])) {
        $msg = "Неверный ответа от сервиса оплаты";
        return $msg;
      }
      
      if($data->status == 'failure') {
        $msg = 'Неуспешный платеж';
        return $msg;
      }
      
      if($data->status == 'error') {
        $msg = 'Неуспешный платеж. Некорректно заполнены данные';
        return $msg;
      }
      
      if($data->status == 'reversed') {
        $msg = 'Платеж возвращен';
        return $msg;
      }
      
      $order = new Models_Order();              
      $received_public_key = $data->public_key;
      $paymentOrderId = $data->order_id;
      $orderInfo = $order->getOrder(" id = " . DB::quoteInt(intval($paymentOrderId), 1));
      
      if(empty($orderInfo)) {
        $msg = 'Заказа, с указанным идентификатором не существует в системе';
        return $msg;
      }
      
      $paymentInfo = $order->getParamArray($paymentID, $paymentOrderId, $orderInfo[$paymentOrderId]['summ']);
      $publicKey = trim($paymentInfo[0]['value']);
      $privateKey = trim($paymentInfo[1]['value']);
      $sign = base64_encode(sha1($privateKey.$_POST['data'].$privateKey, 1));
      $paymentAmount = $data->amount;
      
      if($sign != $_POST['signature'] || $publicKey != $received_public_key) {
        $msg = "Не совпадает подпись или ключ доступа";
        return $msg;
      }else if($data->status == 'success') {
        $this->actionWhenPayment(
          array(
            'paymentOrderId' => $paymentOrderId,
            'paymentAmount' => $paymentAmount,
            'paymentID' => $paymentID
          )
        );
        
        $msg = 'Вы успешно оплатили заказ №' . $orderInfo[$paymentOrderId]['id'];      
        $msg .= $this->msg;
      } else {
        $msg = 'Во время оплаты произошла ошибка.';
      }
    } else {
      $msg = "Не верный ответа от сервиса оплаты";        
    }
    
    return $msg;
  }
  
  /**
   * Проверка платежа через privat24.
   * @param int $paymentID ID способа оплаты
   * @param string $paymentStatus статус платежа
   * @return string
   */
  public function privat24($paymentID, $paymentStatus) {
    $order = new Models_Order();
    
    if('result' == $paymentStatus && !empty($_POST['payment'])) {
      $payment = $_POST['payment'];

      if($payment) {
        $payment_array = array();
        parse_str($payment, $payment_array);

        $state = trim($payment_array['state']);
        $paymentOrderId = trim($payment_array['order']);
        $orderNumber = trim($payment_array['ext_details']);
        $paymentAmount = trim($payment_array['amt']);

        switch($state) {
          case 'not found':
            $msg = "Платеж не найден";
            return $msg;
            break;
          case 'fail':
            $msg =  "Ошибка оплаты";
            return $msg;
            break;
          case 'incomplete':
            $msg = "Пользователь не подтвердил оплату";
            return $msg;
            break;
          case 'wait':
            $msg = "Платеж в ожидании";
            return $msg;
            break;
        }
        
        if(empty($paymentOrderId)) {
          $msg = "Оплата не удалась";
          return $msg;
        }

        if(!empty($paymentAmount) && !empty($paymentOrderId)) {
          $orderInfo = $order->getOrder(" id = " . DB::quoteInt(intval($paymentOrderId), 1));
          $paymentInfo = $order->getParamArray($paymentID, $paymentOrderId, $orderInfo[$paymentOrderId]['summ']);
        } else {
          $msg = "ERR: НЕКОРРЕКТНЫЕ ДАННЫЕ ЗАКАЗА";
          return $msg;
        }
  
        if(empty($orderInfo) || empty($paymentInfo)) {
          $msg = "ERR: НЕКОРРЕКТНЫЕ ДАННЫЕ ЗАКАЗА";
          return $msg;
        }

        $merchant = trim($paymentInfo[0]['value']);
        $pass = trim($paymentInfo[1]['value']);

        $amt = round($orderInfo[$paymentOrderId]['summ'], 2) + round($orderInfo[$paymentOrderId]['delivery_cost'], 2);
        $payment = 'amt='.$amt.'&ccy=UAH&details=заказ на '.SITE.'&ext_details='.$orderNumber.'&pay_way=privat24&order='.$paymentOrderId.'&merchant='.$merchant;
        $signature = sha1(md5($payment.$pass));

        $paymentSignatureString = 'amt=' . round($payment_array['amt'], 2) . '&ccy=' . $payment_array['ccy'] . '&details=' .  $payment_array['details'] . '&ext_details=' . $payment_array['ext_details'] . '&pay_way=' . $payment_array['pay_way'] . '&order=' . $payment_array['order'] . '&merchant=' . $payment_array['merchant'];
        $paymentSignature = sha1(md5($paymentSignatureString.$pass));

        if($paymentSignature !== $signature) {
          $msg = "Подписи не совпадают!";
           return $msg;
        }

        $this->actionWhenPayment(
          array(
            'paymentOrderId' => $paymentOrderId,
            'paymentAmount' => $paymentAmount,
            'paymentID' => $paymentID
          )
        );

        $msg = 'Вы успешно оплатили заказ №' . $orderInfo[$paymentOrderId]['id'];      
        $msg .= $this->msg;

      } else {
        $msg = 'Оплата не удалась';
      }
    } else {
      $msg = 'Оплата не удалась';
    }
    return $msg;
  }
  /**
   * Проверка платежа через Сбербанк.
   * @param int $paymentID ID способа оплаты
   * @param string $paymentStatus статус платежа
   * @return string
   */
  public function sberbank($paymentID, $paymentStatus) {
    $msg = '';
    if('result' == $paymentStatus && isset($_POST)) {
      $order = new Models_Order();
      $paymentInfo = $order->getParamArray($paymentID, null, null);
      $serverUrl = (empty($paymentInfo[2]['value'])) 
              ? "https://3dsec.sberbank.ru" : $paymentInfo[2]['value'];
      $userName = urlencode(trim($paymentInfo[0]['value']));
      $password = urlencode(trim($paymentInfo[1]['value']));

      if(!empty($_POST['paymentSberbank'])) {
        $paymentAmount = trim($_POST['amount']);
        $paymentOrderId = trim($_POST['orderNumber']);

        if(!empty($paymentAmount) && !empty($paymentOrderId)) {
          $orderInfo = $order->getOrder(" id = " . DB::quoteInt($paymentOrderId, 1)
              . " and ROUND(summ+delivery_cost, 2) = " . DB::quoteFloat($paymentAmount));
        }
        // предварительная проверка платежа
        if(empty($orderInfo)) {
          $msg =  "ERR: НЕКОРРЕКТНЫЕ ДАННЫЕ ЗАКАЗА";
          return $msg;
        }

        $orderNumber = $_POST['orderNumber'] . '/' . time();
        $amount = number_format($_POST['amount'], 2, '', '');

        $url = $serverUrl.'/payment/rest/register.do';
        $url .= '?userName=' . $userName . '&password=' . $password . '&amount=' . $amount  
            . '&currency=' . $_POST['currency'] . '&language=' . $_POST['language'] 
            . '&orderNumber=' . $orderNumber . '&description=' . $_POST['description'] 
            . '&returnUrl=' . urlencode($_POST['returnUrl']);

        if ($paymentInfo[3]['value'] == 'true') {
          $content = unserialize(stripslashes($orderInfo[$paymentOrderId]['order_content']));
          $ids = array();
          $units = array();
          foreach ($content as $prod) {
            $ids[] = $prod['id'];
          }

          $res = DB::query("SELECT p.`id`, p.`unit` as produnit, c.`unit` as catunit 
              FROM `".PREFIX."product` p 
              LEFT JOIN `".PREFIX."category` c 
              ON p.`cat_id` = c.`id`
              WHERE p.`id` IN (".DB::quoteIN($ids).")");
          while ($row = DB::fetchArray($res)) {
            if ($row['produnit']) {
              $units[$row['id']] = $row['produnit'];
            } elseif($row['catunit']) {
              $units[$row['id']] = $row['catunit'];
            } else {
              $units[$row['id']] = 'шт.';
            }
          }

          $orderBundle = array();
          $i = 1;
          foreach ($content as $prod) {
            $item = array();

            $item['positionId'] = $i++;

            $prod['name'] = strip_tags($prod['name']);
            $prod['name'] = str_replace('\\', '-', $prod['name']);
            $prod['name'] = preg_replace('/[[:cntrl:]]/', '', $prod['name']);
            $prod['name'] = preg_replace('/\s+/', ' ',$prod['name']);
            $prod['name'] = MG::textMore(trim($prod['name']), 96);
            $item['name'] = urlencode($prod['name']);

            $item['quantity']['value'] = intval($prod['count']);
            $item['quantity']['measure'] = $units[$prod['id']];
            $item['itemAmount'] = $prod['price']*$prod['count']*100;
            $item['itemCode'] = urlencode($prod['code']);
            $item['tax']['taxType'] = $paymentInfo[5]['value'];
            $item['itemPrice'] = $prod['price']*100;
            $item['itemAttributes']['attributes'] = array(
              array('name' => 'paymentMethod', 'value' => 1),
              array('name' => 'paymentObject', 'value' => 1)
            );

            $orderBundle['cartItems']['items'][] = $item;
          }

          if ($orderInfo[$paymentOrderId]['delivery_cost'] > 0) {
            $item = array();

            $item['positionId'] = $i++;
            $item['name'] = 'Доставка';
            $item['quantity']['value'] = 1;
            $item['quantity']['measure'] = 'шт.';
            $item['itemAmount'] = $orderInfo[$paymentOrderId]['delivery_cost']*100;
            $item['itemCode'] = 'DOSTAVKA';
            $item['tax']['taxType'] = $paymentInfo[6]['value'];
            $item['itemPrice'] = $orderInfo[$paymentOrderId]['delivery_cost']*100;
            $item['itemAttributes']['attributes'] = array(
              array('name' => 'paymentMethod', 'value' => 1),
              array('name' => 'paymentObject', 'value' => 4)
            );

            $orderBundle['cartItems']['items'][] = $item;
          }

          if (!empty($orderBundle)) {
            $url .= '&orderBundle='.json_encode($orderBundle).'&taxSystem='.$paymentInfo[4]['value'];
          }
        }
        
        $jsondata = file_get_contents($url);
        $objResponse = json_decode($jsondata);
        
        // если произошла ошибка
        if(!empty($objResponse->errorCode)) {
          $msg = "ERR: " . $objResponse->errorMessage;
          return $msg;
        }

        // если ссылка сформированна, то отправляем клиента в альфабанк
        if(!empty($objResponse->orderId) && !empty($objResponse->formUrl)) {
          header('Location: ' . $objResponse->formUrl);
        }

        exit;
      } else if(!empty($_REQUEST['orderId'])) {
        $url = $serverUrl.'/payment/rest/getOrderStatusExtended.do';
        $url .= '?userName=' . $userName . '&password=' . $password 
            . '&language=ru' . '&orderId=' . $_REQUEST['orderId'];

        $jsondata = file_get_contents($url);
        $objResponse = json_decode($jsondata);

        // если произошла ошибка
        if(!empty($objResponse->ErrorCode)) {
          $msg = "ERR: " . $objResponse->ErrorMessage;
          return $msg;
        }

        if($objResponse->errorCode == 0 && $objResponse->orderStatus == 2 
            && $objResponse->actionCode == 0) {
          // приводим номер заказа к нормальному виду
          $orderNumber = explode('/', $objResponse->orderNumber);
          $paymentOrderId = $orderNumber[0];
          
          $paymentAmount = substr($objResponse->amount, 0, - 2) . "." . substr($objResponse->amount, -2);

          // проверяем имеется ли в базе заказ с такими параметрами
          if(!empty($paymentAmount) && !empty($paymentOrderId)) {
            $orderInfo = $order->getOrder(" id = " . DB::quoteInt($paymentOrderId, 1)
                . " and summ+delivery_cost = " . DB::quoteFloat($paymentAmount));
          }

          // если заказа с таким номером и стоимостью нет, то возвращаем ошибку
          if(empty($orderInfo)) {
            $msg =  "ERR: НЕКОРРЕКТНЫЕ ДАННЫЕ";
            return $msg;
          }
          
          // высылаем письма админу и пользователю об успешной оплате заказа, 
          // только если его действующий статус не равен "оплачен" или "выполнен" или "отменен"   
          if($orderInfo[$paymentOrderId]['status_id'] != 2 && $orderInfo[$paymentOrderId]['status_id'] != 4 && $orderInfo[$paymentOrderId]['status_id'] != 5) {
            $this->actionWhenPayment(
              array(
                'paymentOrderId' => $paymentOrderId,
                'paymentAmount' => $orderInfo[$paymentOrderId]['summ'],
                'paymentID' => $paymentID
              )
            );
          }

          $msg = 'Вы успешно оплатили заказ №' . $orderInfo[$paymentOrderId]['number'];
          $msg .= $this->msg;
        } else {
          $msg = $objResponse->actionCodeDescription;
        }

      }
    }

    return $msg;
  }

  /**
   * Проверка платежа через Tinkoff.
   * @param int $paymentID ID способа оплаты
   * @param string $paymentStatus статус платежа
   * @return string
   */
  public function tinkoff($paymentID, $paymentStatus) {
    $orderId = explode('-', URL::get("OrderId"));
    $orderId = $orderId[0];

    if (empty($orderId)) {
      echo 'Не указан номер заказа!';
      exit();
    }
    
    $order = new Models_Order();
    $paymentInfo = $order->getParamArray($paymentID, null, null);
    if('result' == $paymentStatus) {
      $orderInfo = $order->getOrder(" id = ".DB::quoteInt($orderId, 1));
      if($orderInfo[$orderId]['status_id'] != 2 && $orderInfo[$orderId]['status_id'] != 4 && $orderInfo[$orderId]['status_id'] != 5) {
        include_once CORE_LIB.'TinkoffMerchantAPI.php';
        $api = new TinkoffMerchantAPI(
            $paymentInfo[0]['value'],
            $paymentInfo[1]['value'],
            $paymentInfo[2]['value']
        );

        $paramsTinkoff = array(
            'PaymentId' => URL::get("PaymentId")
        );
        // получаем ответ от банка на наличие проведенного платежа
        $tinkoffResponse = json_decode($api->getState($paramsTinkoff));
        
        if(@$tinkoffResponse->Status == 'CONFIRMED' || @$tinkoffResponse->Status == 'CONFIRMING') {
          // $paymentAmount = URL::get("Amount")/100;
          // $orderInfo = $order->getOrder(" id = " . DB::quote($orderId, 1) 
          //           . " and summ+delivery_cost = " . DB::quote($paymentAmount, 1));
          $this->actionWhenPayment(
                  array(
                    'paymentOrderId' => $orderId,
                    'paymentAmount' => $orderInfo[$orderId]['summ'],
                    'paymentID' => $paymentID
                  )
                );

          $msg = 'Вы успешно оплатили заказ №'.$orderInfo[$orderId]['number']; 
          $msg .= $this->msg; 
        } else {
          $msg = 'Оплата не удалась. '.$orderInfo[$orderId]['number'].' оплата недействительна.';
        }
      } else {
        $msg = 'Оплата не удалась. '.$orderInfo[$orderId]['number'].' уже оплачен.';
      }
    } else {
      $params = URL::get("Details");
      $msg = 'Оплата не удалась. '.$params;
    }
    echo 'OK';
    exit;
    return $msg;
  }

  /**
   * Проверка платежа через PayPal.
   * @param int $paymentID ID способа оплаты
   * @param string $paymentStatus статус платежа
   * @return string
   */
  public function paypal($paymentID, $paymentStatus) {
    $paymentType = $msg = '';
    $res = DB::query("SELECT `paramArray` FROM `".PREFIX."payment` WHERE `id` = 19");
    $row = DB::fetchAssoc($res);

    $i = 0;
    $paymentParam = array();
    $param = json_decode($row['paramArray']);
    foreach ($param as $key=>$value) {
      $paymentParam[$i] = CRYPT::mgDecrypt($value);
      $i++;
    }

    if (!ini_get('user_agent')) {
      ini_set('user_agent', "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36");
    }

    if(array_key_exists('amt', $_GET) && array_key_exists('cc', $_GET) && array_key_exists('cm', $_GET) && array_key_exists('tx', $_GET)) {
      $paymentType = 'pdt';
    }

    if(array_key_exists('mc_gross', $_POST) && array_key_exists('payment_status', $_POST) && array_key_exists('custom', $_POST) && array_key_exists('business', $_POST) && array_key_exists('txn_id', $_POST) && array_key_exists('mc_currency', $_POST)) {
      $paymentType = 'ipn';
    }

    if($paymentType == 'pdt') {

      $res = DB::query("SELECT `summ`, `delivery_cost`, `paided`, `number`, `currency_iso` FROM `".PREFIX."order` WHERE `id` = ".DB::quoteInt($_GET['cm'], true));
      $row = DB::fetchAssoc($res);

      $orderNumber = $row['number'];
      $status = $row['paided'];
      $newPrice = $row['summ'] + $row['delivery_cost'];

      $currency = $row['currency_iso'];
      if($currency == 'RUR') {
        $currency = 'RUB';
      }

      if($_GET['amt'] == $newPrice && $status == 0) {

        if($paymentParam[2] === 'true' || $paymentParam[2] === true || $paymentParam[2] === 1) {
          $req = "cmd=_notify-synch&tx=".$_GET['tx']."&at=".$paymentParam[0];

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, "https://www.sandbox.paypal.com/cgi-bin/webscr");
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
          curl_setopt($ch, CURLOPT_HTTPHEADER, 0);
          $res = curl_exec($ch);
          curl_close($ch);
        }
        else{
          $res = file_get_contents("https://www.paypal.com/cgi-bin/webscr?cmd=_notify-synch&tx=".$_GET['tx']."&at=".$paymentParam[0], false);
        }

        if(!$res || strpos($res, '400 Bad Request') !== false) {
          $msg = 'fail';
          $this->msg = 'Ошибка соединения с PayPal';
        }
        else{
          $lines = explode("\n", trim($res));
          $resArr = array();
          if(strcmp ($lines[0], "SUCCESS") == 0) {
            for ($i = 1; $i < count($lines); $i++) {
              $temp = explode("=", $lines[$i],2);
              $resArr[urldecode($temp[0])] = urldecode($temp[1]);
            }

            if($resArr['payment_status'] == 'Completed' && $newPrice == $resArr['mc_gross'] && $_GET['tx'] == $resArr['txn_id'] && $paymentParam[1] == $resArr['business'] && $currency == $resArr['mc_currency'] && $_GET['cm'] == $resArr['custom']) {

              $res = DB::query("SELECT `paided` FROM `".PREFIX."order` WHERE `id` = ".DB::quoteInt($_GET['cm'], true));
              $row = DB::fetchAssoc($res);
              $status = $row['paided'];
              
              $res = DB::query('UPDATE `'.PREFIX.'order` SET status_id = 2, paided = 1 WHERE id = '.DB::quoteInt($resArr['custom'], true));

              if($res) {

                if($status == 0) {
                  // MG::loger('payed via pdt');
                  $this->actionWhenPayment(
                    array(
                      'paymentOrderId' => DB::quoteInt($resArr['custom'], true),
                      'paymentAmount' => $newPrice,
                      'paymentID' => $paymentID
                    ));
                }

              $msg = 'success'; 
              $this->msg = 'Вы успешно оплатили заказ № '.$orderNumber; 
              }
            }
            else{
              MG::loger('Проверка оплаты через PayPal (заказ № '.DB::quoteInt($resArr['custom'], true).') не удалась (проверка отклонена магазином)');
              $msg = 'fail';
              $this->msg = 'Оплата не удалась';
            }
          }
          else if(strcmp ($lines[0], "FAIL") == 0) {
            MG::loger('Проверка оплаты через PayPal (заказ № '.DB::quoteInt($_GET['cm'], true).') не удалась (проверка отклонена сервером PayPal)');
            $msg = 'fail';
            $this->msg = 'Оплата не удалась';
          }
        }
      }
      else if($status == 1) {
        //MG::loger('Заказ через PayPal № '.DB::quoteInt($_GET['cm'], true).' уже оплачен (скорее всего методом ipn)');
        $msg = 'success'; 
        $this->msg = 'Заказ № '.$orderNumber.' оплачен'; 
      }
      else{
        MG::loger('Проверка оплаты через PayPal (заказ № '.DB::quoteInt($_GET['cm']).') не удалась (не совпали данные запроса)');
        $msg = 'fail';
        $this->msg = 'Оплата не удалась';
      }
    } elseif($paymentType == 'ipn') {

      $postdata = $_POST;

      if(array_key_exists('charset', $postdata) && ($charset = $postdata['charset']) && $postdata['charset'] != 'utf-8') {
        foreach ($postdata as $key => $value) {
          $postdata[$key] = mb_convert_encoding($value, 'utf-8', $charset);
        }
      }

      $address = 'Адрес доставки PayPal: '.$postdata['address_country'].'; '.$postdata['address_state'].'; '.$postdata['address_city'].'; '.$postdata['address_street'].'; '.$postdata['address_name'];

      $orderid = $postdata['custom'];

      $res = DB::query("SELECT `summ`, `delivery_cost`, `currency_iso` FROM `".PREFIX."order` WHERE `id` = ".DB::quoteInt($orderid, true));
      $row = DB::fetchAssoc($res);

      $newPrice = $row['summ'] + $row['delivery_cost'];

      $currency = $row['currency_iso'];
      if($currency == 'RUR') {
        $currency = 'RUB';
      }

      if($postdata['payment_status'] == 'Completed' && $postdata['mc_gross'] == $newPrice && $postdata['business'] == $paymentParam[1] && $postdata['mc_currency'] == $currency) {
        
        if($paymentParam[2] === 'true' || $paymentParam[2] === true || $paymentParam[2] === 1) {
          $link = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $link);
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('cmd' => '_notify-validate') + $_POST) );
          curl_setopt($ch, CURLOPT_HEADER, 0);
          $res = curl_exec($ch);
          $stat = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          curl_close($ch);
        }
        else{
          $stat = 200;

          $req = http_build_query(array('cmd' => '_notify-validate') + $_POST);

          $res = file_get_contents("https://www.paypal.com/cgi-bin/webscr?".$req, false);
        }

        if($stat == 200 && $res == 'VERIFIED') {

          $res = DB::query("SELECT `paided` FROM `".PREFIX."order` WHERE `id` = ".DB::quoteInt($orderid, true));
          $row = DB::fetchAssoc($res);
          $status = $row['paided'];

          if(strpos($row['comment'], 'Адрес доставки PayPal:') === false) {
            $address = $row['comment'].$address;
            DB::query('UPDATE `'.PREFIX.'order` SET `comment` = '.DB::quote($address).' WHERE id = '.DB::quoteInt($orderid));
          }

          if($status == 0) {
            $res = DB::query('UPDATE `'.PREFIX.'order` SET status_id = 2, paided = 1 WHERE id = '.DB::quoteInt($orderid, true));
            if($res) {
              // MG::loger('payed via ipn');
              $this->actionWhenPayment(
                array(
                  'paymentOrderId' => DB::quoteInt($orderid, true),
                  'paymentAmount' => $newPrice,
                  'paymentID' => $paymentID
                ));
              $msg = 'success'; 
              // return 'Вы успешно оплатили заказ № '.DB::quoteInt($orderid, true); 
            }
          }
          else{
            // MG::loger('Заказ через PayPal № '.DB::quoteInt($orderid, true).' уже оплачен (скорее всего методом pdt)');
            $msg = 'success'; 
            // return 'Заказ уже оплачен';
          }
        }
        else{
          MG::loger('Проверка оплаты через PayPal (заказ № '.DB::quoteInt($orderid, true).') не удалась (проверка отклонена сервером PayPal)');
          $msg = 'fail'; 
          // return 'Проверка оплаты не удалась';
        }
      }
      else{
        if(class_exists('IpnOrder') && $postdata['payment_status'] == 'Completed') {
          IpnOrder::createOrder($_POST, $paymentParam);
        }
        else{
          MG::loger('Проверка оплаты через PayPal (заказ № '.DB::quoteInt($orderid, true).') не удалась (не совпали данные запроса)');
        }
        $msg = 'fail'; 
        // return 'Проверка оплаты не удалась';
      }
    } else {
      $this->msg = 'Оплата не удалась';
    }
    return $msg;
  }

  /**
   * Проверка платежа через ComePay.
   * @param int $paymentID ID способа оплаты
   * @param string $paymentStatus статус платежа
   * @return string
   */
  public function comepay($paymentID, $paymentStatus)
  {
    if('result' == $paymentStatus) {
      $orderModel  = new Models_Order();
      $paymentInfo = $orderModel->getParamArray($paymentID, null, null);
      /**
       * Для Basic-авторизации при выставлении счетов
       */
      $shopNumber = $paymentInfo[1]['value'];
      /**
       * Для авторизации уведомлений платежной системы
       */
      $callbackPassword = $paymentInfo[3]['value'];
      if(isset($_SERVER['PHP_AUTH_USER'])) {
        if(($_SERVER['PHP_AUTH_USER'] !== $shopNumber || $_SERVER['PHP_AUTH_PW'] !== $callbackPassword)) {
          $resultCode = 150;
          echo "<?xml version=\"1.0\"?><result><result_code>{$resultCode}</result_code></result>";
          exit;
        }
      } elseif(isset($_SERVER['REMOTE_USER'])) {
        if(($_SERVER['REMOTE_USER'] !== 'Basic ' . base64_encode($shopNumber . ':' . $callbackPassword))) {
          $resultCode = 150;
          echo "<?xml version=\"1.0\"?><result><result_code>{$resultCode}</result_code></result>";
          exit;
        }
      } else {
        $resultCode = 150;
        echo "<?xml version=\"1.0\"?><result><result_code>{$resultCode}</result_code></result>";
        exit;
      }

      $transactionId = htmlspecialchars(URL::get("bill_id"));
      $orderId       = explode('-', $transactionId);
      if(empty($orderId[0])) {
        $resultCode = 0;
        echo "<?xml version=\"1.0\"?><result><result_code>{$resultCode}</result_code></result>";
        exit;
      }

      $orderId = intval($orderId[0]);

      $orderInfo = $orderModel->getOrder(" id = " . DB::quoteInt($orderId, true));
      if( ! isset($orderInfo)) {
        $resultCode = 0;
        echo "<?xml version=\"1.0\"?><result><result_code>{$resultCode}</result_code></result>";
        exit;
      }

      $order = $orderInfo[$orderId];
      if($orderInfo[$orderId]['status_id'] != 2 && $orderInfo[$orderId]['status_id'] != 4 && $orderInfo[$orderId]['status_id'] != 5) {

        $amount = round(($order['summ'] + $order['delivery_cost']) * 100);

        $transactionAmount = round(intval(htmlspecialchars(URL::get("amount"))* 100));

        if($transactionAmount !== $amount) {
          $resultCode = 0;
          echo "<?xml version=\"1.0\"?><result><result_code>{$resultCode}</result_code></result>";
          exit;
        }
        $transactionStatus = htmlspecialchars(URL::get("status"));
        if(strtolower($transactionStatus) !== strtolower('paid')) {
          $resultCode = 0;
          echo "<?xml version=\"1.0\"?><result><result_code>{$resultCode}</result_code></result>";
          exit;
        }
        $this->actionWhenPayment(
          array(
            'paymentOrderId' => $orderId,
            'paymentAmount'  => $orderInfo[$orderId]['summ'],
            'paymentID'      => $paymentID
          )
        );
      }

      $resultCode = 0;
      echo "<?xml version=\"1.0\"?><result><result_code>{$resultCode}</result_code></result>";

      exit;
    } elseif('success' == $paymentStatus) {
      $this->msg = 'Вы успешно оплатили заказ';

      return 'success';
    } elseif('fail' == $paymentStatus) {
      $this->msg = 'Оплата не удалась';

      return 'fail';
    }

    return 'fail';
  }

  /**
   * Проверка платежа через PayKeeper.
   */
  public function paykeeper($paymentID, $paymentStatus) {
    $order = new Models_Order();
  
    if('success' == $paymentStatus) {
      
      if(!empty($_POST['clientid'])) {
        $orderInfo = $order->getOrder(" id = " . DB::quoteInt(intval($_POST['clientid']), 1));
        $msg = 'Вы успешно оплатили заказ № ' . $orderInfo[$_POST['clientid']]['number'].'. Спасибо! Ожидайте звонка менеджера.'; 
      } else {
        $msg = 'Вы успешно оплатили заказ. Спасибо! Ожидайте звонка менеджера.';
      }  
      $msg .= $this->msg;
      
    } elseif('result' == $paymentStatus && count($_POST) > 1) {
      
      $id = $_POST['id'];
      $paymentAmount = $_POST['sum'];
      $paymentOrderId = intval($_POST['clientid']);
      $orderid = $_POST['orderid'];
      $key = $_POST['key'];
        
      //Проверка существование заказа и подлинности платежа
      if(!empty($paymentAmount) && $paymentOrderId > 0) {
        $orderInfo = $order->getOrder(" id = " . DB::quoteInt($paymentOrderId, 1) . " and summ+delivery_cost = " . DB::quoteFloat($paymentAmount));
      }

      $paymentInfo = $order->getParamArray($paymentID);
      $secret_seed = trim($paymentInfo[2]['value']);

      if($key != md5 ($id . sprintf ("%.2lf", $paymentAmount).$paymentOrderId.$orderid.$secret_seed)) {
        echo "Error! Hash mismatch";
        exit();
      }
      
      // предварительная проверка платежа
      if(empty($orderInfo)) {
        echo "ERR: НЕКОРРЕКТНЫЕ ДАННЫЕ ЗАКАЗА";
        exit();
      }
      if (!$orderInfo[$paymentOrderId]['paided']) {
        $this->actionWhenPayment(
          array(
            'paymentOrderId' => $paymentOrderId,
            'paymentAmount' => $paymentAmount,
            'paymentID' => $paymentID
          )
        );
      }
      
      // ОТДАЕМ PAYKEEPER ВСЕ OK
      echo "OK ".md5($id.$secret_seed);
      exit;
    } else {
      $msg = 'Оплата не удалась';
    }
    
    return $msg;
  }

  /**
   * Проверка платежа через CloudPayments.
   * @param int $paymentID ID способа оплаты
   * @param string $paymentStatus статус платежа
   * @return string
   */
  public function cloudpayments($paymentID, $paymentStatus) {
  
    $orderNumber = URL::getQueryParametr('orderNumber');

 //   if (empty($orderNumber)) {
 //     echo 'Оплата не удалась';
 //     exit();
 //   }

    // Редирект из виджета
    if($paymentStatus == 'success') {
      $msg = str_replace('{number}', $orderNumber, lang('paymentCloudPaymentsSuccess'));
      $msg .= $this->msg;
      return $msg;
    } elseif($paymentStatus == 'fail') {
      $msg = str_replace('{number}', $orderNumber, lang('paymentCloudPaymentsFail'));
      $msg .= $this->msg;
      return $msg;
    }

    // Обрабатываем уведомление от CloudPayments
    $response_codes = array(
      'SUCCESS' => 0,
      'ERROR_INVALID_ORDER' => 10,
      'ERROR_INVALID_COST' => 11,
      'ERROR_NOT_ACCEPTED' => 13,
      'ERROR_EXPIRED' => 20
    );

    $response = array(
      'code' => $response_codes['SUCCESS']
    );

    $order = new Models_Order();
    $paymentInfo = $order->getParamArray($paymentID, null, null);
    // Проверяем контрольную подпись
    $post_data    = file_get_contents('php://input');
    $check_sign   = base64_encode(hash_hmac('SHA256', $post_data, $paymentInfo[1]['value'], true));
    $request_sign = isset($_SERVER['HTTP_CONTENT_HMAC']) ? $_SERVER['HTTP_CONTENT_HMAC'] : '';


    if(false && $check_sign !== $request_sign) {
      $response['code'] = $response_codes['ERROR_NOT_ACCEPTED'];
      $response['msg'] = 'Invalid signature';
    } else {
      $action = URL::getQueryParametr('action');
      $orderId = null;
      if(isset($_POST['Data'])) {
        $data = json_decode(str_replace('&quot;', '"', $_POST['Data']), true);
        if(!empty($data['order_id'])) {
          $orderId = intval($data['order_id']);
        }
      }
      if(!empty($orderId)) {
        $orderInfo = $order->getOrder(" id = " . DB::quoteInt($orderId, 1));
        $orderInfo = current($orderInfo);
      } else {
        $orderNumber = isset($_POST['InvoiceId']) ? $_POST['InvoiceId'] : '';
        $orderInfo = $order->getOrder(" number = " . DB::quote($orderNumber));
        $orderInfo = current($orderInfo);
        $orderId = isset($orderInfo['id']) ? $orderInfo['id'] : null;
      }
      if(empty($orderInfo)) {
        $response['code'] = $response_codes['ERROR_INVALID_ORDER'];
        $response['msg'] = 'Order not found';
      } else {
        // Запросы связанные с оплатой, для них проверяем статус заказа и сумму
        $is_payment_callback = in_array($action, array('check', 'pay', 'confirm'));
        $orderSum = floatval($orderInfo['summ']) + floatval($orderInfo['delivery_cost']);

        if($is_payment_callback && in_array($orderInfo['status_id'], array(2, 4, 5))) {
          // Нельзя оплатить уже оплаченный заказ
          $response['code'] = $response_codes['ERROR_NOT_ACCEPTED'];
          $response['msg'] = 'Order already payment or canceled';
        } 
        elseif($is_payment_callback && floatval($_POST['Amount']) != $orderSum) {
          // Проверяем сумму заказа
          $response['code'] = $response_codes['ERROR_INVALID_COST'];
          $response['msg'] = 'Invalid order summ, should be ' . $orderSum;
        } 
        elseif(($action == 'pay' && $_POST['Status'] == 'Completed') || $action == 'confirm') {
          $this->actionWhenPayment(
            array(
              'paymentOrderId' => $orderId,
              'paymentAmount' => $orderInfo['summ'],
              'paymentID' => $paymentID
            )
          );
        }
        elseif(in_array($action, array('fail', 'refund', 'cancel'))) {
          $order = new Models_Order();
          if(method_exists($order, 'updateOrder')) {
            $order->updateOrder(array(
              'id' => $orderId,
              'status_id' => 4
            ), true);
          }
        }
        elseif(in_array($action, array('pay')) && $_POST['Status'] == 'Authorized') {
          $order = new Models_Order();
          if(method_exists($order, 'updateOrder')) {
            $order->updateOrder(array(
              'id' => $orderId,
              'status_id' => 1
            ), true);
          }
        }
      }
    }

    header('Content-Type: application/json');
    echo json_encode($response, 256); //JSON_UNESCAPED_UNICODE для совместимости с PHP 5.3;
    exit;
  }

  /**
   * Проверка платежа через Яндекс.Кассу.
   * @param int $paymentID ID способа оплаты
   */
  public function yandexKassaNew($paymentID) {

    /* Узнаем статус
      waiting_for_capture
      succeeded
      canceled
    */

    $data = file_get_contents("php://input");
    $json = json_decode($data, true);
    $paymentStatus = $json['object']['status'];
    $orderId = $json['object']['metadata']['orderId'];

    if (empty($orderId)) {
      echo 'Оплата не удалась';
      exit();
    }

    
    $order = new Models_Order();
    $orderNumber = $order->getOrder(' id = '.DB::quoteInt(intval($orderId), true));
    
    if($paymentStatus == 'succeeded' || $paymentStatus == 'waiting_for_capture') {
      //$orderInfo = $order->getOrder(" number = " . DB::quote($orderNumber));
      $orderInfo = $order->getOrder(" number = " . DB::quote($orderNumber));
      if($orderInfo[$orderId]['status_id']!=2 && $orderInfo[$orderId]['status_id']!=4 && $orderInfo[$orderId]['status_id']!=5) {
        $this->actionWhenPayment(
          array(
            'paymentOrderId' => $orderId,
            'paymentAmount' => $orderInfo[$orderId]['summ'],
            'paymentID' => $paymentID
            )
          );
      }
      $msg = 'Вы успешно оплатили заказ №' . $orderNumber;
      $msg .= $this->msg;
      $this->msg = $msg;
    } elseif($paymentStatus == 'canceled') {
      $order->setOrderStatus($orderId, 4);
      $msg = 'Оплата заказ №' . $orderNumber . ' отменена';
      $msg .= $this->msg;
      $this->msg = $msg;
    }  else {  
      //Раскоментировать, если будут проблемы
      //MG::loger($data, 'new', 'yandex');
      $this->msg = 'Оплата не удалась';
    }
    return $paymentStatus;
  }

  /**
   * Проверка платежа через FreeKassa.
   * @param int $paymentID ID способа оплаты
   * @param string $paymentStatus статус платежа
   */
  public function freeKassa($paymentID, $paymentStatus) {
    if('success' == $paymentStatus) {
      
      $msg = 'Вы успешно оплатили заказ. Спасибо!';
      
      $msg .= $this->msg;
      
    } elseif('fail' == $paymentStatus) {
      $msg = 'Не удалось оплатить заказ!';
      
      $msg .= $this->msg;
    } elseif('result' == $paymentStatus && !empty($_REQUEST['MERCHANT_ORDER_ID'])) {
      $orderAmount = $_REQUEST['AMOUNT']; //Стоимость заказа
      $orderId = $_REQUEST['MERCHANT_ORDER_ID']; //ID заказа

      if(empty($orderId)) {
        echo 'Оплата не удалась';
        exit();
      }

      //Получаем настройки оплаты
      $order = new Models_Order();
      $paymentInfo = $order->getParamArray($paymentID, null, null);

      //Получаем ID магазина и второе секретное слово
      $merchant_id = $paymentInfo[1]['value'];
      $merchant_secret = $paymentInfo[3]['value'];

      //Собираем подпись для проверки
      $sign = md5($merchant_id.':'.$orderAmount.':'.$merchant_secret.':'.$orderId);

      //Проверяем собранную подпись с пришедшей
      if ($sign != $_REQUEST['SIGN']) {
        //Если не совпало, то спамим в логер и прерываем выполнение
        MG::loger('Error pay free-kassa:');
        MG::loger($_REQUEST);
        die('wrong sign');
      }

      //Если подписи равны, то ищем заказ и помечаем его оплаченым
      $orderInfo = $order->getOrder(" id = " . DB::quoteInt($orderId));
      if($orderInfo[$orderId]['status_id']!=2 && $orderInfo[$orderId]['status_id']!=4 && $orderInfo[$orderId]['status_id']!=5) {
        $this->actionWhenPayment(
          array(
            'paymentOrderId' => $orderId,
            'paymentAmount' => $orderAmount,
            'paymentID' => $paymentID
            )
          );
      }


      echo 'YES'; //Возвращаем 'YES' по документации FREE-KASSA
      exit();
    }
  }

  /**
   * Проверка платежа через Мегакасса.
   * @param int $paymentID ID способа оплаты
   * @param string $paymentStatus статус платежа
   */
  public function megaKassa($paymentID, $paymentStatus){
    if ($paymentStatus == 'success') {

      $msg = 'Вы успешно оплатили заказ. Спасибо!';

      $msg .= $this->msg;

    } elseif ($paymentStatus == 'fail') {
      $msg = 'Не удалось оплатить заказ!';

      $msg .= $this->msg;
    } elseif ($paymentStatus == 'result') {

      //Получаем настройки оплаты
      $order = new Models_Order();
      $paymentInfo = $order->getParamArray(27, null, null);
      $secretKey = $paymentInfo[1]['value'];

      $orderId = $_REQUEST['mg_order_id']; //ID заказа
      $orderAmount = $_REQUEST['amount']; //Стоимость заказа

      // проверка IP-адреса
      $ipChecked = false;

      foreach (array('HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'HTTP_CLIENT_IP', 'REMOTE_ADDR') as $param) {
        if (!empty($_SERVER[$param]) && $_SERVER[$param] === '5.196.121.217') {
          $ipChecked = true;
          break;
}
      }
      if (!$ipChecked) {
        die('error');
      }

      // проверка на наличие обязательных полей
      // поля $payment_time и $debug могут дать true для empty() поэтому их нет в проверке
      foreach (array('uid', 'amount', 'amount_shop', 'amount_client', 'currency', 'order_id', 'payment_method_title', 'creation_time', 'client_email', 'status', 'signature') as $field) {
        if (empty($_REQUEST[$field])) {
          die('error');
        }
      }

      // нормализация данных
      $uid = (int)$_REQUEST["uid"];
      $amount = (double)$_REQUEST["amount"];
      $amountShop = (double)$_REQUEST["amount_shop"];
      $amountClient = (double)$_REQUEST["amount_client"];
      $currency = $_REQUEST["currency"];
      $orderID = $_REQUEST["order_id"];
      $paymentMethodID = (int)$_REQUEST["payment_method_id"];
      $paymentMethodTitle = $_REQUEST["payment_method_title"];
      $creationTime = $_REQUEST["creation_time"];
      $paymentTime = $_REQUEST["payment_time"];
      $clientEmail = $_REQUEST["client_email"];
      $status = $_REQUEST["status"];
      $debug = (!empty($_REQUEST["debug"])) ? '1' : '0';
      $signature = $_REQUEST["signature"];

      // проверка валюты
      if (!in_array($currency, array('RUB', 'USD', 'EUR'), true)) {
        die('error');
      }

      // проверка статуса платежа
      if (!in_array($status, array('success', 'fail'), true)) {
        die('error');
      }

      // проверка формата сигнатуры
      if (!preg_match('/^[0-9a-f]{32}$/', $signature)) {
        die('error');
      }

      // проверка значения сигнатуры
      $signature_calc = md5(join(':', array($uid, $amount, $amountShop, $amountClient, $currency, $orderID, $paymentMethodID, $paymentMethodTitle, $creationTime, $paymentTime, $clientEmail, $status, $debug, $secretKey)));
      if ($signature_calc !== $signature) {
        die('error');
      }

      //Если подписи равны, то ищем заказ и помечаем его оплаченным
      $orderInfo = $order->getOrder(" id = " . DB::quoteInt($orderId));
      if($orderInfo[$orderId]['status_id']!=2 && $orderInfo[$orderId]['status_id']!=4 && $orderInfo[$orderId]['status_id']!=5 && $_REQUEST['status'] == 'success') {
        $this->actionWhenPayment(
          array(
            'paymentOrderId' => $orderId,
            'paymentAmount' => $orderAmount,
            'paymentID' => $paymentID
          )
        );
      }
      echo('ok');
      exit();
    }
    return $msq;
  }

  /**
  * Проверка платежа через Qiwi API.
  * @param int $paymentID ID способа оплаты
  * @param string $paymentStatus статус платежа
  */
  public function qiwiApi($paymentID, $paymentStatus){
  //Получаем настройки оплаты
  $order = new Models_Order();
  $paymentInfo = $order->getParamArray(28, null, null);
  $secretKey = $paymentInfo[1]['value'];
  $orderIDqiwi = $_SESSION['qiwiApi']['orderID'];

  $auth = 'Bearer '. $secretKey;
  $url = 'https://api.qiwi.com/partner/bill/v1/bills/'.$orderIDqiwi;
  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: application/json", "Content-Type: application/json", "Authorization: $auth"));

  $response = curl_exec($curl);
  $code = curl_getinfo($curl,CURLINFO_HTTP_CODE);
  curl_close($curl);

  $response = json_decode($response, true);
  $status = $response['status']['value'];
  if ($status == 'PAID') {
    $orderID = preg_replace('/-.*/is', '',$response['billId']);
    $orderInfo = $order->getOrder(" id = " . DB::quoteInt(intval($orderID), 1) . " and summ+delivery_cost = " . DB::quoteFloat($response['amount']['value']));

    if(empty($orderInfo)) {
          $msg = 'ERR: Заказ был изменен! Была произведена оплата '.$response['amount']['value'].' '.$response['amount']['currency'].'  по некорректному счету!';
          $msg .= $this->msg;
        }
    else{
    $msg = 'Вы успешно оплатили заказ. Спасибо!';
    $msg .= $this->msg;
      
    // Находим заказ и помечаем его оплаченным
    if ($response['billId'] == $orderIDqiwi) {
      $orderInfo = $order->getOrder(" id = " . DB::quoteInt($orderID));
      if($orderInfo[$orderID]['status_id']!=2 && $orderInfo[$orderID]['status_id']!=4 && $orderInfo[$orderID]['status_id']!=5) {
        $this->actionWhenPayment(
          array(
            'paymentOrderId' => $orderID,
            'paymentAmount' => $response['amount']['value'],
            'paymentID' => $paymentID
          )
        );
       }
      }
    }
  } else {
  $msg = 'Возникла ошибка в оплате заказа.';
  $msg .= $this->msg;
  }

  return $msg;
  }
}
