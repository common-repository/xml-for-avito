<?php
/**
 * The class return the Settings page of the plugin XML for Avito
 *
 * @package                 iCopyDoc Plugins (ICPD)
 * @subpackage              XML for Avito
 * @since                   0.1.0
 * 
 * @version                 2.4.12 (03-10-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param                   
 *
 * @depends                 classes:    XFAVI_Data_Arr
 *                                      XFAVI_Error_Log 
 *                                      XFAVI_WP_List_Table
 *                                      XFAVI_Settings_Feed_WP_List_Table
 *                          traits:     
 *                          methods:    
 *                          functions:  common_option_add
 *                                      common_option_get
 *                                      common_option_upd
 *                                      xfavi_optionGET
 *                                      xfavi_optionUPD
 *                                      xfavi_optionDEL
 *                          constants:  XFAVI_PLUGIN_UPLOADS_DIR_PATH
 *                          options:     
 *
 */
defined( 'ABSPATH' ) || exit;

class XFAVI_Settings_Page {
	/**
	 * Allowed HTML tags for use in wp_kses()
	 */
	const ALLOWED_HTML_ARR = [ 
		'a' => [ 
			'href' => true,
			'title' => true,
			'target' => true,
			'class' => true,
			'style' => true
		],
		'br' => [ 'class' => true ],
		'i' => [ 'class' => true ],
		'small' => [ 'class' => true ],
		'strong' => [ 'class' => true, 'style' => true ],
		'p' => [ 'class' => true, 'style' => true ],
		'kbd' => [ 'class' => true ]
	];

	/**
	 * Feed ID
	 * @var string
	 */
	private $feed_id = '1';

	/**
	 * The value of the current tab
	 * @var string
	 */
	private $cur_tab = 'main_tab';

	/**
	 * The class return the Settings page of the plugin XML for Avito
	 */
	public function __construct() {
		if ( isset( $_GET['feed_id'] ) ) {
			$this->feed_id = sanitize_text_field( $_GET['feed_id'] );
		} else {
			if ( empty( xfavi_get_first_feed_id() ) ) {
				$this->feed_id = '';
			} else {
				$this->feed_id = xfavi_get_first_feed_id();
			}
		}
		if ( isset( $_GET['tab'] ) ) {
			$this->cur_tab = sanitize_text_field( $_GET['tab'] );
		}

		$this->init_classes();
		$this->init_hooks();
		$this->listen_submit();

		$this->print_view_html_form();
	}

	/**
	 * Initialization classes
	 * 
	 * @return void
	 */
	public function init_classes() {

	}

	/**
	 * Initialization hooks
	 * 
	 * @return void
	 */
	public function init_hooks() {
		// наш класс, вероятно, вызывается во время срабатывания хука admin_menu.
		// admin_init - следующий в очереди срабатывания, на хуки раньше admin_menu нет смысла вешать
		// add_action('admin_init', [ $this, 'my_func' ], 10, 1);

	}

