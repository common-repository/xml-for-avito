<?php
/**
 * This class is responsible for the operation of the plugin Debug page.
 *
 * @package                 iCopyDoc Plugins (ICPD)
 * @subpackage              XML for Avito
 * @since                   0.1.0
 * 
 * @version                 2.5.0 (04-10-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param      string       
 *
 * @depends                 classes:    
 *                          traits:     
 *                          methods:    
 *                          functions:  common_option_get
 *                                      common_option_upd
 *                                      xfavi_optionGET
 *                          constants:  XFAVI_PLUGIN_UPLOADS_DIR_PATH
 *                          options:     
 *
 */
defined( 'ABSPATH' ) || exit;

class XFAVI_Debug_Page {
	/**
	 * Prefix
	 * @var string
	 */
	private $pref = 'xfavi';

	/**
	 * Resust of simulation
	 * @var string
	 */
	private $simulation_result = '';

	/**
	 * Report on the results of resust simulation
	 * @var string
	 */
	private $simulation_result_report = '';

	/**
	 * This class is responsible for the operation of the plugin Debug page.
	 * 
	 * @param string $pref
	 */
	public function __construct( $pref = null ) {
		if ( $pref ) {
			$this->pref = $pref;
		}

		$this->init_classes();
		$this->init_hooks();
		$this->listen_submit();
		$this->print_html_settings_page();
	}

	/**
	 * Initialization classes.
	 * 
	 * @return void
	 */
	public function init_classes() {

	}

	/**
	 * Initialization hooks.
	 * 
	 * @return void
	 */
	public function init_hooks() {
		// наш класс, вероятно, вызывается во время срабатывания хука admin_menu.
		// admin_init - следующий в очереди срабатывания, на хуки раньше admin_menu нет смысла вешать
		// add_action('admin_init', [ $this, 'my_func' ], 10, 1);

	}

	/**
	 * Print HTML Settings page.
	 * 
	 * @return void
	 */
	public function print_html_settings_page() {
		$xfavi_keeplogs = xfavi_optionGET( $this->get_input_name_keeplogs() );
		$xfavi_disable_notices = xfavi_optionGET( $this->get_input_name_disable_notices() );
		$view_arr = [ 
			'keeplogs' => $xfavi_keeplogs,
			'disable_notices' => $xfavi_disable_notices,
			'input_name_keeplogs' => $this->get_input_name_keeplogs(),
			'input_name_disable_notices' => $this->get_input_name_disable_notices(),
			'submit_name_clear_logs' => $this->get_submit_name_clear_logs(),
			'nonce_action_debug_page' => $this->get_nonce_action_debug_page(),
			'nonce_field_debug_page' => $this->get_nonce_field_debug_page(),
			'submit_name' => $this->get_submit_name(),
			'simulation_result' => $this->get_simulation_result(),
			'simulation_result_report' => $this->get_simulation_result_report()
		];
		if ( isset( $_POST['xfavi_feed_id'] ) ) {
			$view_arr['feed_id'] = sanitize_text_field( $_POST['xfavi_feed_id'] );
		} else {
			$view_arr['feed_id'] = '1';
		}
		if ( isset( $_POST['xfavi_simulated_post_id'] ) ) {
			$view_arr['simulated_post_id'] = sanitize_text_field( $_POST['xfavi_simulated_post_id'] );
		} else {
			$view_arr['simulated_post_id'] = '';
		}
		include_once __DIR__ . '/views/html-admin-debug-page.php';
	}

