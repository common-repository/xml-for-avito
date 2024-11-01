<?php
/**
 * Interface Hoocked
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
 * @depends                 classes:    XFAVI_Error_Log
 *                          traits:     
 *                          methods:    
 *                          functions:  common_option_get
 *                                      common_option_upd
 *                          constants:  
 *                          options:    
 */
defined( 'ABSPATH' ) || exit;

final class XFAVI_Interface_Hoocked {
	/**
	 * Interface Hoocked
	 */
	public function __construct() {
		$this->init_hooks();
		$this->init_classes();
	}

	/**
	 * Initialization hooks
	 * 
	 * @return void
	 */
	public function init_hooks() {
		// индивидуальные опции доставки товара
		// add_action('add_meta_boxes', array($this, 'xfavi_add_custom_box'));
		add_action( 'save_post', [ $this, 'save_post_product' ], 50, 3 );
		add_action( 'woocommerce_save_product_variation', [ $this, 'save_variation_product' ], 10, 2 );
		// пришлось юзать save_post вместо save_post_product ибо wc блочит обновы

		// https://wpruse.ru/woocommerce/custom-fields-in-products/
		// https://wpruse.ru/woocommerce/custom-fields-in-variations/
		add_filter( 'woocommerce_product_data_tabs', [ $this, 'xfavi_added_wc_tabs' ], 10, 1 );
		add_action( 'admin_footer', [ $this, 'xfavi_art_added_tabs_icon' ], 10, 1 );
		add_action( 'woocommerce_product_data_panels', [ $this, 'xfavi_art_added_tabs_panel' ], 10, 1 );
		add_action( 'woocommerce_process_product_meta', [ $this, 'xfavi_art_woo_custom_fields_save' ], 10, 1 );
		add_action( 'woocommerce_product_after_variable_attributes', [ $this, 'add_variable_custom_field' ], 10, 3 );

		/* Мета-поля для категорий товаров */
		add_action( "product_cat_edit_form_fields", [ $this, 'add_meta_product_cat' ], 10, 1 );
		add_action( 'edited_product_cat', [ $this, 'save_meta_product_cat' ], 10, 1 );
		add_action( 'create_product_cat', [ $this, 'save_meta_product_cat' ], 10, 1 );
	}

	/**
	 * Initialization classes
	 * 
	 * @return void
	 */
	public function init_classes() {
		return;
	}

	/**
	 * Сохраняем данные блока, когда пост сохраняется. Function for `save_post` action-hook
	 * 
	 * @param int $post_id
	 * @param WP_Post $post Post object
	 * @param bool $update (`true` — это обновление записи; `false` — это добавление новой записи)
	 * 
	 * @return void
	 */
	public function save_post_product( $post_id, $post, $update ) {
		new XFAVI_Error_Log( 'Стартовала функция save_post_product; Файл: xml-for-avito.php; Строка: ' . __LINE__ );

		if ( $post->post_type !== 'product' ) {
			return; // если это не товар вукомерц
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return; // если это ревизия
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return; // если это автосохранение ничего не делаем
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return; // проверяем права юзера
		}

		new XFAVI_Error_Log( 'Работает функция save_post_product; Файл: xml-for-avito.php; Строка: ' . __LINE__ );

		$post_meta_arr = [ 
			'_xfavi_avito_id',
			'_xfavi_condition',
			'_xfavi_adType',
			'_xfavi_goods_type',
			'_xfavi_goods_subtype',
			'_xfavi_apparel',
			'_xfavi_appareltype',
			'_xfavi_product_sub_type',
			'_xfavi_forwhom',
			'_xfavi_mechanism',
			'_xfavi_material',
			'_xfavi_color',
			'_xfavi_oem',
			'_xfavi_oemoil',
			'_xfavi_spare_part_type',
			'_xfavi_transmission_spare_part_type',
			'_xfavi_technic_spare_part_type',
			'_xfavi_engine_spare_part_type',
			'_xfavi_body_spare_part_type',
			'_xfavi_voltage',
			'_xfavi_capacity',
			'_xfavi_dcl',
			'_xfavi_polarity',
			'_xfavi_volume',
			'_xfavi_product_name',
			'_xfavi_accessory_type',
			'_xfavi_acea',
			'_xfavi_aft',
			'_xfavi_api',
			'_xfavi_astm',
			'_xfavi_dot',
			'_xfavi_sae',
			'_xfavi_tirestype',
			'_xfavi_custom_type_tag_name_1',
			'_xfavi_custom_type_tag_name_2',
			'_xfavi_custom_type_tag_name_3',
			'_xfavi_custom_type_tag_value_1',
			'_xfavi_custom_type_tag_value_2',
			'_xfavi_custom_type_tag_value_3',
			'_xfavi_cabinet_type'
		];
		$this->save_post_meta( $post_meta_arr, $post_id );
		$this->run_feeds_upd( $post_id );
		return;
	}