	/**
	 * The function listens for the send buttons
	 * 
	 * @return void
	 */
	private function listen_submit() {
		// массовое удаление фидов по чекбоксу checkbox_xml_file
		if ( isset( $_GET['xfavi_form_id'] ) && ( $_GET['xfavi_form_id'] === 'xfavi_wp_list_table' ) ) {
			if ( is_array( $_GET['checkbox_xml_file'] ) && ! empty( $_GET['checkbox_xml_file'] ) ) {
				if ( $_GET['action'] === 'delete' || $_GET['action2'] === 'delete' ) {
					$checkbox_xml_file_arr = $_GET['checkbox_xml_file'];
					$xfavi_settings_arr = xfavi_optionGET( 'xfavi_settings_arr' );
					for ( $i = 0; $i < count( $checkbox_xml_file_arr ); $i++ ) {
						$feed_id = $checkbox_xml_file_arr[ $i ];
						unset( $xfavi_settings_arr[ $feed_id ] );
						wp_clear_scheduled_hook( 'xfavi_cron_period', [ $feed_id ] ); // отключаем крон
						wp_clear_scheduled_hook( 'xfavi_cron_sborki', [ $feed_id ] ); // отключаем крон
						remove_directory( XFAVI_PLUGIN_UPLOADS_DIR_PATH . '/feed' . $feed_id );
						// ! возможно надо будет вернуть но с др.функцией xfavi_optionDEL( 'xfavi_status_sborki', $i );

						$xfavi_registered_feeds_arr = xfavi_optionGET( 'xfavi_registered_feeds_arr' );
						// первый элемент не проверяем, тк. там инфо по последнему id
						for ( $n = 1; $n < count( $xfavi_registered_feeds_arr ); $n++ ) {
							if ( $xfavi_registered_feeds_arr[ $n ]['id'] === $feed_id ) {
								unset( $xfavi_registered_feeds_arr[ $n ] );
								$xfavi_registered_feeds_arr = array_values( $xfavi_registered_feeds_arr );
								xfavi_optionUPD( 'xfavi_registered_feeds_arr', $xfavi_registered_feeds_arr );
								break;
							}
						}
					}
					xfavi_optionUPD( 'xfavi_settings_arr', $xfavi_settings_arr );
					$this->feed_id = xfavi_get_first_feed_id();
				}
			}
		}

		if ( isset( $_REQUEST['xfavi_submit_action'] ) || isset( $_REQUEST['xfavi_submit_action'] ) ) {
			if ( ! empty( $_POST ) && check_admin_referer( 'xfavi_nonce_action', 'xfavi_nonce_field' ) ) {
				do_action( 'xfavi_prepend_submit_action', $this->get_feed_id() );
				$feed_id = sanitize_text_field( $_POST['xfavi_feed_id_for_save'] );

				common_option_upd(
					'xfavi_date_save_set',
					current_time( 'timestamp', true ),
					'no',
					$feed_id,
					'xfavi'
				);

				if ( isset( $_POST['xfavi_run_cron'] ) ) {
					$run_cron = sanitize_text_field( $_POST['xfavi_run_cron'] );
					common_option_upd( 'xfavi_status_cron', $run_cron, 'no', $feed_id, 'xfavi' );
					// xfavi_optionUPD('xfavi_status_cron', $run_cron, $feed_id, 'yes', 'set_arr');

					if ( $run_cron === 'disabled' ) {
						// отключаем крон
						wp_clear_scheduled_hook( 'xfavi_cron_period', [ $feed_id ] );
						common_option_upd( 'xfavi_status_cron', 'disabled', 'no', $feed_id, 'xfavi' );

						wp_clear_scheduled_hook( 'cron_sborki', [ $feed_id ] );
						common_option_upd( 'xfavi_cron_sborki', '-1', 'no', $feed_id, 'xfavi' );
					} else if ( $run_cron === 'once' ) {
						// единоразовый импорт
						common_option_upd( 'xfavi_cron_sborki', '-1', 'no', $feed_id, 'xfavi' );
						// ? в теории тут можно регулировать "продолжить импорт" или "с нуля"
						wp_clear_scheduled_hook( 'xfavi_cron_period', [ $feed_id ] );
						wp_schedule_single_event( time() + 3, 'xfavi_cron_period', [ $feed_id ] ); // старт через 3 сек
						new XFAVI_Error_Log( sprintf( 'FEED № %1$s; %2$s. Файл: %3$s; Строка: %4$s',
							'Единоразово xfavi_cron_period внесен в список заданий',
							$this->get_feed_id(),
							'class-xfavi-settings-page.php',
							__LINE__
						) );
					} else {
						wp_clear_scheduled_hook( 'xfavi_cron_period', [ $feed_id ] );
						wp_schedule_event( time(), $run_cron, 'xfavi_cron_period', [ $feed_id ] );
						new XFAVI_Error_Log( sprintf( 'FEED № %1$s; %2$s. Файл: %3$s; Строка: %4$s',
							'xfavi_cron_period внесен в список заданий',
							$this->get_feed_id(),
							'class-xfavi-settings-page.php',
							__LINE__
						) );
					}
				}

				$def_plugin_date_arr = new XFAVI_Data_Arr();
				$opts_name_and_def_date_arr = $def_plugin_date_arr->get_opts_name_and_def_date( 'public' );
				foreach ( $opts_name_and_def_date_arr as $opt_name => $value ) {
					$save_if_empty = 'no';
					$save_if_empty = apply_filters( 'xfavi_f_save_if_empty', $save_if_empty, [ 'opt_name' => $opt_name ] );
					$this->save_plugin_set( $opt_name, $feed_id, $save_if_empty );
				}
				do_action( 'xfavi_settings_page_listen_submit', $feed_id );
				$this->feed_id = $feed_id;
			}
		}

		if ( isset( $_REQUEST['xfavi_submit_add_new_feed'] ) ) { // если создаём новый фид
			if ( ! empty( $_POST )
				&& check_admin_referer( 'xfavi_nonce_action_add_new_feed', 'xfavi_nonce_field_add_new_feed' ) ) {
				$xfavi_settings_arr = xfavi_optionGET( 'xfavi_settings_arr' );

				if ( is_multisite() ) {
					$xfavi_registered_feeds_arr = get_blog_option( get_current_blog_id(), 'xfavi_registered_feeds_arr' );
					$feed_id = $xfavi_registered_feeds_arr[0]['last_id'];
					$feed_id++;
					$xfavi_registered_feeds_arr[0]['last_id'] = (string) $feed_id;
					$xfavi_registered_feeds_arr[] = [ 'id' => (string) $feed_id ];
					update_blog_option( get_current_blog_id(), 'xfavi_registered_feeds_arr', $xfavi_registered_feeds_arr );
				} else {
					$xfavi_registered_feeds_arr = get_option( 'xfavi_registered_feeds_arr' );
					$feed_id = $xfavi_registered_feeds_arr[0]['last_id'];
					$feed_id++;
					$xfavi_registered_feeds_arr[0]['last_id'] = (string) $feed_id;
					$xfavi_registered_feeds_arr[] = [ 'id' => (string) $feed_id ];
					update_option( 'xfavi_registered_feeds_arr', $xfavi_registered_feeds_arr );
				}

				$name_dir = XFAVI_PLUGIN_UPLOADS_DIR_PATH . '/feed' . $feed_id;
				if ( ! is_dir( $name_dir ) ) {
					if ( ! mkdir( $name_dir ) ) {
						error_log( sprintf( 'ERROR: Ошибка создания папки %s; Файл: %s; Строка: %s',
							$name_dir,
							'class-xfavi-settings-page.php',
							__LINE__
						), 0 );
					}
				}

				$def_plugin_date_arr = new XFAVI_Data_Arr();
				$xfavi_settings_arr[ $feed_id ] = $def_plugin_date_arr->get_opts_name_and_def_date( 'all' );

				xfavi_optionUPD( 'xfavi_settings_arr', $xfavi_settings_arr );

				common_option_add('xfavi_status_sborki', '-1', 'no', $feed_id, 'xfavi');
				common_option_add('xfavi_last_element', '-1', 'no', $feed_id, 'xfavi');
				printf( '<div class="updated notice notice-success is-dismissible"><p>%s. ID = %s.</p></div>',
					esc_html__( 'Добавлен фид', 'xml-for-avito' ),
					$feed_id
				);

				$this->feed_id = $feed_id;
			}
		}

		// дублировать фид
		if ( isset( $_GET['feed_id'] )
			&& isset( $_GET['action'] )
			&& sanitize_text_field( $_GET['action'] ) === 'duplicate'
		) {
			$feed_id = (string) sanitize_text_field( $_GET['feed_id'] );
			if ( wp_verify_nonce( $_GET['_wpnonce'], 'nonce_duplicate' . $feed_id ) ) {
				$xfavi_settings_arr = univ_option_get( 'xfavi_settings_arr' );
				$new_data_arr = $xfavi_settings_arr[ $feed_id ];
				$xfavi_params_arr = xfavi_optionGET( 'xfavi_params_arr', $feed_id );
				if ( class_exists( 'XmlforAvitoPro' ) ) {
					$params_arr = xfavi_optionGET( 'xfavip_exclude_cat_arr', $feed_id );
				}

				// обнулим часть значений т.к фид-клон ещё не создавался
				$new_data_arr['xfavi_file_url'] = '';
				$new_data_arr['xfavi_file_file'] = '';
				$new_data_arr['xfavi_status_cron'] = 'off';
				$new_data_arr['xfavi_date_sborki'] = '-'; // 'Y-m-d H:i
				$new_data_arr['xfavi_date_sborki_end'] = '-'; // 'Y-m-d H:i
				$new_data_arr['xfavi_date_save_set'] = 0000000001; // 0000000001 - timestamp format
				$new_data_arr['xfavi_count_products_in_feed'] = '-1';

				// обновим список зарегистрированных фидов
				if ( is_multisite() ) {
					$xfavi_registered_feeds_arr = get_blog_option( get_current_blog_id(), 'xfavi_registered_feeds_arr' );
					$feed_id = $xfavi_registered_feeds_arr[0]['last_id'];
					$feed_id++;
					$xfavi_registered_feeds_arr[0]['last_id'] = (string) $feed_id;
					$xfavi_registered_feeds_arr[] = [ 'id' => (string) $feed_id ];
					update_blog_option( get_current_blog_id(), 'xfavi_registered_feeds_arr', $xfavi_registered_feeds_arr );
				} else {
					$xfavi_registered_feeds_arr = get_option( 'xfavi_registered_feeds_arr' );
					$feed_id = $xfavi_registered_feeds_arr[0]['last_id'];
					$feed_id++;
					$xfavi_registered_feeds_arr[0]['last_id'] = (string) $feed_id;
					$xfavi_registered_feeds_arr[] = [ 'id' => (string) $feed_id ];
					update_option( 'xfavi_registered_feeds_arr', $xfavi_registered_feeds_arr );
				}

				// запишем данные в базу
				$xfavi_settings_arr[ $feed_id ] = $new_data_arr;
				xfavi_optionUPD( 'xfavi_settings_arr', $xfavi_settings_arr );
				common_option_add('xfavi_status_sborki', '-1', 'no', $feed_id, 'xfavi');
				common_option_add('xfavi_last_element', '-1', 'no', $feed_id, 'xfavi');

				xfavi_optionUPD( 'xfavip_params_arr', $xfavi_params_arr, $feed_id );
				if ( class_exists( 'XmlforAvitoPro' ) ) {
					xfavi_optionUPD( 'xfavip_exclude_cat_arr', $params_arr, $feed_id );
				}

				// создадим папку
				$name_dir = XFAVI_PLUGIN_UPLOADS_DIR_PATH . '/feed' . $feed_id;
				if ( ! is_dir( $name_dir ) ) {
					if ( ! mkdir( $name_dir ) ) {
						error_log(
							'ERROR: Ошибка создания папки ' . $name_dir . '; Файл: class-xfavi-settings-page.php; Строка: ' . __LINE__,
							0
						);
					}
				}

				$url = admin_url() . '?page=xfaviexport&action=edit&feed_id=' . $feed_id . '&duplicate=true';
				wp_safe_redirect( $url );
			}
		}

		return;
	}

