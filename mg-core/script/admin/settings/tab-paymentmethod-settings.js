var settings_payment = (function () {
	return {
		init: function() {
			settings_payment.initEvents();
			settings.updataTabs();
			admin.sortable('.paymentMethod-tbody','payment');
		},
		initEvents: function() {
			// установка активности для способов оплаты
			$('#tab-paymentMethod-settings').on('click', '.activity', function() {
				$(this).find('a').toggleClass('active');
				if($(this).attr('status') == 1) $(this).attr('status', 0); else $(this).attr('status', 1);
				settings_payment.changeActivity($(this).attr('id'), $(this).find('a').hasClass('active'));
			});

			// Вызов модального окна при нажатии на кнопку изменения способа оплаты.
			$('#tab-paymentMethod-settings').on('click', '.edit-row', function() {
				admin.ajaxRequest({
					mguniqueurl: "action/loadPayment",
					id: $(this).parents('tr').data('id'),
					lang: $('#tab-paymentMethod-settings .select-lang').val()
				},
				function (response) {
					settings_payment.openPaymentModalWindow(response.data);
				});
			});

			$('#tab-paymentMethod-settings').on('change', '.select-lang', function() {
				admin.ajaxRequest({
					mguniqueurl: "action/loadPayment",
					id: $('#tab-paymentMethod-settings .save-button').attr('id'),
					lang: $('#tab-paymentMethod-settings .select-lang').val()
				},
				function (response) {
					settings_payment.openPaymentModalWindow(response.data);
				});
			});

			// Сохранение при нажатии на кнопку сохранить в модальном окне способа оплаты
			$('#tab-paymentMethod-settings').on('click', '.save-button', function() {
				settings_payment.savePaymentMethod($(this).attr('id'));
			});

			//Клик по ссылке для установки скидки/наценки способа оплаты
			$('#tab-paymentMethod-settings').on('click', '#add-paymentMethod-wrapper .discount-setup-rate', function() {
				$(this).hide();
				$('.discount-rate-control').show();
			});

			//Клик по отмене скидки/наценки 
			$('#tab-paymentMethod-settings').on('click', '#add-paymentMethod-wrapper .cancel-rate', function() {
				$('.discount-setup-rate').show();
				$('.discount-rate-control').hide();
				$('.discount-rate-control input[name=rate]').val(0);
			});

			// Клик по кнопке для смены скидки/наценки
			$('#tab-paymentMethod-settings').on('click', '#add-paymentMethod-wrapper .discount-change-rate', function() {
				$('.select-rate-block').show();
			});

			// Клик по кнопке для  отмены модалки смены скидки/наценки
			$('#tab-paymentMethod-settings').on('click', '#add-paymentMethod-wrapper .cancel-rate-dir', function() {
				$('.select-rate-block').hide();  
				if($('.rate-dir').text()=="+") {
					$('.select-rate-block select[name=change_rate_dir] option[value=up]').prop('selected','selected');
				}
				if($('.rate-dir').text()=="-") {
					$('.select-rate-block select[name=change_rate_dir] option[value=down]').prop('selected','selected');
				}
			});

			// Клик по кнопке для применения скидки/наценки
			$('#tab-paymentMethod-settings').on('click', '#add-paymentMethod-wrapper .apply-rate-dir', function() {
				$('.select-rate-block').hide();        
				if($('.select-rate-block select[name=change_rate_dir]').val()=='up') {
					settings_payment.setupDirRate(1);
				} else {
					settings_payment.setupDirRate(-1);
				}
			});

			$('#tab-paymentMethod-settings').on('click', '.createNewPaymentMethod', function() {
				settings_payment.createPaymentModalOpen();
			});

			$('#tab-paymentMethod-settings').on('click', '.deletePayment', function() {
				if(!confirm('Удалить способ оплаты?')) return false;
				settings_payment.deletePayment($(this).data('id'));
			});
		},
		/**
		 * Открывает модальное окно способа оплаты.
		 */
		openPaymentModalWindow: function(data) {  
		 var paramArray = JSON.parse($('tr[id=payment_'+data.id+'] td#paramHideArray').text());     
			//проверка ниличия сопособов доставки для данного метода      
			if('' != $('tr[id=payment_'+data.id+'] td#deliveryHideMethod').text()) {
				var deliveryMethod = $.parseJSON($('tr[id=payment_'+data.id+'] td#deliveryHideMethod').text());
			}

			settings_payment.clearFileds();
			$('#add-paymentMethod-wrapper .payment-table-icon').text(lang.TITLE_EDIT_PAYMENT);
			$('#add-paymentMethod-wrapper .save-button').attr("id", data.id);
			//подстановка классов иконок
			switch (data.id) {
				case "1":
					var iconClass = 'wm_icon';
					break;
				case "2":
					var iconClass = 'ym_icon';
					break;
				case "5":
					var iconClass = 'robo_icon';
					break;
				case "6":
					var iconClass = 'qiwi_icon';
					break;
				case "8":
					var iconClass = 'sci_icon';
					break;
				case "9":
					var iconClass = 'payanyway_icon';
					break;
				case "10":
					var iconClass = 'paymenmaster_icon';
					break;
				case "11":
					var iconClass = 'alfabank_icon';
					break;      
				default:
					var iconClass = 'default_icon';
			}
			$('#add-paymentMethod-wrapper span#paymentName').html('<span class="'+iconClass+'">'+'<input class="name-payment" name="name" type="text" value="'+data.name+'">'+'</span>');
			
			if('' != $('tr[id=payment_'+data.id+'] td#urlArray').text()) {
				var urlArray = $.parseJSON($('tr[id=payment_'+data.id+'] td#urlArray').text());
				var urlParam = '<div class="custom-text links-text" style="margin-bottom:7px;"><strong>'+lang.LINKS_SERVICE+''+$('tr[id=payment_'+data.id+'] td#paymentName').text()+':</strong></div>';
				var k=1;
				$.each(urlArray, function(name, val) {
					if(k==1) {urlParam += '<p class="alert-block warning">'}
					if(k==2) {urlParam += '<p class="alert-block success">'}
					if(k==3) {urlParam += '<p class="alert-block alert">'}
					if(k==4) {urlParam += '<p class="alert-block refund">'}
					urlParam += '<span>'+name+'</span>\
											'+admin.SITE+val+'\
										</p>';
					k++;
				});
				$('#add-paymentMethod-wrapper #urlParam').html(urlParam);
			}
			//создание списка изменения параметров для данного способа оплаты
			var input = '';
			var algorithm = new Array('md5', 'sha256', 'sha1');
				$('#add-paymentMethod-wrapper #paymentParam').html('');
			var yandexNDS = new Array('без НДС', '0%', '10%','20%');

			if(data.id == 23) {
				$('#paymentParam').html('<div class="alert-block info credit-info">\
					 Данный способ оплаты является дополнительным для способа оплаты "Яндекс.Касса".\
					 Задать настройки необходимо в редактировании способа оплаты "Яндекс.Касса"<br>\
					 Чтобы сделать доступным данный способ оплаты, вам так же необходимо иметь договор с "Яндекс.Кассой"</div>');
			} else {
				$('.credit-info').detach();
			}

			var comepayPayattributs = [];
				comepayPayattributs[1] ='Полная предварительная оплата до момента передачи предмета расчёта';
				comepayPayattributs[2] ='Частичная предварительная оплата до момента передачи предмета расчёта';
				comepayPayattributs[3] ='Аванс';
				comepayPayattributs[4] ='Полная оплата, в том числе с учётом аванса (предварительной оплаты) в момент передачи предмета расчёта';
				comepayPayattributs[5] ='Частичная оплата предмета расчёта в момент его передачи с последующей оплатой в кредит';
				comepayPayattributs[6] ='Передача предмета расчёта без его оплаты в момент его передачи с последующей оплатой в кредит';
				comepayPayattributs[7] ='Оплата предмета расчёта после его передачи с оплатой в кредит (оплата кредита). Этот признак должен быть единственным в документе и документ с этим признаком может содержать только одну строку';

			var comepayVats = [];
				comepayVats[1] = 'НДС не облагается';
				comepayVats[2] = 'НДС 10%';
				comepayVats[3] = 'НДС 20%';

			var cloudpaymentsSCHEME = {
				'charge': 'Одностадийная',
				'auth': 'Двухстадийная',
			};
		
				var cloudpaymentsSKIN = {
				'classic': 'Classic',
				'modern': 'Modern',
				'mini': 'Mini',
			};

			var cloudpaymentsTS = {
				'ts_0': 'Общая система налогообложения',
				'ts_1': 'Упрощенная система налогообложения (Доход)',
				'ts_2': 'Упрощенная система налогообложения (Доход минус Расход)',
				'ts_3': 'Единый налог на вмененный доход',
				'ts_4': 'Единый сельскохозяйственный налог',
				'ts_5': 'Патентная система налогообложения',
			};

			var cloudpaymentsVat = {
				'vat_none': 'НДС не облагается',
				'vat_0': 'НДС 0%',
				'vat_10': 'НДС 10%',
				'vat_20': 'НДС 20%',
				'vat_110': 'Расчетный НДС 10/110',
				'vat_120': 'Расчетный НДС 20/120',
			};

			var cloudpaymentsLang = {
				'ru-RU': 'Русский (MSK)',
				'en-US': 'Английский (CET)',
				'lv': 'Латышский (CET)',
				'az': 'Азербайджанский (AZT)',
				'kk': 'Русский (ALMT)',
				'kk-KZ': 'Казахский (ALMT)',
				'uk': 'Украинский (EET)',
				'pl': 'Польский (CET)',
				'pt': 'Португальский (CET)'
			};
			var cloudpaymentsMethod = {
				'0': 'Неизвестный способ расчета',
				'1': 'Предоплата 100%',
				'2': 'Предоплата',
				'3': 'Аванс',
				'4': 'Полный расчёт',
				'5': 'Частичный расчёт и кредит',
				'6': 'Передача в кредит',
				'7': 'Оплата кредита'
			};
			var cloudpaymentsObject = {
				'0': 'Неизвестный предмет оплаты',
				'1': 'Товар',
				'2': 'Подакцизный товар',
				'3': 'Работа',
				'4': 'Услуга',
				'5': 'Ставка азартной игры',
				'6': 'Выигрыш азартной игры',
				'7': 'Лотерейный билет',
				'8': 'Выигрыш лотереи',
				'9': 'Предоставление РИД',
				'10': 'Платеж',
				'11': 'Агентское вознаграждение',
				'12': 'Составной предмет расчета',
				'13': 'Иной предмет расчета',
			};
            
           var cloudpaymentsStatus = {
				'0': 'Не подтвержден',
				'1': 'Ожидает оплаты',
				'2': 'Оплачен',
				'3': 'В доставке',
				'4': 'Отменен',
				'5': 'Выполнен',
				'6': 'В обработке'
			};
            
			var sberNDS = [];
				sberNDS[0] = 'без НДС';
				sberNDS[1] = 'НДС по ставке 0%';
				sberNDS[2] = 'НДС чека по ставке 10%';
				sberNDS[3] = 'НДС чека по ставке 20%';
				sberNDS[4] = 'НДС чека по расчетной ставке 10/110';
				sberNDS[5] = 'НДС чека по расчетной ставке 18/118';

			var sberTaxSystem = [];
				sberTaxSystem[0] = 'общая';
				sberTaxSystem[1] = 'упрощённая, доход';
				sberTaxSystem[2] = 'упрощённая, доход минус расход';
				sberTaxSystem[3] = 'единый налог на вменённый доход';
				sberTaxSystem[4] = 'единый сельскохозяйственный налог';
				sberTaxSystem[5] = 'патентная система налогообложения';

			var tinkoffTaxSystem = {
				'osn':'Общая СН',
				'usn_income':'Упрощенная СН (доходы)',
				'usn_income_outcome':'Упрощенная СН (доходы минус расходы)',
				'envd':'Единый налог на вмененный доход',
				'esn':'Единый сельскохозяйственный налог',
				'patent':'Патентная СН',
			};

			var tinkoffNDS = {
				'none':'Без НДС',
				'vat0':'НДС 0%',
				'vat10':'НДС 10%',
				'vat20':'НДС 20%',
			};

			var numberOfCheckbox = 0;
		 
			$.each(paramArray, function(name, val) {  
				var inpType = "text";
				if(name.indexOf('ароль') + 1) {
					inpType = "password";
				}
				if(name.indexOf('екретн') + 1) {
					inpType = "password";
				}
				if(name.indexOf('од проверки ') + 1) {
					inpType = "password";
				}
				if(name.indexOf('естовый') + 1) {
					 inpType = "checkbox";
				}
				if(name.indexOf('пользовать онлайн кас') + 1) {
					 inpType = "checkbox";
				}

				/*COMEPAY*/
				if('Callback Password' === name) {
						inpType = "password";
				}

				if ('Разрешить печать чеков в ККТ' === name) {
						inpType = "checkbox";
				}

				if ('НДС на товары' === name || 'НДС на доставку' === name) {
						var options = '';
						if (data.id == '17') {
							sberNDS.forEach(function(arr, i, e) {
								options += '<option value="'+i+'">'+arr+'</option>';
							});
						}
						if (data.id == '18') {
							$.each(tinkoffNDS, function(key, val) {
								options += '<option value="'+key+'">'+val+'</option>';
							});
						}
						if (data.id == '20') {
							comepayVats.forEach(function(arr, i, e) {
								options += '<option value="'+i+'">'+arr+'</option>';
							});
						}
						
						$('#add-paymentMethod-wrapper #paymentParam').append(
								'<div class="row">\
									<div class="small-5 columns">\
										<label class="middle">'+name+'</label>\
							</div>\
							<div class="small-7 columns">\
							<div class="select medium">\
								<select name="'+name+'">'+options+'</select>\
							</div>\
						</div>');
						val = admin.htmlspecialchars_decode(val);
						$('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
						return;
				}

				if ('Признак способа расчёта' === name) {
						var options = '';
						comepayPayattributs.forEach(function(arr, i, e) {
								options += '<option value="'+i+'">'+arr+'</option>';
						});
						$('#add-paymentMethod-wrapper #paymentParam').append(
								'<div class="row">\
									<div class="small-5 columns">\
										<label class="middle">'+name+'</label>\
						</div>\
						<div class="small-7 columns">\
						<div class="select medium">\
							<select name="'+name+'">'+options+'</select>\
						</div>\
					</div>');
						val = admin.htmlspecialchars_decode(val);
						$('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
						return;
				}
				/* END COMEPAY*/

				if(name.indexOf('С, включенный в це') + 1 && data.id == '14') {
					var options = '';
					yandexNDS.forEach(function(arr, i, e) {
						options += '<option value="'+arr+'">'+arr+'</option>';
					});
					$('#add-paymentMethod-wrapper #paymentParam').append(
						'<div class="row">\
							<div class="small-5 columns">\
								<label class="middle">'+name+'</label>\
							</div>\
							<div class="small-7 columns">\
							<div class="select medium">\
								<select name="'+name+'">'+options+'</select>\
							</div>\
						</div>');
					val = admin.htmlspecialchars_decode(val);
					$('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
					return; 
				}
				if(name.indexOf(lang.CRIPT_METHOD) + 1) {
					var options = '<option value="0">'+lang.CHOOSE+':</option>';
					algorithm.forEach(function(arr, i, e) {
						options += '<option value="'+arr+'">'+arr+'</option>';
					});
					$('#add-paymentMethod-wrapper #paymentParam').append(
						'<div class="row">\
							<div class="small-5 columns">\
								<label class="middle">'+name+'</label>\
							</div>\
							<div class="small-7 columns">\
							<div class="select medium">\
								<select name="'+name+'">'+options+'</select>\
							</div>\
						</div>');
					val = admin.htmlspecialchars_decode(val);
					$('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
					return; 
				}

				// cloudPayment
				if(name.indexOf('С, включенный в це') + 1) {
					var options = '';
					yandexNDS.forEach(function(arr, i, e) {
						options += '<option value="'+arr+'">'+arr+'</option>';
					});
					$('#add-paymentMethod-wrapper #paymentParam').append(
						'<div class="row">\
							<div class="small-5 columns">\
								<label class="middle">'+name+'</label>\
							</div>\
							<div class="small-7 columns">\
							<div class="select medium">\
								<select name="'+name+'">'+options+'</select>\
							</div>\
						</div>');
					val = admin.htmlspecialchars_decode(val);
					$('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
					return; 
				}
				if(name.indexOf(lang.CRIPT_METHOD) + 1) {
					var options = '<option value="0">'+lang.CHOOSE+':</option>';
					algorithm.forEach(function(arr, i, e) {
						options += '<option value="'+arr+'">'+arr+'</option>';
					});
					$('#add-paymentMethod-wrapper #paymentParam').append(
						'<div class="row">\
							<div class="small-5 columns">\
								<label class="middle">'+name+'</label>\
							</div>\
							<div class="small-7 columns">\
							<div class="select medium">\
								<select name="'+name+'">'+options+'</select>\
							</div>\
						</div>');
					val = admin.htmlspecialchars_decode(val);
					$('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
					return; 
				}
				if(name.indexOf('истема налогообложения') + 1) {
						var options = '';
						if (data.id == '17') {
							sberTaxSystem.forEach(function(arr, i, e) {
								options += '<option value="'+i+'">'+arr+'</option>';
							});
						}
						if (data.id == '18') {
							$.each(tinkoffTaxSystem, function(key, val) {
								options += '<option value="'+key+'">'+val+'</option>';
							});
						}
						if (data.id == '22') {
							$.each(cloudpaymentsTS, function(key, val) {
								options += '<option value="'+key+'">'+val+'</option>';
							});
						}

						$('#add-paymentMethod-wrapper #paymentParam').append(
								'<div class="row">\
									<div class="small-5 columns">\
										<label class="middle">'+name+'</label>\
						</div>\
						<div class="small-7 columns">\
						<div class="select medium">\
							<select name="'+name+'">'+options+'</select>\
						</div>\
					</div>');
						val = admin.htmlspecialchars_decode(val);
						$('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
						return;
				}
				if(name.indexOf('тавка НДС') + 1) {
					var options = '';
					$.each(cloudpaymentsVat, function(key, val) {
						options += '<option value="'+key+'">'+val+'</option>';
					});
					// console.log(cloudpaymentsVat);
					// console.log(options);
					$('#add-paymentMethod-wrapper #paymentParam').append(
						'<div class="row">\
							<div class="small-5 columns">\
								<label class="middle">'+name+'</label>\
						</div>\
						<div class="small-7 columns">\
						<div class="select medium">\
							<select name="'+name+'">'+options+'</select>\
						</div>\
					</div>');
					val = admin.htmlspecialchars_decode(val);
					$('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
					return;
				}
				if(name.indexOf('зык виджета') + 1) {
					var options = '';
					$.each(cloudpaymentsLang, function(key, val) {
						options += '<option value="'+key+'">'+val+'</option>';
					});
					$('#add-paymentMethod-wrapper #paymentParam').append(
						'<div class="row">\
							<div class="small-5 columns">\
								<label class="middle">'+name+'</label>\
						</div>\
						<div class="small-7 columns">\
						<div class="select medium">\
							<select name="'+name+'">'+options+'</select>\
						</div>\
					</div>');
					val = admin.htmlspecialchars_decode(val);
					$('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
					return;
				}

				if(name.indexOf('изайн виджета') + 1) {
					var options = '';
					$.each(cloudpaymentsSKIN, function(key, val) {
						options += '<option value="'+key+'">'+val+'</option>';
					});
					$('#add-paymentMethod-wrapper #paymentParam').append(
						'<div class="row">\
							<div class="small-5 columns">\
							<label class="middle">'+name+'</label>\
						</div>\
						<div class="small-7 columns">\
						<div class="select medium">\
						<select name="'+name+'">'+options+'</select>\
						</div>\
					</div>');
					val = admin.htmlspecialchars_decode(val);
					$('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
					return;
				}

				if(name.indexOf('хема проведения платежа') + 1) {
				var options = '';
				$.each(cloudpaymentsSCHEME, function(key, val) {
					options += '<option value="'+key+'">'+val+'</option>';
				});
				$('#add-paymentMethod-wrapper #paymentParam').append(
					'<div class="row">\
						<div class="small-5 columns">\
						<label class="middle">'+name+'</label>\
					</div>\
					<div class="small-7 columns">\
					<div class="select medium">\
					<select name="'+name+'">'+options+'</select>\
					</div>\
				</div>');
				val = admin.htmlspecialchars_decode(val);
				$('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
				return;
				}
				
				if(name.indexOf('пособ расчета') + 1) {
					var options = '';
					$.each(cloudpaymentsMethod, function(key, val) {
						options += '<option value="'+key+'">'+val+'</option>';
					});
					$('#add-paymentMethod-wrapper #paymentParam').append(
						'<div class="row">\
							<div class="small-5 columns">\
							<label class="middle">'+name+'</label>\
						</div>\
						<div class="small-7 columns">\
						<div class="select medium">\
						<select name="'+name+'">'+options+'</select>\
						</div>\
					</div>');
					val = admin.htmlspecialchars_decode(val);
					$('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
					return;
				}
				
				if(name.indexOf('редмет расчета') + 1) {
					var options = '';
					$.each(cloudpaymentsObject, function(key, val) {
						options += '<option value="'+key+'">'+val+'</option>';
					});
					$('#add-paymentMethod-wrapper #paymentParam').append(
						'<div class="row">\
							<div class="small-5 columns">\
							<label class="middle">'+name+'</label>\
						</div>\
						<div class="small-7 columns">\
						<div class="select medium">\
						<select name="'+name+'">'+options+'</select>\
						</div>\
					</div>');
					val = admin.htmlspecialchars_decode(val);
					$('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
					return;
				}
				
				if(name.indexOf('татус заказа для печати второго чека') + 1) {
					var options = '';
					$.each(cloudpaymentsStatus, function(key, val) {
						options += '<option value="'+key+'">'+val+'</option>';
					});
					$('#add-paymentMethod-wrapper #paymentParam').append(
						'<div class="row">\
							<div class="small-5 columns">\
							<label class="middle">'+name+'</label>\
						</div>\
						<div class="small-7 columns">\
						<div class="select medium">\
						<select name="'+name+'">'+options+'</select>\
						</div>\
					</div>');
					val = admin.htmlspecialchars_decode(val);
					$('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
					return;
				}
				// cloudPayment end
				
				switch(inpType) {
					case 'checkbox':
						numberOfCheckbox++;
						input = '<div class="checkbox margin">\
											<input id="cr' + numberOfCheckbox + '" type="checkbox" name="'+name+'">\
											<label for="cr' + numberOfCheckbox + '"></label>\
										</div>';
						break;
					default:
						input = '<input type="'+inpType+'" name="'+name+'" class="product-name-input" value="">';
						break;
				}
				 
				$('#add-paymentMethod-wrapper #paymentParam').append(
					'<div class="row">\
						<div class="small-5 columns">\
							<label class="middle">'+name+'</label>\
						</div>\
						<div class="small-7 columns">\
							'+input+'\
						</div>\
					</div>'
				);
				val = admin.htmlspecialchars_decode(val);
				$('#add-paymentMethod-wrapper #paymentParam input[name="'+name+'"]').val(val);
				if (inpType=='checkbox'&&val=='true') {
					$('#add-paymentMethod-wrapper #paymentParam input[name="'+name+'"]').attr('checked', 'checked');
				}
			});
			
			// вешаем текстовый редактор на поле в реквизитах
			$('textarea[class=product-name-input]').ckeditor();  
			//ниличие сопобов доставки для данного метода
			if(!$.isEmptyObject(deliveryMethod)) {
				//выбор способов доставки применительно к данному способу оплаты
				$.each(deliveryMethod, function(deliveryId, active) {
					if(1 == active) {
						$('#add-paymentMethod-wrapper #deliveryCheckbox input[name='+deliveryId+']').prop('checked', true);
					} else {
						$('#add-paymentMethod-wrapper #deliveryCheckbox input[name='+deliveryId+']').prop('checked', false);
					}
				});
			} else {
				// $('#add-paymentMethod-wrapper #deliveryArray').html(lang.NONE_DELIVERY);
			}
			//выбор активности данного способа оплаты
			if(1 == $('tr[id=payment_'+data.id+'] td .activity').attr('status')) {
				$('input[name=paymentActivity]').prop('checked', true);
			}
			
			var rate = $('tr[id=payment_'+data.id+'] td#paymentRate').text();      
			$('.discount-rate-control input[name=rate]').val(rate*100);
			if(rate != 0) {
				$('.discount-setup-rate').hide();
				$('.discount-rate-control').show();
				settings_payment.setupDirRate(rate);  
			}

			$('#add-paymentMethod-wrapper #paymentParam').append(
				'<div class="row">\
					<div class="small-5 columns">\
						<label class="middle">'+lang.PAYMENT_PERMISSION+':</label>\
					</div>\
					<div class="small-7 columns">\
						<select class="medium permission">\
							<option value="all">'+lang.PAYMENT_ALL+'</option>\
							<option value="fiz">'+lang.PAYMENT_FIZ+'</option>\
							<option value="yur">'+lang.PAYMENT_YUR+'</option>\
						</select>\
					</div>\
				</div>'
			);

			$('#add-paymentMethod-wrapper .permission').val(data.permission);

			// Вызов модального окна.
			admin.openModal('#add-paymentMethod-wrapper');
		},
		/**
		 * сохранение способа оплаты
		 */
		savePaymentMethod:function(id) {      
			$('.img-loader').show();
		 
			//обрабатываем параметры методов оплаты
			var name = admin.htmlspecialchars($('.name-payment').val());   
			
			
			//обрабатываем параметры методов оплаты
			var paymentParam ='{';
			$('#paymentParam input,#paymentParam select').each(function() {
					if(!$(this).hasClass('name-payment') && !$(this).hasClass('permission')) {
					 // paymentParam+='"'+$(this).attr('name')+'":"'+$(this).val().replace(/\\/g, '\\\\\\\\').replace(/"/g, '\\\\$&')+'",';
					 paymentParam+='"'+$(this).attr('name')+'":"'+admin.htmlspecialchars($(this).val().replace(/'/g, '"'))+'",';
					}
			});   
			
			paymentParam = paymentParam.substr(0, paymentParam.length-1); //удаляем последнюю запятую в конце списка
			paymentParam+='}';
			
			var deliveryMethod='';
			if(0 != $('#deliveryCheckbox #deliveryArray').find('input').length) {
				//обрабатываем доступные методы доставки для данного метода оплаты

				deliveryMethod='{';
				$('#deliveryCheckbox input').each(function() {

					if($(this).prop('checked')) {
						deliveryMethod += '"'+admin.htmlspecialchars($(this).attr('name'))+'":1,';
					} else {
						deliveryMethod += '"'+admin.htmlspecialchars($(this).attr('name'))+'":0,';
					}
				});

				deliveryMethod = deliveryMethod.substr(0, deliveryMethod.length-1); //удаляем последнюю запятую в конце списка
				deliveryMethod +='}';
			}            
			
			var rate = $('.discount-rate-control input[name=rate]').val();
			
			if(rate!=0) {
				rate = rate/100;
			}
			
			if($('.rate-dir').text()!='+') {
				rate = -1*rate;
			}
			
			//активность метода оплаты
			var paymentActivity = 0;
			if($('input[name=paymentActivity]').prop('checked')) {
				paymentActivity = 1;
			}
			
			admin.ajaxRequest({
				mguniqueurl: "action/savePaymentMethod",
				paymentParam: paymentParam,
				deliveryMethod: deliveryMethod,
				paymentActivity: paymentActivity,
				name: name,
				rate: rate,
				paymentId: id,
				permission: $('#tab-paymentMethod-settings .permission').val(),
				lang: $('#tab-paymentMethod-settings .select-lang').val()
			},
			function(response) {
				$('.img-loader').hide();
				admin.indication(response.status, response.msg);
				if('success' == response.status) {
					admin.closeModal('#add-paymentMethod-wrapper');
					admin.refreshPanel();
				}
			}
			);
		},
		setupDirRate: function(rate) {         
			if(rate>=0) {
				$('#add-paymentMethod-wrapper select[name=change_rate_dir] option[value=up]').prop('selected','selected');
				$('#add-paymentMethod-wrapper .discount-rate').removeClass('color-down').addClass('color-up');
				$('.rate-dir').text('+');
				$('.rate-dir-name span').text(lang.DISCOUNT_UP);
				$('.discount-rate-control input[name=rate]').val(Math.abs($('.discount-rate-control input[name=rate]').val()));
			} else {
				$('#add-paymentMethod-wrapper select[name=change_rate_dir] option[value=down]').prop('selected','selected');  
				$('.rate-dir-name span').text(lang.DISCOUNT_DOWN);
				$('#add-paymentMethod-wrapper .discount-rate').removeClass('color-up').addClass('color-down');
				$('.rate-dir').text('-');
				$('.discount-rate-control input[name=rate]').val(Math.abs($('.discount-rate-control input[name=rate]').val()));
			}
		},
		createPaymentModalOpen: function() {
			$('#add-paymentMethod-wrapper span#paymentName').html('<span class="default_icon">'+'<input class="name-payment" name="name" type="text" value="">'+'</span>');
			settings_payment.clearFileds();
			$('#add-paymentMethod-wrapper .payment-table-icon').text(lang.TITLE_NEW_PAYMENT);
			$('#paymentParam').replaceWith('<div id="paymentParam"><div class="row"><div class="small-5 columns"><label class="middle">Примечание</label></div><div class="small-7 columns"><input type="text" name="Примечание" class="product-name-input" value=""></div></div><div class="row"><div class="small-5 columns"><label class="middle">Способ оплаты доступен для:</label></div><div class="small-7 columns"><select class="medium permission"><option value="all">всех</option><option value="fiz">физических лиц</option><option value="yur">юридических лиц</option></select></div></div></div>');
			// Вызов модального окна.
			admin.openModal('#add-paymentMethod-wrapper');
		},
		deletePayment: function(id) {
			$.ajax({
				type: "POST",
				url: mgBaseDir + "/ajax",
				data: {
					mguniqueurl: "action/deletePayment",
					id: id,
				},
				cache: false,
				// async: false,
				dataType: "json",
				success: function (response) {
					admin.refreshPanel();
				}
			});
		},
		clearFileds:function() {
			$('#tab-paymentMethod-settings input[name=paymentActivity]').prop('checked', false);
			$('#tab-paymentMethod-settings .deliveryMethod').prop('checked', false);
			$('#tab-paymentMethod-settings #add-paymentMethod-wrapper #urlParam').html('');
			$('#tab-paymentMethod-settings .discount-setup-rate').show();
			$('#tab-paymentMethod-settings .discount-rate-control input[name=rate]').val(0);
			$('#tab-paymentMethod-settings .discount-rate-control').hide();
			$('#tab-paymentMethod-settings #add-paymentMethod-wrapper .discount-rate').removeClass('color-down').addClass('color-up');
			$('#tab-paymentMethod-settings .save-button').attr('id','');
			$('#tab-paymentMethod-settings input').removeClass('error-input');
			$('#tab-paymentMethod-settings .errorField').css('display','none');
		},
		changeActivity: function(id, status) {
			if(status) status = 1; else status = 0;
			admin.ajaxRequest({
				mguniqueurl: "action/changeActivityDP",
				tab: 'payment',
				id: id,
				status: status
			},
			function(response) {
				admin.indication(response.status, response.msg);
			});
		},
	};
})();