	/**
	 * Проверяет, нужно ли запускать обновление фида при обновлении товара и при необходимости запускает процесс
	 * 
	 * @param int $post_id
	 * 
	 * @return void
	 */
	public function run_feeds_upd( $post_id ) {
		// нужно ли запускать обновление фида при перезаписи файла
		$xfavi_settings_arr = univ_option_get( 'xfavi_settings_arr' );
		$xfavi_settings_arr_keys_arr = array_keys( $xfavi_settings_arr );
		for ( $i = 0; $i < count( $xfavi_settings_arr_keys_arr ); $i++ ) {
			// !! т.к у нас работа с array_keys, то в $feed_id может быть int, а не string значит преобразуем в string
			$feed_id = (string) $xfavi_settings_arr_keys_arr[ $i ];

			$xfavi_run_cron = common_option_get( 'xfavi_run_cron', false, $feed_id, 'xfavi' );
			if ( $xfavi_run_cron == 'disabled' ) {
				new XFAVI_Error_Log(
					sprintf( 'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
						$feed_id,
						'Фид отключён. Создание кэш-файла для данного фида не требуется',
						'class-xfavi-interface-hocked.php',
						__LINE__
					)
				);
				continue;
			}

			new XFAVI_Error_Log(
				sprintf( 'FEED № %1$s; Шаг $i = %2$s цикла по формированию кэша файлов; Файл: %3$s; Строка: %4$s',
					$feed_id,
					$i,
					'class-xfavi-interface-hocked.php',
					__LINE__
				)
			);

			$result_get_unit_obj = new XFAVI_Get_Unit( $post_id, $feed_id ); // формируем фид товара
			$result_xml = $result_get_unit_obj->get_result();
			$stock_xml = $result_get_unit_obj->get_stock_xml();
			$ids_in_xml = $result_get_unit_obj->get_ids_in_xml();

			new XFAVI_Write_File( $result_xml, [ 'file_name' => $post_id ], $feed_id );
			new XFAVI_Write_File( $stock_xml, [ 'file_name' => $post_id . '-stock' ], $feed_id );
			new XFAVI_Write_File( $ids_in_xml, [ 'file_name' => $post_id . '-in' ], $feed_id );

			$xfavi_ufup = common_option_get( 'xfavi_ufup', false, $feed_id, 'xfavi' );
			if ( $xfavi_ufup !== 'on' ) {
				continue;
			}
			$status_sborki = (int) xfavi_optionGET( 'xfavi_status_sborki', $feed_id );
			if ( $status_sborki > -1 ) {
				continue; // если идет сборка фида - пропуск
			}

			$xfavi_date_save_set = common_option_get( 'xfavi_date_save_set', false, $feed_id, 'xfavi' );
			$feed_file_meta = new XFAVI_Feed_File_Meta( $feed_id );
			// https://site.ru/wp-content/uploads/feed-avito-2.xml
			$filenamefeed = sprintf( '%1$s/%2$s.%3$s',
				XFAVI_SITE_UPLOADS_DIR_PATH,
				$feed_file_meta->get_feed_filename(),
				$feed_file_meta->get_feed_extension()
			);

			if ( ! file_exists( $filenamefeed ) ) { // файла с фидом нет
				new XFAVI_Error_Log(
					sprintf( 'FEED № %1$s; WARNING: %2$s filenamefeed = %3$s; Файл: %4$s; Строка: %5$s',
						$feed_id,
						'Пропускаем быструю сборку тк не существует файла',
						$filenamefeed,
						'class-xfavi-interface-hocked.php',
						__LINE__
					)
				);
				continue;
			}

			clearstatcache(); // очищаем кэш дат файлов
			$last_upd_file = filemtime( $filenamefeed );
			new XFAVI_Error_Log(
				sprintf( 'FEED № %1$s; xfavi_date_save_set = %2$s, filenamefeed = %3$s. %4$s; Файл: %5$s; Строка: %6$s',
					$feed_id,
					$xfavi_date_save_set,
					$filenamefeed,
					'Начинаем сравнивать даты',
					'class-xfavi-interface-hocked.php',
					__LINE__
				)
			);
			if ( $xfavi_date_save_set > $last_upd_file ) {
				// настройки фида сохранялись позже, чем создан фид		
				// нужно полностью пересобрать фид
				new XFAVI_Error_Log(
					sprintf( 'FEED № %1$s; NOTICE: %2$s; Файл: %3$s; Строка: %4$s',
						$feed_id,
						'Настройки фида сохранялись позже, чем создан фид',
						'class-xfavi-interface-hocked.php',
						__LINE__
					)
				);
				$xfavi_run_cron = common_option_get( 'xfavi_status_cron', false, $feed_id, 'xfavi' );

				if ( $xfavi_run_cron === 'disabled' || $xfavi_run_cron === 'once' ) {
					// фид отключён или разово собирается
				} else {
					// ! для правильности работы важен тип string
					wp_clear_scheduled_hook( 'xfavi_cron_period', [ $feed_id ] );
					if ( ! wp_next_scheduled( 'xfavi_cron_period', [ $feed_id ] ) ) {
						wp_schedule_event( time() + 3, $xfavi_run_cron, 'xfavi_cron_period', [ $feed_id ] ); // старт через 3 сек
					}
					new XFAVI_Error_Log(
						sprintf( 'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
							$feed_id,
							'Для полной пересборки после быстрого сохранения xfavi_cron_period внесен в список заданий',
							'class-xfavi-interface-hocked.php',
							__LINE__
						)
					);
				}
			} else { // нужно лишь обновить цены				
				new XFAVI_Error_Log(
					sprintf( 'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
						$feed_id,
						'Настройки фида сохранялись раньше, чем создан фид. Нужно лишь обновить цены',
						'class-xfavi-interface-hocked.php',
						__LINE__
					)
				);
				$generation = new XFAVI_Generation_XML( $feed_id );
				$generation->clear_file_ids_in_xml( $feed_id );
				$generation->onlygluing();
			}
		}
		return;
	}

	/**
	 * Сохраняем данные блока, когда пост сохраняется. Function for `woocommerce_save_product_variation` action-hook
	 * 
	 * @param int $post_id
	 * 
	 * @return void
	 */
	public function save_variation_product( $post_id ) {
		if ( wp_is_post_revision( $post_id ) ) {
			return; // если это ревизия
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return; // если это автосохранение ничего не делаем
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return; // проверяем права юзера
		}

		// обращаем внимание на двойное подчёркивание в $woocommerce__xfavi_avito_id
		$woocommerce__xfavi_avito_id = $_POST['_xfavi_avito_id'][ $post_id ];
		if ( isset( $woocommerce__xfavi_avito_id ) ) {
			update_post_meta( $post_id, '_xfavi_avito_id', esc_attr( $woocommerce__xfavi_avito_id ) );
		}
	}

	/**
	 * Save post_meta
	 * 
	 * @param array $post_meta_arr
	 * @param int $post_id
	 * 
	 * @return void
	 */
	private function save_post_meta( $post_meta_arr, $post_id ) {
		for ( $i = 0; $i < count( $post_meta_arr ); $i++ ) {
			$meta_name = $post_meta_arr[ $i ];
			if ( isset( $_POST[ $meta_name ] ) ) {
				if ( empty( $_POST[ $meta_name ] ) ) {
					delete_post_meta( $post_id, $meta_name );
				} else {
					update_post_meta( $post_id, $meta_name, sanitize_text_field( $_POST[ $meta_name ] ) );
				}
			}
		}
	}

	/**
	 * Function for `woocommerce_product_data_tabs` filter-hook.
	 * 
	 * @param array $tabs
	 *
	 * @return array
	 */
	public static function xfavi_added_wc_tabs( $tabs ) {
		$tabs['xfavi_special_panel'] = [ 
			'label' => __( 'Avito', 'xml-for-avito' ), // название вкладки
			'target' => 'xfavi_added_wc_tabs', // идентификатор вкладки
			'class' => [ 'hide_if_grouped' ], // классы управления видимостью вкладки в зависимости от типа товара
			'priority' => 70 // приоритет вывода
		];
		return $tabs;
	}