	/**
	 * Summary of print_view_html_form
	 * 
	 * @return void
	 */
	public function print_view_html_form() {
		$view_arr = [ 
			'feed_id' => $this->get_feed_id(),
			'tab_name' => $this->get_tab_name(),
			'tabs_arr' => $this->get_tabs_arr(),
			'prefix_feed' => $this->get_prefix_feed(),
			'current_blog_id' => $this->get_current_blog_id()
		];
		include_once __DIR__ . '/views/html-admin-settings-page.php';
	}

	/**
	 * Get tabs arr
	 * 
	 * @param string $current
	 * @return array
	 */
	public function get_tabs_arr( $current = 'main_tab' ) {
		$tabs_arr = [ 
			'main_tab' => sprintf( '%s (%s: %s)',
				__( 'Основные настройки', 'xml-for-avito' ),
				__( 'Фид', 'xml-for-avito' ),
				$this->get_feed_id()
			),
			'tags_settings_tab' => sprintf( '%s (%s: %s)',
				__( 'Настройки тегов', 'xml-for-avito' ),
				__( 'Фид', 'xml-for-avito' ),
				$this->get_feed_id()
			),
			'filtration_tab' => sprintf( '%s (%s: %s)',
				__( 'Фильтрация', 'xml-for-avito' ),
				__( 'Фид', 'xml-for-avito' ),
				$this->get_feed_id()
			)
		];
		$tabs_arr = apply_filters( 'xfavi_f_tabs_arr', $tabs_arr, [ 'feed_id' => $this->get_feed_id() ] );
		return $tabs_arr;
	}

