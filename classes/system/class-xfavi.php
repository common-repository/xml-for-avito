<?php
/**
 * The main class of the plugin XML for Avito
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   1.7.0
 * 
 * @version                 2.5.0 (04-10-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param       
 *
 * @depends                 classes:    XFAVI_Data_Arr
 *                                      XFAVI_Settings_Page
 *                                      XFAVI_Debug_Page
 *                                      XFAVI_Error_Log
 *                                      XFAVI_Generation_XML
 *                          traits:     
 *                          methods:    
 *                          functions:  common_option_get
 *                                      common_option_upd
 *                                      univ_option_get
 *                                      univ_option_upd
 *                                      xfavi_optionGET
 *                                      xfavi_optionUPD
 *                          constants:	XFAVI_PLUGIN_VERSION
 *                                      XFAVI_PLUGIN_BASENAME
 *                                      XFAVI_PLUGIN_DIR_URL
 *                                      XFAVI_PLUGIN_UPLOADS_DIR_URL
 *
 */
defined( 'ABSPATH' ) || exit;

final class XmlforAvito {
	/**
	 * The list of allowed tags that will be passed to the function wp_kses()
	 * @var array
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
	 * The current version of the plugin
	 * @var string
	 */
	private $plugin_version = XFAVI_PLUGIN_VERSION; // 1.0.0

	/**
	 * Instance
	 * @var XmlforAvito
	 */
	protected static $instance;

	/**
	 * Initialization XmlforAvito class.
	 * @return XmlforAvito
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Срабатывает при активации плагина (вызывается единожды)
	 * 
	 * @return void
	 */
	public static function on_activation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		if ( ! is_dir( XFAVI_SITE_UPLOADS_DIR_PATH ) ) {
			if ( ! mkdir( XFAVI_SITE_UPLOADS_DIR_PATH ) ) {
				error_log(
					sprintf( '%s %s; Файл: class-xfavi.php; Строка: ',
						'ERROR: Ошибка создания папки',
						XFAVI_SITE_UPLOADS_DIR_PATH,
						__LINE__
					), 0 );
			}
		}

		$name_dir = XFAVI_SITE_UPLOADS_DIR_PATH . '/feed1';
		if ( ! is_dir( $name_dir ) ) {
			if ( ! mkdir( $name_dir ) ) {
				error_log(
					sprintf( '%s %s; Файл: class-xfavi.php; Строка: ',
						'ERROR: Ошибка создания папки',
						$name_dir,
						__LINE__
					), 0 );
			}
		}

		$xfavi_registered_feeds_arr = [ 
			0 => [ 'last_id' => '1' ],
			1 => [ 'id' => '1' ]
		];

		$def_plugin_date_arr = new XFAVI_Data_Arr();
		$xfavi_settings_arr = [];
		$xfavi_settings_arr['1'] = $def_plugin_date_arr->get_opts_name_and_def_date( 'all' );