	/**
	 * Gets a list of possible problems with this plugin.
	 * 
	 * @return array
	 */
	public static function get_possible_problems_list() {
		$possibleProblems = '';
		$possibleProblemsCount = 0;
		$conflictWithPlugins = 0;
		$conflictWithPluginsList = '';
		$check_global_attr_count = wc_get_attribute_taxonomies();
		if ( count( $check_global_attr_count ) < 1 ) {
			$possibleProblemsCount++;
			$possibleProblems .= sprintf( '<li>%s.<a href="%s/?%s">%s</a>.</li>',
				__(
					'Ваш сайт не имеет глобальных атрибутов! Это может повлиять на качество XML-фида. Это также может вызвать трудности при настройке плагина',
					'xml-for-avito'
				),
				'https://icopydoc.ru/global-and-local-attributes-in-woocommerce',
				'utm_source=xml-for-avito&utm_medium=organic&utm_campaign=in-plugin-xml-for-avito&utm_content=debug-page&utm_term=possible-problems',
				__( 'Пожалуйста, ознакомьтесь с рекомендациями', 'xml-for-avito' )
			);
		}
		if ( is_plugin_active( 'snow-storm/snow-storm.php' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'Snow Storm<br/>';
		}
		if ( is_plugin_active( 'email-subscribers/email-subscribers.php' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'Email Subscribers & Newsletters<br/>';
		}
		if ( is_plugin_active( 'saphali-search-castom-filds/saphali-search-castom-filds.php' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'Email Subscribers & Newsletters<br/>';
		}
		if ( is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'W3 Total Cache<br/>';
		}
		if ( is_plugin_active( 'docket-cache/docket-cache.php' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'Docket Cache<br/>';
		}
		if ( class_exists( 'MPSUM_Updates_Manager' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'Easy Updates Manager<br/>';
		}
		if ( class_exists( 'OS_Disable_WordPress_Updates' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'Disable All WordPress Updates<br/>';
		}
		if ( $conflictWithPlugins > 0 ) {
			$possibleProblemsCount++;
			$possibleProblems .= sprintf( '<li>
				<p>%1$s: XML for Avito</p>
				%2$s
				<p>%3$s: <a href="mailto:%4$s">%4$s</a>.</p>
				</li>',
				__( 'Скорее всего, эти плагины негативно влияют на работу', 'xml-for-avito' ),
				$conflictWithPluginsList,
				__(
					'Если вы разработчик одного из плагинов из списка выше, пожалуйста, свяжитесь со мной',
					'xml-for-avito'
				),
				'mailto:support@icopydoc.ru'
			);
		}
		return [ $possibleProblems, $possibleProblemsCount, $conflictWithPlugins, $conflictWithPluginsList ];
	}

	/**
	 * Get prefix
	 * 
	 * @return mixed|string
	 */
	private function get_pref() {
		return $this->pref;
	}

	/**
	 * Get input name keeplogs option
	 * 
	 * @return string
	 */
	private function get_input_name_keeplogs() {
		return $this->get_pref() . '_keeplogs';
	}

	/**
	 * Summary of get_input_name_disable_notices
	 * 
	 * @return string
	 */
	private function get_input_name_disable_notices() {
		return $this->get_pref() . '_disable_notices';
	}

	/**
	 * Summary of get_submit_name
	 * 
	 * @return string
	 */
	private function get_submit_name() {
		return $this->get_pref() . '_submit_debug_page';
	}

	/**
	 * Summary of get_nonce_action_debug_page
	 * 
	 * @return string
	 */
	private function get_nonce_action_debug_page() {
		return $this->get_pref() . '_nonce_action_debug_page';
	}

	/**
	 * Summary of get_nonce_field_debug_page
	 * 
	 * @return string
	 */
	private function get_nonce_field_debug_page() {
		return $this->get_pref() . '_nonce_field_debug_page';
	}

	/**
	 * Summary of get_submit_name_clear_logs
	 * 
	 * @return string
	 */
	private function get_submit_name_clear_logs() {
		return $this->get_pref() . '_submit_clear_logs';
	}

	/**
	 * Summary of get_simulation_result
	 * 
	 * @return string
	 */
	private function get_simulation_result() {
		return $this->simulation_result;
	}

	/**
	 * Summary of get_simulation_result_report
	 * 
	 * @return string
	 */
	private function get_simulation_result_report() {
		return $this->simulation_result_report;
	}

	/**
	 * Summary of listen_submit
	 * 
	 * @return void
	 */
	private function listen_submit() {
		if ( isset( $_REQUEST[ $this->get_submit_name()] ) ) {
			$this->save_data();
			$message = __( 'Обновлено', 'xml-for-avito' );
			$class = 'notice-success';

			add_action( 'my_admin_notices', function () use ($message, $class) {
				$this->admin_notices_func( $message, $class );
			}, 10, 2 );
		}

		if ( isset( $_REQUEST[ $this->get_submit_name_clear_logs()] ) ) {
			$filename = XFAVI_PLUGIN_UPLOADS_DIR_PATH . '/xml-for-avito.log';
			if ( file_exists( $filename ) ) {
				$res = unlink( $filename );
			} else {
				$res = false;
			}
			if ( true == $res ) {
				$message = __( 'Логи были очищены', 'xml-for-avito' );
				$class = 'notice-success';
			} else {
				$message = __( 'Ошибка доступа к log-файлу. Возможно log-файл был удален ранее', 'xml-for-avito' );
				$class = 'notice-warning';
			}

			add_action( 'my_admin_notices', function () use ($message, $class) {
				$this->admin_notices_func( $message, $class );
			}, 10, 2 );
		}


		if ( isset( $_POST['xfavi_feed_id'] ) ) {
			$xfavi_feed_id = sanitize_text_field( $_POST['xfavi_feed_id'] );
		} else {
			$xfavi_feed_id = '1';
		}
		if ( isset( $_POST['xfavi_simulated_post_id'] ) ) {
			$xfavi_simulated_post_id = sanitize_text_field( $_POST['xfavi_simulated_post_id'] );
		} else {
			$xfavi_simulated_post_id = '';
		}
		if ( isset( $_REQUEST['xfavi_submit_simulated'] ) ) {
			if ( ! empty( $_POST )
				&& check_admin_referer( 'xfavi_nonce_action_simulated', 'xfavi_nonce_field_simulated' ) ) {
				$post_id = (int) $xfavi_simulated_post_id;

				$result_get_unit_obj = new XFAVI_Get_Unit( $post_id, $xfavi_feed_id );
				$simulated_result_xml = $result_get_unit_obj->get_result();
				$simulated_result_xml .= '----- Остатки -----' . PHP_EOL;
				$simulated_result_xml .= $result_get_unit_obj->get_stock_xml();

				$resust_report_arr = $result_get_unit_obj->get_skip_reasons_arr();

				if ( empty( $resust_report_arr ) ) {
					$this->simulation_result_report = 'Всё штатно';
					$simulated_result_xml = '<Ads>' . PHP_EOL . $simulated_result_xml . '</Ads>' . PHP_EOL;
				} else {
					$simulation_result_report = '';
					foreach ( $result_get_unit_obj->get_skip_reasons_arr() as $value ) {
						$simulation_result_report .= $value . PHP_EOL;
					}
					$this->simulation_result_report = $simulation_result_report;
					unset( $simulation_result_report );
				}
				$this->simulation_result = $simulated_result_xml;
			}
		}

		return;
	}

	/**
	 * Save plugin data (only the debugging page settings are saved).
	 * 
	 * @return void
	 */
	private function save_data() {
		if ( ! empty( $_POST )
			&& check_admin_referer( $this->get_nonce_action_debug_page(), $this->get_nonce_field_debug_page() ) ) {
			if ( isset( $_POST[ $this->get_input_name_keeplogs()] ) ) {
				$keeplogs = sanitize_text_field( $_POST[ $this->get_input_name_keeplogs()] );
			} else {
				$keeplogs = '';
			}
			if ( isset( $_POST[ $this->get_input_name_disable_notices()] ) ) {
				$disable_notices = sanitize_text_field( $_POST[ $this->get_input_name_disable_notices()] );
			} else {
				$disable_notices = '';
			}
			if ( is_multisite() ) {
				update_blog_option( get_current_blog_id(), 'xfavi_keeplogs', $keeplogs );
				update_blog_option( get_current_blog_id(), 'xfavi_disable_notices', $disable_notices );
			} else {
				update_option( 'xfavi_keeplogs', $keeplogs );
				update_option( 'xfavi_disable_notices', $disable_notices );
			}
		}
	}

	/**
	 * Print admin notice.
	 * 
	 * @param string $message
	 * @param string $class
	 * 
	 * @return void
	 */
	private function admin_notices_func( $message, $class ) {
		printf( '<div class="notice %1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}
}