	/**
	 * Summary of print_view_html_fields
	 * 
	 * @param string $tab
	 * 
	 * @return void
	 */
	public static function print_view_html_fields( $tab, $feed_id ) {
		$xfavi_data_arr_obj = new XFAVI_Data_Arr();
		$data_for_tab_arr = $xfavi_data_arr_obj->get_data_for_tabs( $tab ); // список дефолтных настроек

		for ( $i = 0; $i < count( $data_for_tab_arr ); $i++ ) {
			switch ( $data_for_tab_arr[ $i ]['type'] ) {
				case 'text':
					self::get_view_html_field_input( $data_for_tab_arr[ $i ], $feed_id );
					break;
				case 'number':
					self::get_view_html_field_number( $data_for_tab_arr[ $i ], $feed_id );
					break;
				case 'select':
					self::get_view_html_field_select( $data_for_tab_arr[ $i ], $feed_id );
					break;
				case 'textarea':
					self::get_view_html_field_textarea( $data_for_tab_arr[ $i ], $feed_id );
					break;
				default:
					do_action( 'xfavi_f_print_view_html_fields', $data_for_tab_arr[ $i ], $feed_id );
			}
		}
	}

	/**
	 * Summary of get_view_html_field_input
	 * 
	 * @param array $data_arr
	 * 
	 * @return void
	 */
	public static function get_view_html_field_input( $data_arr, $feed_id ) {
		if ( isset( $data_arr['tr_class'] ) ) {
			$tr_class = $data_arr['tr_class'];
		} else {
			$tr_class = '';
		}
		printf( '<tr class="%1$s">
					<th scope="row"><label for="%2$s">%3$s</label></th>
					<td class="overalldesc">
						<input 
							type="text" 
							name="%2$s" 
							id="%2$s" 
							value="%4$s"
							placeholder="%5$s" 
							class="xfavi_input" 
							style="%6$s"/><br />
						<span class="description"><small>%7$s</small></span>
					</td>
				</tr>',
			esc_attr( $tr_class ),
			esc_attr( $data_arr['opt_name'] ),
			wp_kses( $data_arr['label'], self::ALLOWED_HTML_ARR ),
			esc_attr( common_option_get( $data_arr['opt_name'], false, $feed_id, 'xfavi' ) ),
			esc_html( $data_arr['placeholder'] ),
			'width: 100%;',
			wp_kses( $data_arr['desc'], 'default' )
		);
	}