		if ( is_multisite() ) {
			add_blog_option( get_current_blog_id(), 'xfavi_version', XFAVI_PLUGIN_VERSION );
			add_blog_option( get_current_blog_id(), 'xfavi_keeplogs', '' );
			add_blog_option( get_current_blog_id(), 'xfavi_disable_notices', '' );
			add_blog_option( get_current_blog_id(), 'xfavi_feed_content', '' );

			add_blog_option( get_current_blog_id(), 'xfavi_settings_arr', $xfavi_settings_arr );
			add_blog_option( get_current_blog_id(), 'xfavi_registered_feeds_arr', $xfavi_registered_feeds_arr );
		} else {
			add_option( 'xfavi_version', XFAVI_PLUGIN_VERSION );
			add_option( 'xfavi_keeplogs', '' );
			add_option( 'xfavi_disable_notices', '' );
			add_option( 'xfavi_feed_content', '' );

			add_option( 'xfavi_settings_arr', $xfavi_settings_arr );
			add_option( 'xfavi_registered_feeds_arr', $xfavi_registered_feeds_arr );
		}
	}

	/**
	 * Срабатывает при отключении плагина (вызывается единожды)
	 * 
	 * @return void
	 */
	public static function on_deactivation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$xfavi_registered_feeds_arr = univ_option_get( 'xfavi_registered_feeds_arr' );
		if ( ! empty( $xfavi_registered_feeds_arr ) ) {
			for ( $i = 1; $i < count( $xfavi_registered_feeds_arr ); $i++ ) { // с единицы, т.к инфа по конкретным фидам там
				// с единицы, т.к инфа по конкретным фидам там
				$feed_id = $xfavi_registered_feeds_arr[ $i ]['id'];
				wp_clear_scheduled_hook( 'xfavi_cron_period', [ $feed_id ] ); // отключаем крон
				wp_clear_scheduled_hook( 'xfavi_cron_sborki', [ $feed_id ] ); // отключаем крон

				common_option_upd( 'xfavi_run_cron', 'disabled', 'no', $feed_id, 'xfavi' );
				common_option_upd( 'xfavi_status_cron', 'disabled', 'no', $feed_id, 'xfavi' );

				// * c id == '1' у нас, будет загвоздка при таком подходе, но в целом пойдёт и так
				univ_option_upd( 'xfavi_status_sborki' . $feed_id, '-1' );
			}
		}
		deactivate_plugins( 'xml-for-avito-pro/xml-for-avito-pro.php' ); // ? удалить
	}

	/**
	 * The main class of the plugin XML for Avito
	 */
	public function __construct() {
		$this->check_and_fix(); // если вдруг нет настроек плагина
		$this->check_options_upd(); // проверим, нужны ли обновления опций плагина
		$this->init_classes();
		$this->init_hooks(); // подключим хуки
	}

	/**
	 * Если по непонятной причине нет настроек плагина - создаём
	 * 
	 * @return void
	 */
	public function check_and_fix() {
		$settings_arr = univ_option_get( 'xfavi_settings_arr' );
		if ( ! is_array( $settings_arr ) ) {
			self::on_activation();
		}
	}

	/**
	 * Checking whether the plugin options need to be updated
	 * 
	 * @return void
	 */
	public function check_options_upd() {
		if ( false == univ_option_get( 'xfavi_version' ) ) { // это первая установка
			if ( is_multisite() ) {
				update_blog_option( get_current_blog_id(), 'xfavi_version', $this->plugin_version );
			} else {
				update_option( 'xfavi_version', $this->plugin_version );
			}
		} else {
			$this->set_new_options();
		}
	}

	/**
	 * Summary of set_new_options
	 * 
	 * @return void
	 */
	public function set_new_options() {
		// Если предыдущая версия плагина меньше текущей
		if ( version_compare( $this->get_plugin_version(), $this->plugin_version, '<' ) ) {

		} else { // обновления не требуются
			return;
		}

		$xfavi_data_arr_obj = new XFAVI_Data_Arr();
		$opts_arr = $xfavi_data_arr_obj->get_opts_name_and_def_date_obj( 'all' ); // список дефолтных настроек
		// проверим, заданы ли дефолтные настройки
		$xfavi_settings_arr = univ_option_get( 'xfavi_settings_arr' );
		if ( is_array( $xfavi_settings_arr ) ) {
			$xfavi_settings_arr_keys_arr = array_keys( $xfavi_settings_arr );
			for ( $i = 0; $i < count( $xfavi_settings_arr_keys_arr ); $i++ ) {
				// ! т.к у нас работа с array_keys, то в $feed_id может быть int. Для гарантии сделаем string
				$feed_id = (string) $xfavi_settings_arr_keys_arr[ $i ];
				for ( $n = 0; $n < count( $opts_arr ); $n++ ) {
					$name = $opts_arr[ $n ]->name;
					$value = $opts_arr[ $n ]->opt_def_value;
					if ( ! isset( $xfavi_settings_arr[ $feed_id ][ $name ] ) ) {
						xfavi_optionUPD( $name, $value, $feed_id, 'yes', 'set_arr' );
					}
				}
			}
		}

		if ( is_multisite() ) {
			update_blog_option( get_current_blog_id(), 'xfavi_version', $this->plugin_version );
		} else {
			update_option( 'xfavi_version', $this->plugin_version );
		}
	}

	/**
	 * Get plugin version
	 * 
	 * @return string
	 */
	public function get_plugin_version() {
		if ( is_multisite() ) {
			$v = get_blog_option( get_current_blog_id(), 'xfavi_version' );
		} else {
			$v = get_option( 'xfavi_version' );
		}
		return (string) $v;
	}

	/**
	 * Initialization classes
	 * 
	 * @return void
	 */
	public function init_classes() {
		new XFAVI_Interface_Hoocked();
		new ICPD_Feedback( [ 
			'plugin_name' => 'XML for Avito',
			'plugin_version' => $this->get_plugin_version(),
			'logs_url' => XFAVI_PLUGIN_UPLOADS_DIR_URL . '/plugin.log',
			'pref' => 'xfavi'
		] );
		new ICPD_Promo( 'xfavi' );
		return;
	}

	/**
	 * Initialization hooks
	 * 
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'wp_loaded', [ $this, 'check_forced_cron' ], 10 );
		add_action( 'admin_init', [ $this, 'listen_submits' ], 10 ); // ещё можно слушать чуть раньше на wp_loaded
		add_action( 'admin_init', function () {
			wp_register_style( 'xfavi-admin-css', XFAVI_PLUGIN_DIR_URL . 'assets/css/xfavi_style.css' );
		}, 9999 ); // Регаем стили только для страницы настроек плагина
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ], 10, 1 );
		add_filter( 'plugin_action_links', [ $this, 'add_plugin_action_links' ], 10, 2 );

		add_filter( 'upload_mimes', [ $this, 'add_mime_types' ], 99, 1 ); // чутка позже остальных
		add_filter( 'cron_schedules', [ $this, 'add_cron_intervals' ], 10, 1 );

		add_action( 'xfavi_cron_sborki', [ $this, 'xfavi_do_this_seventy_sec' ], 10, 1 );
		add_action( 'xfavi_cron_period', [ $this, 'xfavi_do_this_event' ], 10, 1 );

		add_action( 'admin_notices', [ $this, 'notices_prepare' ], 10, 1 );
		add_action( 'admin_enqueue_scripts', [ &$this, 'reg_script' ] ); // правильно регаем скрипты в админку

		// дополнительные данные для фидбэка
		add_filter( 'xfavi_f_feedback_additional_info', [ $this, 'feedback_additional_info' ], 10, 1 );
	}

	/**
	 * Forced to start wp-cron.php if CRON tasks are overdue by more than `85` seconds
	 * 
	 * @return void
	 */
	public function check_forced_cron() {
		forced_cron(); // принудительно дёрним крон при больших просрочках
	}

	/**
	 * Listen submits. Function for `admin_init` action-hook.
	 * 
	 * @return void
	 */
	public function listen_submits() {
		do_action( 'xfavi_listen_submits' );

		if ( isset( $_REQUEST['xfavi_submit_action'] ) ) {
			$message = __( 'Обновлено', 'xml-for-avito' );
			$class = 'notice-success';
			if ( isset( $_POST['xfavi_run_cron'] ) && sanitize_text_field( $_POST['xfavi_run_cron'] ) !== 'off' ) {
				$message .= '. ' . __( 'Создание XML-фида запущено. Вы можете продолжить работу с сайтом', 'xml-for-avito' );
			}

			add_action( 'admin_notices', function () use ($message, $class) {
				$this->print_admin_notice( $message, $class );
			}, 10, 2 );
		}
	}

	public function add_plugin_action_links( $actions, $plugin_file ) {
		if ( false === strpos( $plugin_file, XFAVI_PLUGIN_BASENAME ) ) { // проверка, что у нас текущий плагин
			return $actions;
		}

		$settings_link = sprintf( '<a style="%s" href="/wp-admin/admin.php?page=%s">%s</a>',
			'color: green; font-weight: 700;',
			'xfaviextensions',
			__( 'Больше функций', 'xml-for-avito' )
		);
		array_unshift( $actions, $settings_link );

		$settings_link = sprintf( '<a href="/wp-admin/admin.php?page=%s">%s</a>',
			'xfaviexport',
			__( 'Настройки', 'xml-for-avito' )
		);
		array_unshift( $actions, $settings_link );

		return $actions;
	}

	/**
	 * Registration scripts
	 * 
	 * @see https://daext.com/blog/how-to-add-select2-in-wordpress/
	 * 
	 * @return void
	 */
	public function reg_script() {
		// правильно регаем скрипты в админку через промежуточную функцию
		wp_enqueue_script( 'xfavi_find_products', XFAVI_PLUGIN_DIR_URL . 'assets/js/jquery.chained.min.js', [ 'jquery' ] );
		wp_enqueue_script( 'select2-js', XFAVI_PLUGIN_DIR_URL . 'assets/js/select2.min.js', [ 'jquery' ] );
		wp_enqueue_script( 'select2-init', XFAVI_PLUGIN_DIR_URL . 'assets/js/select2-init.js', [ 'jquery' ] );
		wp_enqueue_style( 'select2-css', XFAVI_PLUGIN_DIR_URL . 'assets/css/select2.min.css', [] );
	}

	public static function admin_css_func() {
		// Ставим css-файл в очередь на вывод
		wp_enqueue_style( 'xfavi-admin-css' );
	}

	/**
	 * Add items to admin menu. Function for `admin_menu` action-hook.
	 * 
	 * @param string $context Empty context
	 * 
	 * @return void
	 */
	public function add_admin_menu( $context ) {
		$page_suffix = add_menu_page(
			null,
			__( 'Экспорт на Avito', 'xml-for-avito' ),
			'manage_woocommerce', 'xfaviexport',
			[ $this, 'get_export_page_func' ],
			'dashicons-redo', 51
		);
		// создаём хук, чтобы стили выводились только на странице настроек
		add_action( 'admin_print_styles-' . $page_suffix, [ $this, 'admin_css_func' ] );

		$page_suffix = add_submenu_page(
			'xfaviexport',
			__( 'Отладка', 'xml-for-avito' ),
			__( 'Страница отладки', 'xml-for-avito' ),
			'manage_woocommerce',
			'xfavidebug',
			[ $this, 'get_debug_page_func' ]
		);
		add_action( 'admin_print_styles-' . $page_suffix, [ $this, 'admin_css_func' ] );

		$page_subsuffix = add_submenu_page(
			'xfaviexport',
			__( 'Добавить расширение', 'xml-for-avito' ),
			__( 'Расширения', 'xml-for-avito' ),
			'manage_woocommerce',
			'xfaviextensions',
			[ $this, 'get_extensions_page_func' ]
		);
		add_action( 'admin_print_styles-' . $page_subsuffix, [ $this, 'admin_css_func' ] );
	}

	/**
	 * Вывод страницы настроек плагина
	 * 
	 * @return void
	 */
	public function get_export_page_func() {
		new XFAVI_Settings_Page();
		return;
	}

	/**
	 * Вывод страницы отладки плагина
	 * 
	 * @return void
	 */
	public function get_debug_page_func() {
		new XFAVI_Debug_Page();
		return;
	}

	/**
	 * Вывод страницы расширений плагина
	 * 
	 * @return void
	 */
	public function get_extensions_page_func() {
		new XFAVI_Extensions_Page();
		return;
	}

	/**
	 * Разрешим загрузку xml и csv файлов
	 * 
	 * @param array $mimes
	 * 
	 * @return array
	 */
	public function add_mime_types( $mimes ) {
		$mimes['csv'] = 'text/csv';
		$mimes['xml'] = 'text/xml';
		return $mimes;
	}

	/**
	 * Add cron intervals to WordPress. Function for `cron_schedules` filter-hook.
	 * 
	 * @param array $new_schedules An array of non-default cron schedules keyed by the schedule name.
	 * 
	 * @return array
	 */
	public function add_cron_intervals( $new_schedules ) {
		$new_schedules['seventy_sec'] = [ 
			'interval' => 70,
			'display' => __( '70 секунд', 'xml-for-avito' )
		];
		$new_schedules['five_min'] = [ 
			'interval' => 300,
			'display' => __( '5 минут', 'xml-for-avito' )
		];
		$new_schedules['six_hours'] = [ 
			'interval' => 21600,
			'display' => __( '6 часов', 'xml-for-avito' )
		];
		$new_schedules['week'] = [ 
			'interval' => 604800,
			'display' => __( '1 неделя', 'xml-for-avito' )
		];
		return $new_schedules;
	}

	/* ----------------- функции крона ----------------- */
	/**
	 * Summary of xfavi_do_this_seventy_sec
	 * 
	 * @param string $feed_id
	 * 
	 * @return void
	 */
	public function xfavi_do_this_seventy_sec( $feed_id = '1' ) {
		new XFAVI_Error_Log(
			'FEED № ' . $feed_id . '; Крон xfavi_do_this_seventy_sec запущен; Файл: xml-for-avito.php; Строка: ' . __LINE__
		);
		$generation = new XFAVI_Generation_XML( $feed_id ); // делаем что-либо каждые 70 сек
		$generation->run();
	}

	/**
	 * Summary of xfavi_do_this_event
	 * 
	 * @param string $feed_id
	 * 
	 * @return void
	 */
	public function xfavi_do_this_event( $feed_id ) {
		new XFAVI_Error_Log( sprintf(
			'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
			$feed_id,
			'Крон yfym_do_this_event включен. Делаем что-то каждый час',
			'xml-for-avito.php',
			__LINE__
		) );
		$step_export = (int) common_option_get( 'xfavi_step_export', false, $feed_id, 'xfavi' );
		if ( $step_export === 0 ) {
			$step_export = 500;
		}
		xfavi_optionUPD( 'xfavi_status_sborki', 1, $feed_id );

		wp_clear_scheduled_hook( 'xfavi_cron_sborki', [ $feed_id ] );

		// wp_schedule_event Возвращает nul|false|WP_Error. null когда планирование завершено. false в случае неудачи.
		$res = wp_schedule_event( time(), 'seventy_sec', 'xfavi_cron_sborki', [ $feed_id ] );
		if ( false === $res ) {
			new XFAVI_Error_Log( 'FEED № ' . $feed_id . '; ERROR: Не удалось запланировань CRON seventy_sec; Файл: xml-for-avito.php; Строка: ' . __LINE__ );
		} else {
			new XFAVI_Error_Log( 'FEED № ' . $feed_id . '; CRON seventy_sec успешно запланирован; Файл: xml-for-avito.php; Строка: ' . __LINE__ );
		}
	}
	/* ----------------- end функции крона ----------------- */

	/**
	 * Вывод различных notices
	 * 
	 * @see https://wpincode.com/kak-dobavit-sobstvennye-uvedomleniya-v-adminke-wordpress/
	 * 
	 * @return void
	 */
	public function notices_prepare() {
		if ( is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) {
			// global $pagenow; https://wpincode.com/kak-dobavit-sobstvennye-uvedomleniya-v-adminke-wordpress/
			if ( isset( $_GET['page'] ) ) {
				printf( '<div class="notice notice-warning"><p>
					<span class="xfavi_bold">W3 Total Cache</span> %1$s. %2$s <a href="%3$s/?%4$s" target="_blank">%5$s</a>
					</p></div>',
					esc_html__( 'плагин активен', 'xml-for-avito' ),
					esc_html__( 'Если XML фид не создается, пожалуйста', 'xml-for-avito' ),
					'https://icopydoc.ru/w3tc-page-cache-meshaet-sozdaniyu-fida-reshenie',
					'utm_source=xml-for-avito&utm_medium=organic&utm_campaign=in-plugin-xml-for-avito&utm_content=notice&utm_term=w3-total-cache',
					esc_html__( 'прочтите это руководство', 'xml-for-avito' )
				);
			}
		}

		$xfavi_disable_notices = univ_option_get( 'xfavi_disable_notices' );
		if ( $xfavi_disable_notices !== 'on' ) {
			$xfavi_settings_arr = univ_option_get( 'xfavi_settings_arr' );
			$xfavi_settings_arr_keys_arr = array_keys( $xfavi_settings_arr );
			for ( $i = 0; $i < count( $xfavi_settings_arr_keys_arr ); $i++ ) {
				$feed_id = $xfavi_settings_arr_keys_arr[ $i ];
				$status_sborki = xfavi_optionGET( 'xfavi_status_sborki', $feed_id );
				if ( $status_sborki == false ) {
					continue;
				} else {
					$status_sborki = (int) $status_sborki;
				}
				if ( $status_sborki !== -1 ) {
					$count_posts = wp_count_posts( 'product' );
					$vsegotovarov = $count_posts->publish;
					$step_export = (int) common_option_get( 'xfavi_step_export', false, $feed_id, 'xfavi' );
					if ( $step_export === 0 ) {
						$step_export = 500;
					}
					// $vobrabotke = $status_sborki-$step_export;

					$vobrabotke = ( ( $status_sborki - 1 ) * $step_export ) - $step_export;

					if ( $vsegotovarov > $vobrabotke ) {
						if ( $status_sborki == 1 ) {
							$vyvod = sprintf(
								'<br />FEED № %1$s %2$s.<br />%3$s. %4$s (<a href="%5$s/?%6$s" target="_blank">%7$s</a>)',
								(string) $feed_id,
								__( 'Импорт списка категорий', 'xml-for-avito' ),
								__(
									'Если индикаторы прогресса не изменились в течение 20 минут, попробуйте уменьшить "Шаг экспорта" в настройках плагина',
									'xml-for-avito'
								),
								__( 'Также убедитесь, что на вашем сайте нет проблем с CRON', 'xml-for-avito' ),
								'https://icopydoc.ru/minimalnye-trebovaniya-dlya-raboty-xml-for-avito',
								'utm_source=xml-for-avito&utm_medium=organic&utm_campaign=in-plugin-xml-for-avito&utm_content=notice&utm_term=check_problems_cron',
								__( 'прочтите это руководство', 'xml-for-avito' )
							);
						}
						if ( $status_sborki == 2 ) {
							$vyvod = sprintf( '<br />FEED № %1$s %2$s',
								(string) $feed_id,
								__( 'Подсчет количества товаров', 'xml-for-avito' )
							);
						}
						if ( $status_sborki > 2 ) {
							$vyvod = sprintf( '<br />FEED № %1$s %2$s %3$s %4$s %5$s. %6$s %7$s %8$s (<a href="%9$s/?%10$s" target="_blank">%11$s</a>)',
								(string) $feed_id,
								__( 'Прогресс', 'xml-for-avito' ),
								$vobrabotke,
								__( 'из', 'xml-for-avito' ),
								$vsegotovarov,
								__( 'товаров', 'xml-for-avito' ),
								__(
									'Если индикаторы прогресса не изменились в течение 20 минут, попробуйте уменьшить "Шаг экспорта" в настройках плагина',
									'xml-for-avito'
								),
								__(
									'Также убедитесь, что на вашем сайте нет проблем с CRON',
									'xml-for-avito'
								),
								'https://icopydoc.ru/minimalnye-trebovaniya-dlya-raboty-xml-for-avito',
								'utm_source=xml-for-avito&utm_medium=organic&utm_campaign=in-plugin-xml-for-avito&utm_content=notice&utm_term=check_problems_cron',
								__( 'прочтите это руководство', 'xml-for-avito' )
							);
						}
					} else {
						$vyvod = sprintf( '<br />FEED № %1$s %2$s',
							(string) $feed_id,
							__( 'До завершения менее 70 секунд', 'xml-for-avito' )
						);
					}

					printf( '<div class="updated notice notice-success is-dismissible">
						<p><span class="xfavi_bold">XML4AVITO:</span>  %1$s %2$s</p>
						</div>',
						esc_html__(
							'Идет автоматическое создание файла. XML-фид в скором времени будет создан',
							'xml-for-avito'
						),
						wp_kses( $vyvod, self::ALLOWED_HTML_ARR )
					);
				}
			}
		}
	}

	/**
	 * Add additional information to the feedback email. 
	 * Function for `xfavi_f_feedback_additional_info` action-hook.
	 * 
	 * @param string $additional_info
	 * 
	 * @return string
	 */
	public function feedback_additional_info( $additional_info ) {
		$settings_arr = univ_option_get( 'xfavi_settings_arr', [] );
		$settings_arr_keys_arr = array_keys( $settings_arr );
		for ( $i = 0; $i < count( $settings_arr_keys_arr ); $i++ ) {
			$feed_id = (string) $settings_arr_keys_arr[ $i ];
			$additional_info .= sprintf(
				'<h2>ФИД №%1$s</h2>
				<p>status_sborki: %2$s<br />
				УРЛ: %3$s<br />
				УРЛ XML-фида: %4$s<br />
				Временный файл: %5$s<br />
				Что экспортировать: %6$s<br />
				Автоматическое создание файла: %7$s<br />
				Обновить фид при обновлении карточки товара: %8$s<br />
				Дата начала последней сборки XML: %9$s<br />
				Дата завершения последней сборки XML: %10$s',
				$feed_id,
				common_option_get( 'xfavi_status_sborki', false, $feed_id, 'xfavi' ),
				get_site_url(),
				urldecode( common_option_get( 'xfavi_file_url', false, $feed_id, 'xfavi' ) ),
				urldecode( common_option_get( 'xfavi_file_file', false, $feed_id, 'xfavi' ) ),
				common_option_get( 'xfavi_whot_export', false, $feed_id, 'xfavi' ),
				common_option_get( 'xfavi_status_cron', false, $feed_id, 'xfavi' ),
				common_option_get( 'xfavi_ufup', false, $feed_id, 'xfavi' ),
				common_option_get( 'xfavi_date_sborki', false, $feed_id, 'xfavi' ),
				common_option_get( 'xfavi_date_sborki_end', false, $feed_id, 'xfavi' )
			);
		}
		return $additional_info;
	}

	/**
	 * Print admin notice.
	 * 
	 * @param string $message
	 * @param string $class
	 * 
	 * @return void
	 */
	private function print_admin_notice( $message, $class ) {
		$xfavi_disable_notices = univ_option_get( 'xfavi_disable_notices' );
		if ( $xfavi_disable_notices !== 'on' ) {
			printf( '<div class="notice %1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		}
	}
} /* end class XmlforAvito */