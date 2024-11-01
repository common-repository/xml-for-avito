<?php
/**
 * Set and Get the Plugin Data
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.5.1 (29-10-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param        
 *
 * @depends                 classes:    
 *                          traits:     
 *                          methods:    
 *                          functions:  
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

class XFAVI_Data_Arr {
	/**
	 * Plugin options array
	 * @var array
	 */
	private $data_arr = [];
	/**
	 * Manager name
	 * @var string
	 */
	private $manager_name;

	/**
	 * Set and Get the Plugin Data
	 * 
	 * @param string $manager_name - Optional
	 * @param array $data_arr - Optional
	 */
	public function __construct( $manager_name = '', $data_arr = [] ) {
		$this->data_arr = [ 
			[ 
				'opt_name' => 'xfavi_status_sborki',
				'def_val' => '-1',
				'mark' => 'private',
				'required' => true,
				'type' => 'auto',
				'tab' => 'none'
			],
			[ 
				'opt_name' => 'xfavi_date_sborki',
				'def_val' => '-', // 'Y-m-d H:i
				'mark' => 'private',
				'required' => true,
				'type' => 'auto',
				'tab' => 'none'
			],
			[ 
				'opt_name' => 'xfavi_date_sborki_end',
				'def_val' => '-', // 'Y-m-d H:i
				'mark' => 'private',
				'required' => true,
				'type' => 'auto',
				'tab' => 'none'
			],
			[ 
				'opt_name' => 'xfavi_date_save_set',
				'def_val' => 0000000001, // 0000000001 - timestamp format
				'mark' => 'private',
				'required' => true,
				'type' => 'auto',
				'tab' => 'none'
			],
			[ 
				'opt_name' => 'xfavi_count_products_in_feed',
				'def_val' => '-1',
				'mark' => 'private',
				'required' => true,
				'type' => 'auto',
				'tab' => 'none'
			],
			[ 
				'opt_name' => 'xfavi_file_url',
				'def_val' => '',
				'mark' => 'private',
				'required' => true,
				'type' => 'auto',
				'tab' => 'none'
			],
			[ 
				'opt_name' => 'xfavi_file_file',
				'def_val' => '',
				'mark' => 'private',
				'required' => true,
				'type' => 'auto',
				'tab' => 'none'
			],
			[ 
				'opt_name' => 'xfavi_stock_file_url',
				'def_val' => '',
				'mark' => 'private',
				'required' => true,
				'type' => 'auto',
				'tab' => 'none'
			],
			[ 
				'opt_name' => 'xfavi_stock_file_path',
				'def_val' => '',
				'mark' => 'private',
				'required' => true,
				'type' => 'auto',
				'tab' => 'none'
			],
			[ 
				'opt_name' => 'xfavi_status_cron',
				'def_val' => 'off',
				'mark' => 'private',
				'required' => true,
				'type' => 'auto',
				'tab' => 'none'
			],
			[ // сюда будем записывать критически ошибки при сборке фида
				'opt_name' => 'xfavi_critical_errors',
				'def_val' => '',
				'mark' => 'private',
				'required' => true,
				'type' => 'auto',
				'tab' => 'none'
			],
			// ------------------- ОСНОВНЫЕ НАСТРОЙКИ -------------------
			[ 
				'opt_name' => 'xfavi_run_cron',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => true,
				'type' => 'select',
				'tab' => 'main_tab',
				'data' => [ 
					'label' => __( 'Автоматическое создание файла', 'xml-for-avito' ),
					'desc' => __( 'Интервал обновления вашего фида', 'xml-for-avito' ),
					'woo_attr' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 
							'value' => 'once',
							'text' => sprintf( '%s (%s)',
								__( 'Создать фид один раз', 'xml-for-avito' ),
								__( 'запустить сейчас', 'xml-for-avito' )
							)
						],
						[ 'value' => 'hourly', 'text' => __( 'Раз в час', 'xml-for-avito' ) ],
						[ 'value' => 'six_hours', 'text' => __( 'Каждые 6 часов', 'xml-for-avito' ) ],
						[ 'value' => 'twicedaily', 'text' => __( '2 раза в день', 'xml-for-avito' ) ],
						[ 'value' => 'daily', 'text' => __( 'Раз в день', 'xml-for-avito' ) ],
						[ 'value' => 'week', 'text' => __( 'Раз в неделю', 'xml-for-avito' ) ]
					]
				]
			],
			[ 
				'opt_name' => 'xfavi_ufup',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => true,
				'type' => 'select',
				'tab' => 'main_tab',
				'data' => [ 
					'label' => __( 'Обновить фид при обновлении карточки товара', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 'value' => 'on', 'text' => __( 'Включено', 'xml-for-avito' ) ]
					]
				]
			],
			[ 
				'opt_name' => 'xfavi_feed_assignment',
				'def_val' => '',
				'mark' => 'public',
				'required' => true,
				'type' => 'text',
				'tab' => 'main_tab',
				'data' => [ 
					'label' => __( 'Назначение фида', 'xml-for-avito' ),
					'desc' => __( 'Не используется в фиде. Внутренняя заметка для вашего удобства', 'xml-for-avito' ),
					'placeholder' => __( 'Для Авито', 'xml-for-avito' ),
					'tr_class' => 'xfavi_tr'
				]
			],
			[ 
				'opt_name' => 'xfavi_feed_name',
				'def_val' => '',
				'mark' => 'public',
				'required' => true,
				'type' => 'text',
				'tab' => 'main_tab',
				'data' => [ 
					'label' => __( 'Имя файла фида', 'xml-for-avito' ),
					'desc' => sprintf( '%s. <strong>%s:</strong> %s!',
						__(
							'Если оставить поле пустым, то будет использоваться значение по умолчанию',
							'xml-for-avito'
						),
						__( 'Важно', 'xml-for-avito' ),
						__(
							'Пробелы использовать нельзя',
							'xml-for-avito'
						)
					),
					'placeholder' => 'feed-avito-0',
					'tr_class' => ''
				]
			],
			[ 
				'opt_name' => 'xfavi_file_extension',
				'def_val' => 'xml',
				'mark' => 'public',
				'required' => true,
				'type' => 'select',
				'tab' => 'main_tab',
				'data' => [ 
					'label' => __( 'Расширение файла фида', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => false,
					'key_value_arr' => [ 
						[ 'value' => 'xml', 'text' => 'XML (' . __( 'рекомендуется', 'xml-for-avito' ) . ')' ]
						// TODO: в перспективе добавить csv и xls
					]
				]
			],
			[ 
				'opt_name' => 'xfavi_archive_to_zip',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => true,
				'type' => 'select',
				'tab' => 'main_tab',
				'data' => [ 
					'label' => __( 'Архивировать в ZIP', 'xml-for-avito' ),
					'desc' => sprintf( '%s: %s',
						__( 'По умолчанию', 'xml-for-avito' ),
						__( 'Отключено', 'xml-for-avito' )
					),
					'woo_attr' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 'value' => 'enabled', 'text' => __( 'Включено', 'xml-for-avito' ) ]
					]
				]
			],
			[ 
				'opt_name' => 'xfavi_step_export',
				'def_val' => '500',
				'mark' => 'public',
				'required' => true,
				'type' => 'select',
				'tab' => 'main_tab',
				'data' => [ 
					'label' => __( 'Шаг экспорта', 'xml-for-avito' ),
					'desc' =>
						sprintf( '%s. %s. %s',
							__( 'Значение влияет на скорость создания XML фида', 'xml-for-avito' ),
							__(
								'Если у вас возникли проблемы с генерацией файла - попробуйте уменьшить значение в данном поле',
								'xml-for-avito'
							),
							__( 'Более 500 можно устанавливать только на мощных серверах', 'xml-for-avito' )
						),
					'woo_attr' => false,
					'key_value_arr' => [ 
						[ 'value' => '80', 'text' => '80' ],
						[ 'value' => '200', 'text' => '200' ],
						[ 'value' => '300', 'text' => '300' ],
						[ 'value' => '450', 'text' => '450' ],
						[ 'value' => '500', 'text' => '500' ],
						[ 'value' => '800', 'text' => '800' ],
						[ 'value' => '1000', 'text' => '1000' ]
					],
					'tr_class' => 'xfavi_tr'
				]
			],
			// -------------------------- ВКЛАДКА ДАННЫЕ МАГАЗИНА --------------------------
			[ 
				'opt_name' => 'xfavi_address',
				'def_val' => '',
				'mark' => 'public',
				'required' => true,
				'type' => 'text',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Адрес', 'xml-for-avito' ),
					'desc' => __( 'Полный адрес объекта — строка до 256 символов', 'xml-for-avito' ),
					'default_value' => false,
					'placeholder' => '',
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'Address'
				]
			],
			[ 
				'opt_name' => 'xfavi_managerName',
				'def_val' => '',
				'mark' => 'public',
				'required' => true,
				'type' => 'text',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Имя менеджера', 'xml-for-avito' ),
					'desc' => sprintf( '%s ManagerName. %s',
						__( 'Элемент', 'xml-for-avito' ),
						__(
							'Имя менеджера, контактного лица компании по данному объявлению — строка не более 40 символов',
							'xml-for-avito'
						)
					),
					'default_value' => false,
					'placeholder' => '',
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'ManagerName'
				]
			],
			[ 
				'opt_name' => 'xfavi_contact_method',
				'def_val' => 'all',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Способ связи', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => false,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'all', 'text' => __( 'По телефону и в сообщениях', 'xml-for-avito' ) ],
						[ 'value' => 'phone', 'text' => __( 'По телефону', 'xml-for-avito' ) ],
						[ 'value' => 'msg', 'text' => __( 'В сообщениях', 'xml-for-avito' ) ]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'ContactMethod'
				]
			],
			[ 
				'opt_name' => 'xfavi_allowEmail',
				'def_val' => 'Да',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Связь по Email', 'xml-for-avito' ),
					'desc' => sprintf( '%s Allow Email. %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'Возможность написать сообщение по объявлению через сайт', 'xml-for-avito' )
					),
					'woo_attr' => false,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'Да', 'text' => __( 'Да', 'xml-for-avito' ) ],
						[ 'value' => 'Нет', 'text' => __( 'Нет', 'xml-for-avito' ) ]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'AllowEmail'
				]
			],
			[ 
				'opt_name' => 'xfavi_contactPhone',
				'def_val' => '',
				'mark' => 'public',
				'required' => true,
				'type' => 'text',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Телефон', 'xml-for-avito' ),
					'desc' => sprintf( '%s ContactPhone',
						__( 'Элемент', 'xml-for-avito' )
					),
					'default_value' => false,
					'placeholder' => '',
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'ContactPhone'
				]
			],
			[ 
				'opt_name' => 'xfavi_listing_fee',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Вариант платного размещения', 'xml-for-avito' ),
					'desc' => sprintf( '%s <strong>ListingFee</strong>. %s: 
						<br/>Package – %s
						<br/>PackageSingle – %s
						<br/>Single – %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'Согласно справке Авито', 'xml-for-avito' ),
						__(
							'размещение объявления осуществляется только при наличии подходящего пакета размещения',
							'xml-for-avito'
						),
						__(
							'при наличии подходящего пакета оплата размещения объявления произойдет с него; если нет подходящего пакета, но достаточно денег на кошельке Авито, то произойдет разовое размещение',
							'xml-for-avito'
						),
						__(
							'только разовое размещение, произойдет при наличии достаточной суммы на кошельке Авито; если есть подходящий пакет размещения, он будет проигнорирован',
							'xml-for-avito'
						)
					),
					'woo_attr' => false,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 'value' => 'Package', 'text' => sprintf( 'Package (%s)', __( 'По умолчанию', 'xml-for-avito' ) ) ],
						[ 'value' => 'PackageSingle', 'text' => 'PackageSingle' ],
						[ 'value' => 'Single', 'text' => 'Single' ]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'ListingFee'
				]
			],
			// -------------------------- ВКЛАДКА ФИЛЬТРАЦИЯ --------------------------
			[ 
				'opt_name' => 'xfavi_whot_export',
				'def_val' => 'all',
				'mark' => 'public',
				'required' => true,
				'type' => 'select',
				'tab' => 'filtration_tab',
				'data' => [ 
					'label' => __( 'Что экспортировать', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => false,
					'key_value_arr' => [ 
						[ 
							'value' => 'all',
							'text' => __( 'Вариативные и обычные товары', 'xml-for-avito' )
						],
						[ 
							'value' => 'simple',
							'text' => __( 'Только обычные товары', 'xml-for-avito' )
						]
					]
				]
			],
			[ 
				'opt_name' => 'xfavi_add_attr_to_title',
				'def_val' => 'fullexcerpt',
				'mark' => 'public',
				'required' => true,
				'type' => 'select',
				'tab' => 'filtration_tab',
				'data' => [ 
					'label' => __( 'Добавлять атрибуты в название вариативных товаров?', 'xml-for-avito' ),
					'desc' => __( 'Если включено, то будут добавлены ТОЛЬКО атрибуты вариации', 'xml-for-avito' ),
					'woo_attr' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Не добавлять', 'xml-for-avito' ) ],
						[ 'value' => 'add_only_value', 'text' => __( 'Добавлять только значения', 'xml-for-avito' ) ]
					],
					'tr_class' => 'xfavi_tr'
				]
			],
			[ 
				'opt_name' => 'xfavi_desc',
				'def_val' => 'fullexcerpt',
				'mark' => 'public',
				'required' => true,
				'type' => 'select',
				'tab' => 'filtration_tab',
				'data' => [ 
					'label' => __( 'Описание товара', 'xml-for-avito' ),
					'desc' => sprintf( '[description] - %s',
						__( 'Источник описания товара', 'xml-for-avito' )
					),
					'woo_attr' => false,
					'key_value_arr' => [ 
						[ 
							'value' => 'excerpt',
							'text' => __( 'Только Краткое описание', 'xml-for-avito' )
						],
						[ 
							'value' => 'full',
							'text' => __( 'Только Полное описание', 'xml-for-avito' )
						],
						[ 
							'value' => 'excerptfull',
							'text' => __( 'Краткое или Полное описание', 'xml-for-avito' )
						],
						[ 
							'value' => 'fullexcerpt',
							'text' => __( 'Полное или Краткое описание', 'xml-for-avito' )
						],
						[ 
							'value' => 'excerptplusfull',
							'text' => __( 'Краткое плюс Полное описание', 'xml-for-avito' )
						],
						[ 
							'value' => 'fullplusexcerpt',
							'text' => __( 'Полное плюс Краткое описание', 'xml-for-avito' )
						]
					],
					'tr_class' => 'xfavi_tr'
				]
			],
			[ 
				'opt_name' => 'xfavi_enable_tags_behavior',
				'def_val' => '',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'filtration_tab',
				'data' => [ 
					'label' => __( 'Список разрешенных тегов', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => false,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'default', 'text' => __( 'По умолчанию', 'xml-for-avito' ) ],
						[ 'value' => 'custom', 'text' => __( 'Из поля ниже', 'xml-for-avito' ) ]
					]
				]
			],
			[ 
				'opt_name' => 'xfavi_enable_tags_custom',
				'def_val' => '',
				'mark' => 'public',
				'required' => true,
				'type' => 'text',
				'tab' => 'filtration_tab',
				'data' => [ 
					'default_value' => false,
					'label' => '',
					'desc' => sprintf( '%s <code>p,br,h3</code>',
						__( 'Например', 'xml-for-avito' )
					),
					'placeholder' => 'p,br,h3'
				]
			],
			[ 
				'opt_name' => 'xfavi_the_content',
				'def_val' => 'enabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'filtration_tab',
				'data' => [ 
					'label' => __( 'Задействовать фильтр', 'xml-for-avito' ) . ' the_content',
					'desc' => sprintf( '%s: %s',
						__( 'По умолчанию', 'xml-for-avito' ),
						__( 'Включено', 'xml-for-avito' )
					),
					'woo_attr' => false,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 'value' => 'enabled', 'text' => __( 'Включено', 'xml-for-avito' ) ]
					]
				]
			],
			[ 
				'opt_name' => 'xfavi_behavior_strip_symbol',
				'def_val' => 'default',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'filtration_tab',
				'data' => [ 
					'label' => __( 'В атрибутах амперсанд', 'xml-for-avito' ),
					'desc' => sprintf( '%s: %s',
						__( 'По умолчанию', 'xml-for-avito' ),
						__( 'Включено', 'xml-for-avito' )
					),
					'woo_attr' => false,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'default', 'text' => __( 'По умолчанию', 'xml-for-avito' ) ],
						[ 'value' => 'del', 'text' => __( 'Удалить', 'xml-for-avito' ) ],
						[ 'value' => 'slash', 'text' => __( 'Заменить на', 'xml-for-avito' ) . '/' ],
						[ 'value' => 'amp', 'text' => __( 'Заменить на', 'xml-for-avito' ) . 'amp;' ]
					]
				]
			],
			[ 
				'opt_name' => 'xfavi_var_desc_priority',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'filtration_tab',
				'data' => [ 
					'label' => __(
						'Описание вариации имеет приоритет над другими',
						'xml-for-avito'
					),
					'desc' => sprintf( '%s: %s',
						__( 'По умолчанию', 'xml-for-avito' ),
						__( 'Включено', 'xml-for-avito' )
					),
					'woo_attr' => false,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 'value' => 'on', 'text' => __( 'Включено', 'xml-for-avito' ) ]
					]
				]
			],
			[ 
				'opt_name' => 'xfavi_simple_source_id',
				'def_val' => 'product_id',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'filtration_tab',
				'data' => [ 
					'label' => __( 'Источник ID для простых товаров', 'xml-for-avito' ),
					'desc' => sprintf( '%s: %s',
						__( 'По умолчанию', 'xml-for-avito' ),
						__( 'ID товара', 'xml-for-avito' )
					),
					'woo_attr' => false,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'product_id', 'text' => __( 'ID товара', 'xml-for-avito' ) ],
						[ 'value' => 'product_sku', 'text' => __( 'Артикул товара', 'xml-for-avito' ) ]
					]
				]
			],
			[ 
				'opt_name' => 'xfavi_var_source_id',
				'def_val' => 'offer_id',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'filtration_tab',
				'data' => [ 
					'label' => __( 'Источник ID для вариативных товаров', 'xml-for-avito' ),
					'desc' => sprintf( '%s: %s',
						__( 'По умолчанию', 'xml-for-avito' ),
						__( 'ID вариации', 'xml-for-avito' )
					),
					'woo_attr' => false,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'product_id', 'text' => __( 'ID товара', 'xml-for-avito' ) ],
						[ 'value' => 'offer_id', 'text' => __( 'ID вариации', 'xml-for-avito' ) ],
						[ 'value' => 'product_sku', 'text' => __( 'Артикул товара', 'xml-for-avito' ) ],
						[ 'value' => 'offer_sku', 'text' => __( 'Артикул вариации', 'xml-for-avito' ) ]
					]
				]
			],
			[ 
				'opt_name' => 'xfavi_no_default_png_products',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'filtration_tab',
				'data' => [ 
					'label' => __( 'Удалить default.png из XML', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => false,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 'value' => 'on', 'text' => __( 'Включено', 'xml-for-avito' ) ]
					]
				]
			],
			[ 
				'opt_name' => 'xfavi_skip_products_without_pic',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'filtration_tab',
				'data' => [ 
					'label' => __( 'Пропустить товары без картинок', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => false,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 'value' => 'on', 'text' => __( 'Включено', 'xml-for-avito' ) ]
					]
				]
			],
			[ 
				'opt_name' => 'xfavi_skip_missing_products',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'filtration_tab',
				'data' => [ 
					'label' => sprintf( '%s (%s)',
						__( 'Исключать товары которых нет в наличии', 'xml-for-avito' ),
						__( 'за исключением товаров, для которых разрешен предварительный заказ', 'xml-for-avito' ),
					),
					'desc' => '',
					'woo_attr' => false,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 'value' => 'on', 'text' => __( 'Включено', 'xml-for-avito' ) ]
					]
				]
			],
			[ 
				'opt_name' => 'xfavi_skip_backorders_products',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'filtration_tab',
				'data' => [ 
					'label' => __( 'Исключать из фида товары для предзаказа', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => false,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 'value' => 'on', 'text' => __( 'Включено', 'xml-for-avito' ) ]
					]
				]
			],
			[ 
				'opt_name' => 'xfavi_behavior_onbackorder',
				'def_val' => 'true',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'filtration_tab',
				'data' => [ 
					'label' => __(
						'Для товаров на предзаказ установить доступность, равную',
						'xml-for-avito'
					),
					'desc' => '',
					'woo_attr' => false,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'true', 'text' => '1' ],
						[ 'value' => 'false', 'text' => '0' ]
					],
					'tr_class' => 'xfavi_tr'
				]
			],
			// -------------------------- ВКЛАДКА НАСТРОЙКИ ТЕГОВ --------------------------
			[ 
				'opt_name' => 'xfavi_behavior_onbackorder',
				'def_val' => 'true',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Для товаров на предзаказ установить доступность равную', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => false,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'true', 'text' => 'В наличии' ],
						[ 'value' => 'false', 'text' => 'Под заказ' ]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'Availability'
				]
			],
			[ 
				'opt_name' => 'xfavi_size',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Размер', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => true,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'Size'
				]
			],
			[ 
				'opt_name' => 'xfavi_condition',
				'def_val' => 'new',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Состояние товара', 'xml-for-avito' ),
					'desc' => sprintf( '%s Condition. %s',
						__( 'Обязательный элемент', 'xml-for-avito' ),
						__( 'Задайте значение по умолчанию', 'xml-for-avito' )
					),
					'woo_attr' => false,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'new', 'text' => __( 'Новый', 'xml-for-avito' ) ],
						[ 'value' => 'bu', 'text' => __( 'Б/у', 'xml-for-avito' ) ]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'Condition'
				]
			],
			[ 
				'opt_name' => 'xfavi_vendorcode',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Номер (артикул)', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => true,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 'value' => 'sku', 'text' => __( 'Подставлять из Артикул', 'xml-for-avito' ) ]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'VendorCod'
				]
			],
			[ 
				'opt_name' => 'xfavi_oem',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => 'OEM',
					'desc' => '',
					'woo_attr' => true,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 'value' => 'sku', 'text' => __( 'Подставлять из Артикул', 'xml-for-avito' ) ]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'OEM'
				]
			],
			[ 
				'opt_name' => 'xfavi_oemoil',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => 'OEMOil',
					'desc' => '',
					'woo_attr' => true,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 'value' => 'sku', 'text' => __( 'Подставлять из Артикул', 'xml-for-avito' ) ]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'OEMOil'
				]
			],
			[ 
				'opt_name' => 'xfavi_brand',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Производитель', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => true,
					'default_value' => false,
					'brands' => true,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'Brand'
				]
			],
			[ 
				'opt_name' => 'xfavi_make',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Марка автомобиля', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => true,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 'value' => 'sku', 'text' => __( 'Подставлять из Артикул', 'xml-for-avito' ) ]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'Make'
				]
			],
			[ 
				'opt_name' => 'xfavi_model',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Модель', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => true,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 'value' => 'sku', 'text' => __( 'Подставлять из Артикул', 'xml-for-avito' ) ]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'Model'
				]
			],
			[ 
				'opt_name' => 'xfavi_generation',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Поколение автомобиля', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => true,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 'value' => 'sku', 'text' => __( 'Подставлять из Артикул', 'xml-for-avito' ) ]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'Generation'
				]
			],
			[ 
				'opt_name' => 'xfavi_gender',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Пол', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => true,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'Gender'
				]
			],
			[ 
				'opt_name' => 'xfavi_weight_for_delivery',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Вес товара (кг), может использоваться для доставки', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => true,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 
							'value' => 'woo_dimensions',
							'text' => __( 'Подставлять из "Доставка" - "Размеры"', 'xml-for-avito' )
						]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'WeightForDelivery'
				]
			],
			[ 
				'opt_name' => 'xfavi_length_for_delivery',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Длина товара (см), может использоваться для доставки', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => true,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 
							'value' => 'woo_dimensions',
							'text' => __( 'Подставлять из "Доставка" - "Размеры"', 'xml-for-avito' )
						]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'LengthForDelivery'
				]
			],
			[ 
				'opt_name' => 'xfavi_width_for_delivery',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Ширина товара (см), может использоваться для доставки', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => true,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 
							'value' => 'woo_dimensions',
							'text' => __( 'Подставлять из "Доставка" - "Размеры"', 'xml-for-avito' )
						]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'WidthForDelivery'
				]
			],
			[ 
				'opt_name' => 'xfavi_height_for_delivery',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Высота товара (см), может использоваться для доставки', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => true,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 
							'value' => 'woo_dimensions',
							'text' => __( 'Подставлять из "Доставка" - "Размеры"', 'xml-for-avito' )
						]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'HeightForDelivery'
				]
			],
			[ 
				'opt_name' => 'xfavi_length',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Длина', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => true,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 
							'value' => 'woo_dimensions',
							'text' => __( 'Подставлять из "Доставка" - "Размеры"', 'xml-for-avito' )
						]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'Length'
				]
			],
			[ 
				'opt_name' => 'xfavi_width',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Ширина', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => true,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 
							'value' => 'woo_dimensions',
							'text' => __( 'Подставлять из "Доставка" - "Размеры"', 'xml-for-avito' )
						]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'Width'
				]
			],
			[ 
				'opt_name' => 'xfavi_height',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Высота', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => true,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ],
						[ 
							'value' => 'woo_dimensions',
							'text' => __( 'Подставлять из "Доставка" - "Размеры"', 'xml-for-avito' )
						]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'Height'
				]
			],
			// TODO: Добавить смену источника ID товара xfavi_simple_source_id 
			[ 
				'opt_name' => 'xfavi_voltage',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Напряжение в вольтах', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => true,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'Voltage'
				]
			],
			[ 
				'opt_name' => 'xfavi_capacity',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Ёмкость в ампер-часах', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => true,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'Capacity'
				]
			],
			[ 
				'opt_name' => 'xfavi_dcl',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Пусковой ток в амперах', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => true,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'DCL'
				]
			],
			[ 
				'opt_name' => 'xfavi_polarity',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'wp_list_table',
				'data' => [ 
					'label' => __( 'Полярность', 'xml-for-avito' ),
					'desc' => '',
					'woo_attr' => true,
					'default_value' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Отключено', 'xml-for-avito' ) ]
					],
					'rules' => [ 
						'yandex_market'
					],
					'tag_name' => 'Polarity'
				]
			]
		];

		if ( empty( $manager_name ) ) {
			$blog_title = get_bloginfo( 'name' );
			$this->manager_name = substr( $blog_title, 0, 20 );
		}
		if ( ! empty( $data_arr ) ) {
			$this->data_arr = $data_arr;
		}

		$args_arr = [ $this->manager_name ];
		$this->data_arr = apply_filters( 'xfavi_set_default_feed_settings_result_arr_filter', $this->get_data_arr(), $args_arr );
	}

	/**
	 * Get the plugin options array
	 * 
	 * @return array
	 */
	public function get_data_arr() {
		return $this->data_arr;
	}

	/**
	 * Get data for tabs
	 * 
	 * @param string $whot
	 * 
	 * @return array Example: array([0] => opt_key1, [1] => opt_key2, ...)
	 */
	public function get_data_for_tabs( $whot = '' ) {
		$res_arr = [];
		if ( ! empty( $this->get_data_arr() ) ) {
			// echo get_array_as_string($this->get_data_arr(), '<br/>');
			for ( $i = 0; $i < count( $this->get_data_arr() ); $i++ ) {
				switch ( $whot ) {
					case "main_tab":
					case "tags_settings_tab":
					case "filtration_tab":
						if ( $this->get_data_arr()[ $i ]['tab'] === $whot ) {
							$arr = $this->get_data_arr()[ $i ]['data'];
							$arr['opt_name'] = $this->get_data_arr()[ $i ]['opt_name'];
							$arr['tab'] = $this->get_data_arr()[ $i ]['tab'];
							$arr['type'] = $this->get_data_arr()[ $i ]['type'];
							$res_arr[] = $arr;
						}
						break;
					case "wp_list_table":
						if ( $this->get_data_arr()[ $i ]['tab'] === $whot ) {
							$arr = $this->get_data_arr()[ $i ];
							$res_arr[] = $arr;
						}
						break;
					default:
						if ( $this->get_data_arr()[ $i ]['tab'] === $whot ) {
							$arr = $this->get_data_arr()[ $i ]['data'];
							$arr['opt_name'] = $this->get_data_arr()[ $i ]['opt_name'];
							$arr['tab'] = $this->get_data_arr()[ $i ]['tab'];
							$arr['type'] = $this->get_data_arr()[ $i ]['type'];
							$res_arr[] = $arr;
						}
				}
			}
			// echo get_array_as_string($res_arr, '<br/>');
			return $res_arr;
		} else {
			return $res_arr;
		}
	}

	/**
	 * Get plugin options name
	 * 
	 * @param string $whot
	 * 
	 * @return array	Example: array([0] => opt_key1, [1] => opt_key2, ...)
	 */
	public function get_opts_name( $whot = '' ) {
		$res_arr = [];
		if ( ! empty( $this->get_data_arr() ) ) {
			for ( $i = 0; $i < count( $this->get_data_arr() ); $i++ ) {
				switch ( $whot ) {
					case "public":
						if ( $this->get_data_arr()[ $i ]['mark'] === 'public' ) {
							$res_arr[] = $this->get_data_arr()[ $i ]['opt_name'];
						}
						break;
					case "private":
						if ( $this->get_data_arr()[ $i ]['mark'] === 'private' ) {
							$res_arr[] = $this->get_data_arr()[ $i ]['opt_name'];
						}
						break;
					default:
						$res_arr[] = $this->get_data_arr()[ $i ]['opt_name'];
				}
			}
			return $res_arr;
		} else {
			return $res_arr;
		}
	}

	/**
	 * Get plugin options name and default date (array)
	 * 
	 * @param string $whot
	 * 
	 * @return array	Example: array(opt_name1 => opt_val1, opt_name2 => opt_val2, ...)
	 */
	public function get_opts_name_and_def_date( $whot = 'all' ) {
		$res_arr = [];
		if ( ! empty( $this->get_data_arr() ) ) {
			for ( $i = 0; $i < count( $this->get_data_arr() ); $i++ ) {
				switch ( $whot ) {
					case "public":
						if ( $this->get_data_arr()[ $i ]['mark'] === 'public' ) {
							$res_arr[ $this->get_data_arr()[ $i ]['opt_name'] ] = $this->get_data_arr()[ $i ]['def_val'];
						}
						break;
					case "private":
						if ( $this->get_data_arr()[ $i ]['mark'] === 'private' ) {
							$res_arr[ $this->get_data_arr()[ $i ]['opt_name'] ] = $this->get_data_arr()[ $i ]['def_val'];
						}
						break;
					default:
						$res_arr[ $this->get_data_arr()[ $i ]['opt_name'] ] = $this->get_data_arr()[ $i ]['def_val'];
				}
			}
			return $res_arr;
		} else {
			return $res_arr;
		}
	}

	/**
	 * Get plugin options name and default date (stdClass object)
	 * 
	 * @param string $whot
	 * 
	 * @return array<stdClass>
	 */
	public function get_opts_name_and_def_date_obj( $whot = 'all' ) {
		$source_arr = $this->get_opts_name_and_def_date( $whot );

		$res_arr = [];
		foreach ( $source_arr as $key => $value ) {
			$obj = new stdClass();
			$obj->name = $key;
			$obj->opt_def_value = $value;
			$res_arr[] = $obj; // unit obj
			unset( $obj );
		}
		return $res_arr;
	}
}