	/**
	 * Summary of get_view_html_field_number
	 * 
	 * @param array $data_arr
	 * 
	 * @return void
	 */
	public static function get_view_html_field_number( $data_arr, $feed_id ) {
		if ( isset( $data_arr['tr_class'] ) ) {
			$tr_class = $data_arr['tr_class'];
		} else {
			$tr_class = '';
		}
		if ( isset( $data_arr['min'] ) ) {
			$min = $data_arr['min'];
		} else {
			$min = '';
		}
		if ( isset( $data_arr['max'] ) ) {
			$max = $data_arr['max'];
		} else {
			$max = '';
		}
		if ( isset( $data_arr['step'] ) ) {
			$step = $data_arr['step'];
		} else {
			$step = '';
		}

		printf( '<tr class="%1$s">
					<th scope="row"><label for="%2$s">%3$s</label></th>
					<td class="overalldesc">
						<input 
							type="number" 
							name="%2$s" 
							id="%2$s" 
							value="%4$s"
							placeholder="%5$s" 
							min="%6$s"
							max="%7$s"
							step="%8$s"
							class="xfavi_input"
							/><br />
						<span class="description"><small>%9$s</small></span>
					</td>
				</tr>',
			esc_attr( $tr_class ),
			esc_attr( $data_arr['opt_name'] ),
			wp_kses( $data_arr['label'], self::ALLOWED_HTML_ARR ),
			esc_attr( common_option_get( $data_arr['opt_name'], false, $feed_id, 'xfavi' ) ),
			esc_html( $data_arr['placeholder'] ),
			esc_attr( $min ),
			esc_attr( $max ),
			esc_attr( $step ),
			wp_kses( $data_arr['desc'], 'default' )
		);
	}