	/**
	 * Function for `admin_footer` action-hook.
	 * 
	 * @see https://rawgit.com/woothemes/woocommerce-icons/master/demo.html
	 * 
	 * @param string $data The data to print.
	 *
	 * @return void
	 */
	public static function xfavi_art_added_tabs_icon( $data ) {
		print ( '<style>#woocommerce-coupon-data ul.wc-tabs li.xfavi_special_panel_options a::before,
			#woocommerce-product-data ul.wc-tabs li.xfavi_special_panel_options a::before,
			.woocommerce ul.wc-tabs li.xfavi_special_panel_options a::before {
				font-family: WooCommerce; content: "\e014";
			}</style>' );
	}

	/**
	 * Function for `woocommerce_product_data_panels` action-hook
	 * 
	 * @return void
	 */
	public static function xfavi_art_added_tabs_panel() {
		global $post; ?>
		<div id="xfavi_added_wc_tabs" class="panel woocommerce_options_panel">
			<?php do_action( 'xfavi_before_options_group', $post ); ?>
			<div class="options_group">
				<?php
				printf( '<h2><strong>%s</strong></h2>',
					__( 'Индивидуальные настройки товара для XML фида для Avito', 'xml-for-avito' )
				);

				do_action( 'xfavi_prepend_options_group', $post );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_avito_id',
					'label' => 'AvitoId',
					'description' => sprintf( '%s <strong>AvitoId</strong>. %s. %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'Заполняйте только если добавляете в фид уже размещённое объявление', 'xml-for-avito' ),
						__(
							'Причины могут быть разные — например, вы размещали его не через автозагрузку или у него изменился Id',
							'xml-for-avito'
						)
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_product_name',
					'label' => __( 'Имя товара для Авито', 'xml-for-avito' ),
					'description' => __(
						'При помощи этого поля можно изменить название товара в фиде',
						'xml-for-avito'
					),
					'type' => 'text',
					'custom_attributes' => [ 'maxlength' => 50 ],
					'desc_tip' => true
				] );

				woocommerce_wp_select( [ 
					'id' => '_xfavi_color',
					'label' => sprintf( '%s <i>[%s]</i>', __( 'Цвет', 'xml-for-avito' ), 'Color' ),
					'description' => sprintf( '%s <strong>%s</strong>',
						__( 'Элемент фида', 'xml-for-avito' ),
						'Color'
					),
					'options' => [ 
						'disabled' => __( 'Отключено', 'xml-for-avito' ),
						'Бесцветный' => __( 'Бесцветный', 'xml-for-avito' ),
						'Серый' => __( 'Серый', 'xml-for-avito' ),
						'Синий' => __( 'Синий', 'xml-for-avito' ),
						'Бежевый' => __( 'Бежевый', 'xml-for-avito' ),
						'Чёрный' => __( 'Чёрный', 'xml-for-avito' ),
						'Коричневый' => __( 'Коричневый', 'xml-for-avito' ),
						'Белый' => __( 'Белый', 'xml-for-avito' ),
						'Зелёный' => __( 'Зелёный', 'xml-for-avito' ),
						'Красный' => __( 'Красный', 'xml-for-avito' ),
						'Розовый' => __( 'Розовый', 'xml-for-avito' ),
						'Разноцветный' => __( 'Разноцветный', 'xml-for-avito' ),
						'Фиолетовый' => __( 'Фиолетовый', 'xml-for-avito' ),
						'Голубой' => __( 'Голубой', 'xml-for-avito' ),
						'Оранжевый' => __( 'Оранжевый', 'xml-for-avito' ),
						'Жёлтый' => __( 'Жёлтый', 'xml-for-avito' ),
						'Серебряный' => __( 'Серебряный', 'xml-for-avito' ),
						'Золотой' => __( 'Золотой', 'xml-for-avito' ),
						'Бордовый' => __( 'Бордовый', 'xml-for-avito' )
					],
					'desc_tip' => true
				] );

				woocommerce_wp_select( [ 
					'id' => '_xfavi_condition',
					'label' => sprintf( '%s <i>[%s]</i>', __( 'Состояние товара', 'xml-for-avito' ), 'Condition' ),
					'description' => sprintf( '%s <strong>%s</strong>',
						__( 'Обязательный элемент фида', 'xml-for-avito' ),
						'Condition'
					),
					'options' => [ 
						'new' => __( 'Новый', 'xml-for-avito' ),
						'bu' => __( 'Б/у', 'xml-for-avito' )
					],
					'desc_tip' => true
				] );

				printf( '<h2><strong>%s</strong></h2><hr class="xfavi_tr" />',
					__( 'Индивидуальные настройки товара для XML фида для Avito', 'xml-for-avito' )
				);

				woocommerce_wp_select( [ 
					'id' => '_xfavi_adType',
					'label' => __( 'AdType', 'xml-for-avito' ),
					'description' => __( 'Обязателен для форматов "Для дома и дачи" и "Личные вещи"', 'xml-for-avito' ),
					'options' => [ 
						'default' => __( 'По умолчанию', 'xml-for-avito' ),
						'disabled' => __( 'Отключено', 'xml-for-avito' ),
						'Товар приобретен на продажу' => __( 'Товар приобретен на продажу', 'xml-for-avito' ),
						'Товар от производителя' => __( 'Товар от производителя', 'xml-for-avito' ),
						'Продаю своё' => __( 'Продаю своё', 'xml-for-avito' ),
					],
					'desc_tip' => true
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_goods_type',
					'label' => __( 'Тип товара', 'xml-for-avito' ),
					'description' => sprintf( '%s <strong>GoodsType</strong> / <strong>Breed</strong> /
					<strong>VehicleType</strong>. <a href="//autoload.avito.ru/format/"
					target="_blank">%s</a>',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'Подробнее', 'xml-for-avito' )
					),
					'type' => 'text'
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_goods_subtype',
					'label' => __( 'Тип товара', 'xml-for-avito' ),
					'description' => sprintf( '%s <strong>GoodsSubType</strong> /
					<strong>ProductsType</strong>. <a href="//autoload.avito.ru/format/"
					target="_blank">%s</a>',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'Подробнее', 'xml-for-avito' )
					),
					'type' => 'text'
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_cabinet_type',
					'label' => __( 'Тип товара CabinetType', 'xml-for-avito' ),
					'description' => sprintf( '%s <strong>CabinetType</strong>. %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'используется в подкатегории "Шкафы"', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_apparel',
					'label' => __( 'Apparel', 'xml-for-avito' ),
					'description' => sprintf( '%s %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'Обязательный элемент для Одежды, обуви, аксессуаров', 'xml-for-avito' )
					),
					'type' => 'text'
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_appareltype',
					'label' => __( 'ApparelType', 'xml-for-avito' ),
					'description' => sprintf( '%s %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'Подтип товара (для верхней одежды)', 'xml-for-avito' )
					),
					'type' => 'text'
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_material',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Материал', 'xml-for-avito' ),
						'Material'
					),
					'description' => sprintf( '%s <strong>Material</strong>. %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'используется в подкатегории "Сумки" и "Шкафы"', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				printf( '<h2><strong>%s</strong></h2><hr class="xfavi_tr" />',
					__( 'Часы', 'xml-for-avito' )
				);

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_product_sub_type',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Тип товара', 'xml-for-avito' ),
						'ProductSubType'
					),
					'description' => sprintf( '%s <strong>ProductSubType</strong>. %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'используется в подкатегории "Часы"', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				woocommerce_wp_select( [ 
					'id' => '_xfavi_forwhom',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Для кого', 'xml-for-avito' ),
						'forwhom'
					),
					'description' => sprintf( '%s <strong>forwhom</strong>',
						__( 'Элемент', 'xml-for-avito' )
					),
					'options' => [ 
						'' => __( 'Отключено', 'xml-for-avito' ),
						'Женские' => __( 'Женские', 'xml-for-avito' ),
						'Мужские' => __( 'Мужские', 'xml-for-avito' ),
						'Унисекс' => __( 'Унисекс', 'xml-for-avito' ),
						'Детские' => __( 'Детские', 'xml-for-avito' )
					],
					'desc_tip' => true
				] );

				woocommerce_wp_select( [ 
					'id' => '_xfavi_mechanism',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Механизм', 'xml-for-avito' ),
						'Mechanism'
					),
					'description' => sprintf( '%s <strong>Mechanism</strong>',
						__( 'Элемент', 'xml-for-avito' )
					),
					'options' => [ 
						'' => __( 'Отключено', 'xml-for-avito' ),
						'Кварцевые' => __( 'Кварцевые', 'xml-for-avito' ),
						'Механические' => __( 'Механические', 'xml-for-avito' ),
						'Электронные' => __( 'Электронные', 'xml-for-avito' ),
						'Другие' => __( 'Другие', 'xml-for-avito' )
					],
					'desc_tip' => true
				] );

				printf( '<h2><strong>%s</strong></h2><hr class="xfavi_tr" />',
					__( 'Запчасти', 'xml-for-avito' )
				);

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_oem',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Номер детали OEM', 'xml-for-avito' ),
						'OEM'
					),
					'description' => sprintf( '%s <strong>OEM</strong>. %s<br/>%s <strong>|</strong> (%s) %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'используется только в подкатегории "Запчасти"', 'xml-for-avito' ),
						__( 'Если вам необходимо ввести несколько значений, то исплользуёте символ', 'xml-for-avito' ),
						__( 'вертикальная черта', 'xml-for-avito' ),
						__( 'в качестве разделителя', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_oemoil',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Допуски OEM', 'xml-for-avito' ),
						'OEMOil'
					),
					'description' => sprintf( '%s <strong>OEMOil</strong>. %s<br/>%s <strong>|</strong> (%s) %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'используется только в подкатегории "Запчасти"', 'xml-for-avito' ),
						__( 'Если вам необходимо ввести несколько значений, то исплользуёте символ', 'xml-for-avito' ),
						__( 'вертикальная черта', 'xml-for-avito' ),
						__( 'в качестве разделителя', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				print ( '<hr class="xfavi_tr" />' );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_accessory_type',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Тип аксессуара', 'xml-for-avito' ),
						'AccessoryType'
					),
					'description' => sprintf( '%s <strong>AccessoryType</strong>. %s<br/>%s %s<strong>|</strong> (%s) %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'используется в подкатегориях', 'xml-for-avito' ),
						'"Уход", "Для колёс", "Набор автомобилиста", "Защита и декор", "Для салона"',
						__( 'Если вам необходимо ввести несколько значений, то исплользуёте символ', 'xml-for-avito' ),
						__( 'вертикальная черта', 'xml-for-avito' ),
						__( 'в качестве разделителя', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				print ( '<hr class="xfavi_tr" />' );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_spare_part_type',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Вид запчасти', 'xml-for-avito' ),
						'SparePartType'
					),
					'description' => sprintf( '%s <strong>SparePartType</strong>. %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'используется в подкатегории "Запчасти"', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_transmission_spare_part_type',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Тип детали трансмиссии', 'xml-for-avito' ),
						'TransmissionSparePartType'
					),
					'description' => sprintf( '%s <strong>TransmissionSparePartType</strong>. %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'используется в подкатегории "Запчасти"', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_technic_spare_part_type',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Тип детали спецтехники', 'xml-for-avito' ),
						'TechnicSparePartType'
					),
					'description' => sprintf( '%s <strong>TechnicSparePartType</strong>. %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'используется в подкатегории "Запчасти"', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_engine_spare_part_type',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Тип детали двигателя', 'xml-for-avito' ),
						'EngineSparePartType'
					),
					'description' => sprintf( '%s <strong>EngineSparePartType</strong>. %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'Применимо, если в поле SparePartType указано значение "Двигатель"', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_body_spare_part_type',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Тип детали кузова', 'xml-for-avito' ),
						'BodySparePartType'
					),
					'description' => sprintf( '%s <strong>BodySparePartType</strong>. %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'Применимо, если в поле SparePartType указано значение "Кузов"', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				printf( '<p><strong>%s</strong></p><hr class="xfavi_tr" />',
					__( 'Аккумуляторы', 'xml-for-avito' )
				);

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_voltage',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Напряжение в вольтах', 'xml-for-avito' ),
						'Voltage'
					),
					'description' => sprintf( '%s <strong>Voltage</strong>. %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'Применимо, если в поле SparePartType указано значение "Аккумуляторы"', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_capacity',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Ёмкость в ампер-часах', 'xml-for-avito' ),
						'Capacity'
					),
					'description' => sprintf( '%s <strong>Capacity</strong>. %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'Применимо, если в поле SparePartType указано значение "Аккумуляторы"', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_dcl',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Пусковой ток в амперах', 'xml-for-avito' ),
						'DCL'
					),
					'description' => sprintf( '%s <strong>DCL</strong>. %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'Применимо, если в поле SparePartType указано значение "Аккумуляторы"', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				woocommerce_wp_select( [ 
					'id' => '_xfavi_polarity',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Полярность', 'xml-for-avito' ),
						'Polarity'
					),
					'description' => sprintf( '%s <strong>Polarity</strong>. %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'Применимо, если в поле SparePartType указано значение "Аккумуляторы"', 'xml-for-avito' )
					),
					'options' => [ 
						'' => __( 'Отключено', 'xml-for-avito' ),
						'Обратная' => __( 'Обратная', 'xml-for-avito' ),
						'Прямая' => __( 'Прямая', 'xml-for-avito' ),
						'Двойная' => __( 'Двойная', 'xml-for-avito' )
					],
					'desc_tip' => true
				] );

				print ( '<hr class="xfavi_tr" />' );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_volume',
					'label' => sprintf( '%s <i>[%s]</i>', __( 'Объём', 'xml-for-avito' ), 'Volume' ),
					'description' => sprintf( '%s <strong>Volume</strong>. %s',
						__( 'Элемент фида', 'xml-for-avito' ),
						__( 'используется в подкатегории "Масла и автохимия"', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_acea',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Стандарт ACEA', 'xml-for-avito' ),
						'ACEA'
					),
					'description' => sprintf( '%s <strong>ACEA</strong>. %s<br/>%s <strong>|</strong> (%s) %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'используется в подкатегории "Моторные масла"', 'xml-for-avito' ),
						__( 'Если вам необходимо ввести несколько значений, то исплользуёте символ', 'xml-for-avito' ),
						__( 'вертикальная черта', 'xml-for-avito' ),
						__( 'в качестве разделителя', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_aft',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Стандарт ATF', 'xml-for-avito' ),
						'ATF'
					),
					'description' => sprintf( '%s <strong>ATF</strong>. %s<br/>%s <strong>|</strong> (%s) %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'используется в подкатегории "Трансмиссионные масла"', 'xml-for-avito' ),
						__( 'Если вам необходимо ввести несколько значений, то исплользуёте символ', 'xml-for-avito' ),
						__( 'вертикальная черта', 'xml-for-avito' ),
						__( 'в качестве разделителя', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_api',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Стандарт API', 'xml-for-avito' ),
						'API'
					),
					'description' => sprintf( '%s <strong>API</strong>. %s<br/>%s <strong>|</strong> (%s) %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'используется в подкатегории "Моторные масла" и "Трансмиссионные масла"', 'xml-for-avito' ),
						__( 'Если вам необходимо ввести несколько значений, то исплользуёте символ', 'xml-for-avito' ),
						__( 'вертикальная черта', 'xml-for-avito' ),
						__( 'в качестве разделителя', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_astm',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Стандарт ASTM', 'xml-for-avito' ),
						'ASTM'
					),
					'description' => sprintf( '%s <strong>ASTM</strong>. %s<br/>%s <strong>|</strong> (%s) %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'используется в подкатегории "Охлаждающие жидкости"', 'xml-for-avito' ),
						__( 'Если вам необходимо ввести несколько значений, то исплользуёте символ', 'xml-for-avito' ),
						__( 'вертикальная черта', 'xml-for-avito' ),
						__( 'в качестве разделителя', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_dot',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Стандарт DOT', 'xml-for-avito' ),
						'DOT'
					),
					'description' => sprintf( '%s <strong>DOT</strong>. %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'используется в подкатегории "Тормозные жидкости"', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_sae',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Класс вязкости SAE', 'xml-for-avito' ),
						'SAE'
					),
					'description' => sprintf( '%s <strong>SAE</strong>. %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'используется в подкатегории "Моторные масла" и "Трансмиссионные масла"', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				print ( '<hr class="xfavi_tr" />' );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_tirestype',
					'label' => sprintf( '%s <i>[%s]</i>',
						__( 'Вид шиномонтажного оборудования', 'xml-for-avito' ),
						'TiresType'
					),
					'description' => sprintf( '%s <strong>TiresType</strong>. %s',
						__( 'Элемент', 'xml-for-avito' ),
						__( 'используется в подкатегории "Шиномонтаж"', 'xml-for-avito' )
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				print ( '<hr class="xfavi_tr" />' );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_custom_type_tag_name_1',
					'label' => sprintf( '%s',
						__( 'Название произвольного тега', 'xml-for-avito' )
					),
					'description' => sprintf( '%s!',
						__(
							'Используя эту настройку вы можете прописать товару совершенно любой тег',
							'xml-for-avito'
						)
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_custom_type_tag_value_1',
					'label' => sprintf( '%s',
						__( 'Значение произвольного тега', 'xml-for-avito' )
					),
					'description' => sprintf( '%s!',
						__(
							'Используя эту настройку вы можете прописать товару совершенно любой тег',
							'xml-for-avito'
						)
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				print ( '<hr class="xfavi_tr" />' );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_custom_type_tag_name_2',
					'label' => sprintf( '%s',
						__( 'Название произвольного тега', 'xml-for-avito' )
					),
					'description' => sprintf( '%s!',
						__(
							'Используя эту настройку вы можете прописать товару совершенно любой тег',
							'xml-for-avito'
						)
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_custom_type_tag_value_2',
					'label' => sprintf( '%s',
						__( 'Значение произвольного тега', 'xml-for-avito' )
					),
					'description' => sprintf( '%s!',
						__(
							'Используя эту настройку вы можете прописать товару совершенно любой тег',
							'xml-for-avito'
						)
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				print ( '<hr class="xfavi_tr" />' );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_custom_type_tag_name_3',
					'label' => sprintf( '%s',
						__( 'Название произвольного тега', 'xml-for-avito' )
					),
					'description' => sprintf( '%s!',
						__(
							'Используя эту настройку вы можете прописать товару совершенно любой тег',
							'xml-for-avito'
						)
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_xfavi_custom_type_tag_value_3',
					'label' => sprintf( '%s',
						__( 'Значение произвольного тега', 'xml-for-avito' )
					),
					'description' => sprintf( '%s!',
						__(
							'Используя эту настройку вы можете прописать товару совершенно любой тег',
							'xml-for-avito'
						)
					),
					'type' => 'text',
					'desc_tip' => true
				] );

				print ( '<hr class="xfavi_tr" />' );

				do_action( 'xfavi_append_options_group', $post ); ?>
			</div>
			<?php do_action( 'xfavi_after_options_group', $post ); ?>
		</div>
		<?php
	}


	/**
	 * Function for `woocommerce_process_product_meta` action-hook.
	 * 
	 * @param $post_id 
	 *
	 * @return void
	 */
	public static function xfavi_art_woo_custom_fields_save( $post_id ) {
		// Сохранение текстового поля
		if ( isset( $_POST['_xfavi_condition'] ) ) {
			update_post_meta( $post_id, '_xfavi_condition', sanitize_text_field( $_POST['_xfavi_condition'] ) );
		}
		if ( isset( $_POST['_xfavi_custom'] ) ) {
			update_post_meta( $post_id, '_xfavi_custom', sanitize_text_field( $_POST['_xfavi_custom'] ) );
		}
		do_action( 'xfavi_process_product_meta_save', $post_id );
	}

	/**
	 * Function for `woocommerce_product_after_variable_attributes` action-hook
	 * 
	 * @param $loop
	 * @param $variation_data
	 * @param $variation
	 * 
	 * @return void
	 */
	public function add_variable_custom_field( $loop, $variation_data, $variation ) {
		woocommerce_wp_text_input( [ 
			'id' => '_xfavi_avito_id[' . $variation->ID . ']',
			'label' => 'AvitoId для вариации',
			'description' => sprintf( '%s <strong>AvitoId</strong>. %s. %s',
				__( 'Элемент', 'xml-for-avito' ),
				__( 'Заполняйте только если добавляете в фид уже размещённое объявление', 'xml-for-avito' ),
				__(
					'Причины могут быть разные — например, вы размещали его не через автозагрузку или у него изменился Id',
					'xml-for-avito'
				)
			),
			'desc_tip' => 'true', // Всплывающая подсказка
			'placeholder' => '',
			// 'custom_attributes' => [ 'required' => 'required' ],
			'value' => get_post_meta( $variation->ID, '_xfavi_avito_id', true )
		] );
	}

	/**
	 * Позволяет добавить дополнительные поля на страницу редактирования элементов таксономии (термина).
	 * Function for `(taxonomy)_edit_form_fields` action-hook.
	 * 
	 * @param WP_Term $tag      Current taxonomy term object.
	 * @param string  $taxonomy Current taxonomy slug.
	 *
	 * @return void
	 */
	public static function add_meta_product_cat( $term ) { ?>
		<tr class="form-field term-parent-wrap">
			<th scope="row" valign="top"><label>
					<?php esc_html_e( 'Обрабатывать согласно правилам Авито', 'xml-for-avito' ); ?>
				</label></th>
			<td>
				<select name="xfavi_cat_meta[xfavi_avito_standart]" id="xfavi_avito_standart">
					<?php $xfavi_avito_standart = esc_attr( get_term_meta( $term->term_id, 'xfavi_avito_standart', 1 ) ); ?>
					<option value="" <?php selected( $xfavi_avito_standart, '' ); ?> disabled="disabled">
						<?php esc_html_e( 'Не задано. Задайте', 'xml-for-avito' ); ?>!
					</option>
					<option value="dom" <?php selected( $xfavi_avito_standart, 'dom' ); ?>>
						<?php esc_html_e( 'Для дома и дачи', 'xml-for-avito' ); ?>
					</option>
					<option value="tehnika" <?php selected( $xfavi_avito_standart, 'tehnika' ); ?>>
						<?php esc_html_e( 'Электроника', 'xml-for-avito' ); ?>
					</option>
					<option value="business" <?php selected( $xfavi_avito_standart, 'business' ); ?>>
						<?php esc_html_e( 'Для бизнеса', 'xml-for-avito' ); ?>
					</option>
					<option value="lichnye_veshi" <?php selected( $xfavi_avito_standart, 'lichnye_veshi' ); ?>>
						<?php esc_html_e( 'Личные вещи', 'xml-for-avito' ); ?>
					</option>
					<option value="zhivotnye" <?php selected( $xfavi_avito_standart, 'zhivotnye' ); ?>>
						<?php esc_html_e( 'Животные', 'xml-for-avito' ); ?>
					</option>
					<option value="zapchasti" <?php selected( $xfavi_avito_standart, 'zapchasti' ); ?>>
						<?php esc_html_e( 'Запчасти и аксессуары', 'xml-for-avito' ); ?> (
						<?php esc_html_e( 'кроме', 'xml-for-avito' ); ?> "
						<?php esc_html_e( 'Шины, диски и колёса', 'xml-for-avito' ); ?>")
					</option>
					<option value="hobby" <?php selected( $xfavi_avito_standart, 'hobby' ); ?>>
						<?php esc_html_e( 'Хобби и отдых', 'xml-for-avito' ); ?>
					</option>
				</select><br /><label>AdType:</label><br />
				<select name="xfavi_cat_meta[xfavi_adType]" id="xfavi_adType">
					<?php $xfavi_adType = esc_attr( get_term_meta( $term->term_id, 'xfavi_adType', 1 ) ); ?>
					<option data-chained="zapchasti" value="Товар приобретен на продажу" <?php selected( $xfavi_adType, 'Товар приобретен на продажу' ); ?>>
						<?php esc_html_e( 'Товар приобретен на продажу', 'xml-for-avito' ); ?>
					</option>
					<option data-chained="zapchasti" value="Товар от производителя" <?php selected( $xfavi_adType, 'Товар от производителя' ); ?>>
						<?php esc_html_e( 'Товар от производителя', 'xml-for-avito' ); ?>
					</option>
					<option data-chained="hobby" value="Товар приобретен на продажу" <?php selected( $xfavi_adType, 'Товар приобретен на продажу' ); ?>>
						<?php esc_html_e( 'Товар приобретен на продажу', 'xml-for-avito' ); ?>
					</option>
					<option data-chained="hobby" value="Товар от производителя" <?php selected( $xfavi_adType, 'Товар от производителя' ); ?>>
						<?php esc_html_e( 'Товар от производителя', 'xml-for-avito' ); ?>
					</option>
					<!-- option data-chained="tehnika" value="disabled" <?php selected( $xfavi_adType, 'disabled' ); ?>><?php esc_html_e( 'Отключено', 'xml-for-avito' ); ?></option -->
					<option data-chained="tehnika" value="Товар приобретен на продажу" <?php selected( $xfavi_adType, 'Товар приобретен на продажу' ); ?>>
						<?php esc_html_e( 'Товар приобретен на продажу', 'xml-for-avito' ); ?>
					</option>
					<option data-chained="tehnika" value="Продаю своё" <?php selected( $xfavi_adType, 'Продаю своё' ); ?>>
						<?php esc_html_e( 'Продаю своё', 'xml-for-avito' ); ?>
					</option>
					<option data-chained="zhivotnye" value="disabled" <?php selected( $xfavi_adType, 'disabled' ); ?>>
						<?php esc_html_e( 'Отключено', 'xml-for-avito' ); ?>
					</option>
					<option data-chained="business" value="disabled" <?php selected( $xfavi_adType, 'disabled' ); ?>>
						<?php esc_html_e( 'Отключено', 'xml-for-avito' ); ?>
					</option>
					<option data-chained="lichnye_veshi" value="Товар приобретен на продажу" <?php selected( $xfavi_adType, 'Товар приобретен на продажу' ); ?>>
						<?php esc_html_e( 'Товар приобретен на продажу', 'xml-for-avito' ); ?>
					</option>
					<option data-chained="lichnye_veshi" value="Товар от производителя" <?php selected( $xfavi_adType, 'Товар от производителя' ); ?>>
						<?php esc_html_e( 'Товар от производителя', 'xml-for-avito' ); ?>
					</option>
					<option data-chained="dom" value="Товар приобретен на продажу" <?php selected( $xfavi_adType, 'Товар приобретен на продажу' ); ?>>
						<?php esc_html_e( 'Товар приобретен на продажу', 'xml-for-avito' ); ?>
					</option>
					<option data-chained="dom" value="Товар от производителя" <?php selected( $xfavi_adType, 'Товар от производителя' ); ?>>
						<?php esc_html_e( 'Товар от производителя', 'xml-for-avito' ); ?>
					</option>
				</select><br />
				<p class="description">
					<?php esc_html_e( 'Укажите по каким правилам будут обрабатываться товары из данной категории', 'xml-for-avito' ); ?>.
					<a href="//autoload.avito.ru/format/" target="_blank">
						<?php esc_html_e( 'Подробнее', 'xml-for-avito' ); ?>
					</a>
				</p>
			</td>
		</tr>
		<?php $result_arr = xfavi_option_construct( $term ); ?>
		<tr class="form-field term-parent-wrap">
			<th scope="row" valign="top"><label>
					<?php esc_html_e( 'Авито', 'xml-for-avito' ); ?> Category
				</label></th>
			<td>
				<select name="xfavi_cat_meta[xfavi_avito_product_category]" id="xfavi_avito_product_category">
					<?php $xfavi_goods_type = esc_attr( get_term_meta( $term->term_id, '_xfavi_goods_type', 1 ) ); ?>
					<option value="" <?php selected( $xfavi_goods_type, '' ); ?> disabled="disabled">
						<?php esc_html_e( 'Не задано. Задайте', 'xml-for-avito' ); ?>!
					</option>
					<?php echo $result_arr[0]; ?>
				</select><br />
				<p class="description">
					<?php esc_html_e( 'Обязательный элемент', 'xml-for-avito' ); ?> <strong>Category</strong>. <a
						href="//autoload.avito.ru/format/" target="_blank">
						<?php esc_html_e( 'Подробнее', 'xml-for-avito' ); ?>
					</a><br /><strong>
						<?php esc_html_e( 'Внимание', 'xml-for-avito' ); ?>!
					</strong>
					<?php esc_html_e( 'Если выпадающий список пуст, то сначала измените значение опции', 'xml-for-avito' ); ?> "
					<?php esc_html_e( 'Обрабатывать согласно правилам Авито', 'xml-for-avito' ); ?>".
					<?php esc_html_e( 'Укажите какой категори на Авито соответствует данная категория', 'xml-for-avito' ); ?>.
				</p>
			</td>
		</tr>
		<tr class="form-field term-parent-wrap">
			<th scope="row" valign="top"><label>
					<?php esc_html_e( 'Авито', 'xml-for-avito' ); ?>:<br />- GoodsType<br />- Breed*<br />- VehicleType**
				</label></th>
			<td>
				<select name="xfavi_cat_meta[xfavi_default_goods_type]" id="xfavi_default_goods_type">
					<option value="disabled">
						<?php esc_html_e( 'Отключено', 'xml-for-avito' ); ?>
					</option>
					<?php echo $result_arr[1]; ?>
				</select><br />
				<p class="description">
					<?php esc_html_e( 'Элемент', 'xml-for-avito' ); ?> <strong>GoodsType</strong> / <strong>Breed</strong> /
					<strong>VehicleType</strong>. <a href="//autoload.avito.ru/format/" target="_blank">
						<?php esc_html_e( 'Подробнее', 'xml-for-avito' ); ?>
					</a>
				</p>
			</td>
		</tr>
		<tr class="form-field term-parent-wrap">
			<th scope="row" valign="top"><label>
					<?php esc_html_e( 'Авито', 'xml-for-avito' ); ?> GoodsSubType / ProductsType
				</label></th>
			<td>
				<select name="xfavi_cat_meta[xfavi_default_goods_subtype]" id="xfavi_default_goods_subtype">
					<option value="disabled">
						<?php esc_html_e( 'Отключено', 'xml-for-avito' ); ?>
					</option>
					<option value="Для_автомобилей">
						<?php esc_html_e( 'Для автомобилей', 'xml-for-avito' ); ?>
					</option>
					<?php echo $result_arr[2]; ?>
				</select><br />
				<p class="description">
					<?php esc_html_e( 'Элемент', 'xml-for-avito' ); ?> <strong>GoodsSubType</strong> /
					<strong>ProductsType</strong>. <a href="//autoload.avito.ru/format/" target="_blank">
						<?php esc_html_e( 'Подробнее', 'xml-for-avito' ); ?>
					</a>
				</p>
			</td>
		</tr>
		<tr class="form-field term-parent-wrap">
			<th scope="row" valign="top"><label>
					<?php esc_html_e( 'Авито', 'xml-for-avito' ); ?> Type <br />
					(<?php esc_html_e( 'указан в скобках', 'xml-for-avito' ); ?>)
				</label></th>
			<td>
				<select name="xfavi_cat_meta[xfavi_default_another_type]" id="xfavi_default_another_type">
					<option value="disabled">
						<?php esc_html_e( 'Отключено', 'xml-for-avito' ); ?>
					</option>
					<?php echo $result_arr[3]; ?>
				</select>
			</td>
		</tr>
		<tr class="form-field term-parent-wrap">
			<th scope="row" valign="top"><label>
					<?php esc_html_e( 'Авито', 'xml-for-avito' ); ?> SparePartType
				</label></th>
			<td>
				<select name="xfavi_cat_meta[xfavi_spare_part_type]" id="xfavi_spare_part_type">
					<option value="disabled">
						<?php esc_html_e( 'Отключено', 'xml-for-avito' ); ?>
					</option>
					<?php
					$xfavi_spare_part_type = esc_attr( get_term_meta( $term->term_id, 'xfavi_spare_part_type', 1 ) );
					$xml_url = XFAVI_PLUGIN_DIR_PATH . 'data/avito-tags.xml';
					$xml_string = file_get_contents( $xml_url );
					$xml_object = new SimpleXMLElement( $xml_string );
					$select_val_arr = [];
					foreach ( $xml_object->children() as $avito_tag ) {
						if ( $avito_tag->getName() == (string) 'SparePartType' ) {
							$select_val_arr[] = (string) $avito_tag[0];
						}
					}

					for ( $i = 0; $i < count( $select_val_arr ); $i++ ) {
						printf( '<option value="%s" %s>%s</option>',
							$select_val_arr[ $i ],
							selected( $xfavi_spare_part_type, $select_val_arr[ $i ], false ),
							$select_val_arr[ $i ]
						);
					}
					?>
				</select><br />
				<p class="description">
					<?php esc_html_e( 'Элемент', 'xml-for-avito' ); ?> <strong>SparePartType</strong>. <a
						href="//www.avito.ru/autoload/documentation/templates/67029" target="_blank">
						<?php esc_html_e( 'Подробнее', 'xml-for-avito' ); ?>
					</a>
				</p>
			</td>
		</tr>
		<tr class="form-field term-parent-wrap">
			<th scope="row" valign="top"><label>
					<?php esc_html_e( 'Авито', 'xml-for-avito' ); ?> EngineSparePartType
				</label></th>
			<td>
				<select name="xfavi_cat_meta[xfavi_engine_spare_part_type]" id="xfavi_engine_spare_part_type">
					<option value="disabled">
						<?php esc_html_e( 'Отключено', 'xml-for-avito' ); ?>
					</option>
					<?php
					$xfavi_engine_spare_part_type = esc_attr( get_term_meta( $term->term_id, 'xfavi_engine_spare_part_type', 1 ) );
					$select_val_arr = [];
					foreach ( $xml_object->children() as $avito_tag ) {
						if ( $avito_tag->getName() == (string) 'EngineSparePartType' ) {
							$select_val_arr[] = (string) $avito_tag[0];
						}
					}

					for ( $i = 0; $i < count( $select_val_arr ); $i++ ) {
						printf( '<option value="%s" %s>%s</option>',
							$select_val_arr[ $i ],
							selected( $xfavi_engine_spare_part_type, $select_val_arr[ $i ], false ),
							$select_val_arr[ $i ]
						);
					}
					?>
				</select><br />
				<p class="description">
					<?php esc_html_e( 'Элемент', 'xml-for-avito' ); ?> <strong>EngineSparePartType</strong>. <a
						href="//www.avito.ru/autoload/documentation/templates/67029" target="_blank">
						<?php esc_html_e( 'Подробнее', 'xml-for-avito' ); ?>
					</a>
				</p>
			</td>
		</tr>
		<tr class="form-field term-parent-wrap">
			<th scope="row" valign="top"><label>
					<?php esc_html_e( 'Авито', 'xml-for-avito' ); ?> TransmissionSparePartType
				</label></th>
			<td>
				<select name="xfavi_cat_meta[xfavi_transmission_engine_spare_part_type]"
					id="xfavi_transmission_engine_spare_part_type">
					<option value="disabled">
						<?php esc_html_e( 'Отключено', 'xml-for-avito' ); ?>
					</option>
					<?php
					$xfavi_transmission_engine_spare_part_type = esc_attr( get_term_meta( $term->term_id, 'xfavi_transmission_engine_spare_part_type', 1 ) );
					$select_val_arr = [];
					foreach ( $xml_object->children() as $avito_tag ) {
						if ( $avito_tag->getName() == (string) 'TransmissionSparePartType' ) {
							$select_val_arr[] = (string) $avito_tag[0];
						}
					}

					for ( $i = 0; $i < count( $select_val_arr ); $i++ ) {
						printf( '<option value="%s" %s>%s</option>',
							$select_val_arr[ $i ],
							selected( $xfavi_transmission_engine_spare_part_type, $select_val_arr[ $i ], false ),
							$select_val_arr[ $i ]
						);
					}
					?>
				</select><br />
				<p class="description">
					<?php esc_html_e( 'Элемент', 'xml-for-avito' ); ?> <strong>TransmissionSparePartType</strong>. <a
						href="//www.avito.ru/autoload/documentation/templates/67029" target="_blank">
						<?php esc_html_e( 'Подробнее', 'xml-for-avito' ); ?>
					</a>
				</p>
			</td>
		</tr>
		<tr class="form-field term-parent-wrap">
			<th scope="row" valign="top"><label>
					<?php esc_html_e( 'Авито', 'xml-for-avito' ); ?> BodySparePartType
				</label></th>
			<td>
				<select name="xfavi_cat_meta[xfavi_body_spare_part_type]" id="xfavi_body_spare_part_type">
					<option value="disabled">
						<?php esc_html_e( 'Отключено', 'xml-for-avito' ); ?>
					</option>
					<?php
					$xfavi_body_spare_part_type = esc_attr( get_term_meta( $term->term_id, 'xfavi_body_spare_part_type', 1 ) );
					$select_val_arr = [];
					foreach ( $xml_object->children() as $avito_tag ) {
						if ( $avito_tag->getName() == (string) 'BodySparePartType' ) {
							$select_val_arr[] = (string) $avito_tag[0];
						}
					}

					for ( $i = 0; $i < count( $select_val_arr ); $i++ ) {
						printf( '<option value="%s" %s>%s</option>',
							$select_val_arr[ $i ],
							selected( $xfavi_body_spare_part_type, $select_val_arr[ $i ], false ),
							$select_val_arr[ $i ]
						);
					}
					?>
				</select><br />
				<p class="description">
					<?php esc_html_e( 'Элемент', 'xml-for-avito' ); ?> <strong>BodySparePartType</strong>. <a
						href="//www.avito.ru/autoload/documentation/templates/67029" target="_blank">
						<?php esc_html_e( 'Подробнее', 'xml-for-avito' ); ?>
					</a>
				</p>
			</td>
		</tr>
		<tr class="form-field term-parent-wrap">
			<th scope="row" valign="top"><label>
					<?php esc_html_e( 'Авито', 'xml-for-avito' ); ?>
					<?php esc_html_e( 'Apparel', 'xml-for-avito' ); ?>
				</label></th>
			<td>
				<input id="xfavi_default_apparel" type="text" name="xfavi_cat_meta[xfavi_default_apparel]"
					value="<?php echo esc_attr( get_term_meta( $term->term_id, 'xfavi_default_apparel', 1 ) ); ?>" />
				<p class="description">
					<?php esc_html_e( 'Обязательный элемент для Одежды, обуви, аксессуаров', 'xml-for-avito' ); ?>
					<strong>Apparel</strong>. <a href="//autoload.avito.ru/format/" target="_blank">
						<?php esc_html_e( 'Подробнее', 'xml-for-avito' ); ?>
					</a>
				</p>
			</td>
		</tr>
		<tr class="form-field term-parent-wrap">
			<th scope="row" valign="top"><label>
					<?php esc_html_e( 'Авито', 'xml-for-avito' ); ?>
					<?php esc_html_e( 'ApparelType', 'xml-for-avito' ); ?>
				</label></th>
			<td>
				<input id="xfavi_default_appareltype" type="text" name="xfavi_cat_meta[xfavi_default_appareltype]"
					value="<?php echo esc_attr( get_term_meta( $term->term_id, 'xfavi_default_appareltype', 1 ) ); ?>" />
				<p class="description">
					<?php esc_html_e( 'Подтип товара (для верхней одежды)', 'xml-for-avito' ); ?> <strong>ApparelType</strong>.
					<a href="//autoload.avito.ru/format/" target="_blank">
						<?php esc_html_e( 'Подробнее', 'xml-for-avito' ); ?>
					</a>
				</p>
			</td>
		</tr>
		<?php for ( $i = 1; $i < 5; $i++ ) : ?>
			<tr class="form-field term-parent-wrap">
				<th scope="row" valign="top">
					<label>
						<?php esc_html_e( 'Название тега', 'xml-for-avito' ); ?>
						<?php esc_html_e( 'Авито', 'xml-for-avito' ); ?><br />
						<input id="xfavi_custom_type_tag_name<?php echo $i; ?>" type="text"
							name="xfavi_cat_meta[xfavi_custom_type_tag_name<?php echo $i; ?>]"
							value="<?php echo esc_attr( get_term_meta( $term->term_id, 'xfavi_custom_type_tag_name' . $i, 1 ) ) ?>" />
					</label>
				</th>
				<td>
					<?php esc_html_e( 'Значение тега', 'xml-for-avito' ); ?><br />
					<input id="xfavi_custom_type_tag_value<?php echo $i; ?>" type="text"
						name="xfavi_cat_meta[xfavi_custom_type_tag_value<?php echo $i; ?>]"
						value="<?php echo esc_attr( get_term_meta( $term->term_id, 'xfavi_custom_type_tag_value' . $i, 1 ) ) ?>" /><br />
					<p class="description">
						<?php esc_html_e( 'Этот тег и значение будут подставлены ВСЕМ товарам из данной категории', 'xml-for-avito' ); ?>
					</p>
				</td>
			</tr>
		<?php endfor; ?>
		<script type="text/javascript">jQuery(document).ready(function () {
				/* https://github.com/tuupola/jquery_chained or $("#series").chainedTo("#mark"); */
				jQuery("#xfavi_adType").chained("#xfavi_avito_standart");
				jQuery("#xfavi_avito_product_category").chained("#xfavi_avito_standart");
				jQuery("#xfavi_default_goods_type").chained("#xfavi_avito_product_category");
				jQuery("#xfavi_default_goods_subtype").chained("#xfavi_default_goods_type");
				jQuery("#xfavi_default_another_type").chained("#xfavi_default_goods_subtype");
			});</script>
		<?php
	}

	/**
	 * Сохранение данных в БД
	 * 
	 * @param int $term_id
	 * 
	 * @return void
	 */
	function save_meta_product_cat( $term_id ) {
		if ( ! isset( $_POST['xfavi_cat_meta'] ) ) {
			return;
		}
		$xfavi_cat_meta = array_map( 'sanitize_text_field', $_POST['xfavi_cat_meta'] );
		foreach ( $xfavi_cat_meta as $key => $value ) {
			if ( empty( $value ) ) {
				delete_term_meta( $term_id, $key );
				continue;
			}
			update_term_meta( $term_id, $key, $value );
		}
		return;
	}
} // end class XFAVI_Interface_Hoocked