	/**
	 * Summary of get_view_html_field_select
	 * 
	 * @param array $data_arr
	 * 
	 * @return void
	 */
	public static function get_view_html_field_select( $data_arr, $feed_id ) {
		if ( isset( $data_arr['key_value_arr'] ) ) {
			$key_value_arr = $data_arr['key_value_arr'];
		} else {
			$key_value_arr = [];
		}
		if ( isset( $data_arr['categories_list'] ) ) {
			$categories_list = $data_arr['categories_list'];
		} else {
			$categories_list = false;
		}
		if ( isset( $data_arr['tags_list'] ) ) {
			$tags_list = $data_arr['tags_list'];
		} else {
			$tags_list = false;
		}
		if ( isset( $data_arr['tr_class'] ) ) {
			$tr_class = $data_arr['tr_class'];
		} else {
			$tr_class = '';
		}
		if ( isset( $data_arr['size'] ) ) {
			$size = $data_arr['size'];
		} else {
			$size = '1';
		}
		// массивы храним отдельно от других параметров
		if ( isset( $data_arr['multiple'] ) && true === $data_arr['multiple'] ) {
			$multiple = true;
			$multiple_val = '[]" multiple';
			$value = unserialize( xfavi_optionGET( $data_arr['opt_name'], $feed_id ) );
		} else {
			$multiple = false;
			$multiple_val = '"';
			$value = common_option_get(
				$data_arr['opt_name'],
				false,
				$feed_id,
				'xfavi' );
		}

		printf( '<tr class="%1$s">
				<th scope="row"><label for="%2$s">%3$s</label></th>
				<td class="overalldesc">
					<select name="%2$s%5$s id="%2$s" size="%4$s"/>%6$s</select><br />
					<span class="description"><small>%7$s</small></span>
				</td>
			</tr>',
			esc_attr( $tr_class ),
			esc_attr( $data_arr['opt_name'] ),
			wp_kses( $data_arr['label'], self::ALLOWED_HTML_ARR ),
			esc_attr( $size ),
			$multiple_val,
			self::print_view_html_option_for_select(
				$value,
				$data_arr['opt_name'],
				[ 
					'woo_attr' => $data_arr['woo_attr'],
					'key_value_arr' => $key_value_arr,
					'categories_list' => $categories_list,
					'tags_list' => $tags_list,
					'multiple' => $multiple
				]
			),
			wp_kses( $data_arr['desc'], 'default' )
		);
	}

	/**
	 * Summary of print_view_html_option_for_select
	 * 
	 * @param mixed $opt_value
	 * @param string $opt_name
	 * @param array $params_arr
	 * @param mixed $res
	 * 
	 * @return mixed
	 */
	public static function print_view_html_option_for_select( $opt_value, string $opt_name, $params_arr = [], $res = '' ) {
		if ( true === $params_arr['multiple'] ) {
			$res .= sprintf( '<optgroup label="%s">', __( 'Категории', 'xfavip' ) );
			foreach ( get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => 0, 'parent' => 0 ] ) as $term ) {
				$res .= the_cat_tree( $term->taxonomy, $term->term_id, $opt_value );
			}
			$res .= '</optgroup>';

			$res .= sprintf( '<optgroup label="%s">', __( 'Теги', 'xfavip' ) );
			foreach ( get_terms( [ 'taxonomy' => 'product_tag', 'hide_empty' => 0, 'parent' => 0 ] ) as $term ) {
				$res .= the_cat_tree( $term->taxonomy, $term->term_id, $opt_value );
			}
			$res .= '</optgroup>';
		} else {
			if ( ! empty( $params_arr['key_value_arr'] ) ) {
				for ( $i = 0; $i < count( $params_arr['key_value_arr'] ); $i++ ) {
					$res .= sprintf( '<option value="%1$s" %2$s>%3$s</option>' . PHP_EOL,
						esc_attr( $params_arr['key_value_arr'][ $i ]['value'] ),
						esc_attr( selected( $opt_value, $params_arr['key_value_arr'][ $i ]['value'], false ) ),
						esc_attr( $params_arr['key_value_arr'][ $i ]['text'] )
					);
				}
			}

			if ( ! empty( $params_arr['woo_attr'] ) ) {
				$woo_attributes_arr = get_woo_attributes();
				for ( $i = 0; $i < count( $woo_attributes_arr ); $i++ ) {
					$res .= sprintf( '<option value="%1$s" %2$s>%3$s</option>' . PHP_EOL,
						esc_attr( $woo_attributes_arr[ $i ]['id'] ),
						esc_attr( selected( $opt_value, $woo_attributes_arr[ $i ]['id'], false ) ),
						esc_attr( $woo_attributes_arr[ $i ]['name'] )
					);
				}
				unset( $woo_attributes_arr );
			}
		}

		return $res;
	}

	/**
	 * Summary of get_view_html_field_textarea
	 * 
	 * @param array $data_arr
	 * 
	 * @return void
	 */
	public static function get_view_html_field_textarea( $data_arr, $feed_id ) {
		if ( isset( $data_arr['tr_class'] ) ) {
			$tr_class = $data_arr['tr_class'];
		} else {
			$tr_class = '';
		}
		if ( isset( $data_arr['rows'] ) ) {
			$rows = $data_arr['rows'];
		} else {
			$rows = '6';
		}
		if ( isset( $data_arr['cols'] ) ) {
			$cols = $data_arr['cols'];
		} else {
			$cols = '32';
		}
		printf( '<tr class="%1$s">
					<th scope="row"><label for="%2$s">%3$s</label></th>
					<td class="overalldesc">
						<textarea 							 
							name="%2$s" 
							id="%2$s" 
							rows="%4$s" 
							cols="%5$s"
							class="xfavi_textarea"
							placeholder="%6$s">%7$s</textarea><br />
						<span class="description"><small>%8$s</small></span>
					</td>
				</tr>',
			esc_attr( $tr_class ),
			esc_attr( $data_arr['opt_name'] ),
			wp_kses( $data_arr['label'], self::ALLOWED_HTML_ARR ),
			esc_attr( $rows ),
			esc_attr( $cols ),
			esc_html( $data_arr['placeholder'] ),
			esc_attr( common_option_get( $data_arr['opt_name'], false, $feed_id, 'xfavi' ) ),
			wp_kses( $data_arr['desc'], 'default' )
		);
	}

	/**
	 * Get feed ID
	 * 
	 * @return string
	 */
	private function get_feed_id() {
		return $this->feed_id;
	}

	/**
	 * Get current tab
	 * 
	 * @return string
	 */
	private function get_tab_name() {
		return $this->cur_tab;
	}

	/**
	 * Save plugin settings
	 * 
	 * @param string $opt_name
	 * @param string $feed_id
	 * @param string $save_if_empty
	 * 
	 * @return void
	 */
	private function save_plugin_set( $opt_name, $feed_id = '1', $save_if_empty = 'no' ) {
		if ( isset( $_POST[ $opt_name ] ) ) {
			if ( is_array( $_POST[ $opt_name ] ) ) {
				// массивы храним отдельно от других параметров
				xfavi_optionUPD( $opt_name, serialize( $_POST[ $opt_name ] ), $feed_id );
			} else {
				$value = preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $_POST[ $opt_name ] );
				common_option_upd( $opt_name, $value, 'no', $feed_id, 'xfavi' );
			}
		} else {
			if ( 'empty_str' === $save_if_empty ) {
				common_option_upd( $opt_name, '', 'no', $feed_id, 'xfavi' );
			}
			if ( 'empty_arr' === $save_if_empty ) {
				// массивы храним отдельно от других параметров
				xfavi_optionUPD( $opt_name, serialize( [] ), $feed_id );
			}
		}
		return;
	}

	/**
	 * Возвращает префикс фида
	 * 
	 * @return string
	 */
	private function get_prefix_feed() {
		if ( $this->get_feed_id() == '1' ) {
			$prefix_feed = '';
		} else {
			$prefix_feed = $this->get_feed_id();
		}
		return (string) $prefix_feed;
	}

	/**
	 * Возвращает id текущего блога
	 * 
	 * @return string
	 */
	private function get_current_blog_id() {
		if ( is_multisite() ) {
			$cur_blog_id = get_current_blog_id();
		} else {
			$cur_blog_id = '0';
		}
		return (string) $cur_blog_id;
	}
}