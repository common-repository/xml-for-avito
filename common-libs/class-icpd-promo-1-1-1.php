<?php
/**
 * This class is responsible for the output of the promo
 *
 * @package                 iCopyDoc Plugins (ICPD)
 * @subpackage              RUS
 * @since                   0.1.0
 * 
 * @version                 1.1.1 (03-10-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param     string	
 *
 * @depends                 classes:    
 *                          traits:     
 *                          methods:    
 *                          functions:  
 *                          constants:  
 *                          actions:    print_view_html_icpd_my_plugins_list
 *                          filters:    icpd_f_plugins_arr
 */
defined( 'ABSPATH' ) || exit;

// 'xml-for-avito' - slug for translation (be sure to make an autocorrect)
if ( ! class_exists( 'ICPD_Promo' ) ) {
	final class ICPD_Promo {
		/**
		 * Prefix
		 * @var string
		 */
		private $pref = '';
		/**
		 * Plugins list
		 * @var array
		 */
		private $plugins_arr;

		/**
		 * This class is responsible for the output of the promo
		 */
		public function __construct( $pref = '' ) {
			$this->pref = $pref;
			$plugins_arr = [ 
				[ 
					'name' => 'XML for Google Merchant Center',
					'desc' => __( 'Создает XML-фид для загрузки в Google Merchant Center', 'xml-for-avito' ),
					'url' => 'https://wordpress.org/plugins/xml-for-google-merchant-center/'
				],
				[ 
					'name' => 'YML for Yandex Market',
					'desc' => __(
						'Создает YML-фид для импорта ваших товаров на Яндекс Маркет',
						'xml-for-avito'
					),
					'url' => 'https://wordpress.org/plugins/xml-for-avito/'
				],
				[ 
					'name' => 'Import from YML',
					'desc' => __( 'Импортирует товары из YML в ваш магазин', 'xml-for-avito' ),
					'url' => 'https://wordpress.org/plugins/import-from-yml/'
				],
				[ 
					'name' => 'Import Products to Yandex',
					'desc' => __(
						'Импортирует товары на Яндекс Маркет из вашего интернет-магазина на Woocommerce с помощью API',
						'xml-for-avito'
					),
					'url' => 'https://wordpress.org/plugins/wc-import-yandex/'
				],
				[ 
					'name' => 'Integrate myTarget for WooCommerce',
					'desc' => __(
						'Этот плагин помогает настроить счетчик myTarget для динамического ремаркетинга для WooCommerce',
						'xml-for-avito'
					),
					'url' => 'https://wordpress.org/plugins/wc-mytarget/'
				],
				[ 
					'name' => 'XML for Hotline',
					'desc' => __( 'Создает XML-фид для импорта ваших товаров на Hotline', 'xml-for-avito' ),
					'url' => 'https://wordpress.org/plugins/xml-for-hotline/'
				],
				[ 
					'name' => 'Gift upon purchase for WooCommerce',
					'desc' => __(
						'Этот плагин добавит маркетинговый инструмент, который позволит вам дарить подарки покупателю при покупке',
						'xml-for-avito'
					),
					'url' => 'https://wordpress.org/plugins/gift-upon-purchase-for-woocommerce/'
				],
				[ 
					'name' => 'Import Products to OK.ru',
					'desc' => __(
						'С помощью этого плагина вы можете импортировать товары в свою группу на ok.ru',
						'xml-for-avito'
					),
					'url' => 'https://wordpress.org/plugins/import-products-to-ok-ru/'
				],
				[ 
					'name' => 'Import Products to OZON',
					'desc' => __(
						'С помощью этого плагина вы можете импортировать товары на OZON',
						'xml-for-avito'
					),
					'url' => 'https://wordpress.org/plugins/xml-for-avito/'
				],
				[ 
					'name' => 'Import Products to VK.com',
					'desc' => __(
						'С помощью этого плагина вы можете импортировать товары в свою группу в VK.com',
						'xml-for-avito'
					),
					'url' => 'https://wordpress.org/plugins/xml-for-avito/'
				],
				[ 
					'name' => 'XML for Avito',
					'desc' => __( 'Создает XML-фид для импорта ваших товаров на Avito', 'xml-for-avito' ),
					'url' => 'https://wordpress.org/plugins/xml-for-avito/'
				],
				[ 
					'name' => 'XML for O.Yandex (Яндекс Объявления)',
					'desc' => __( 'Создает XML-фид для импорта ваших товаров на Яндекс.Объявления.', 'xml-for-avito' ),
					'url' => 'https://wordpress.org/plugins/xml-for-o-yandex/'
				]
			];
			$plugins_arr = apply_filters( 'icpd_f_plugins_arr', $plugins_arr );
			$this->plugins_arr = $plugins_arr;
			unset( $plugins_arr );
			$this->init_hooks();
		}

		/**
		 * Initialization hooks
		 * 
		 * @return void
		 */
		public function init_hooks() {
			add_action( 'admin_print_footer_scripts', [ $this, 'print_css_styles' ] );
			add_action( 'print_view_html_icpd_my_plugins_list', [ $this, 'print_view_html_plugins_list_block' ], 10, 1 );
		}

		/**
		 * Print css styles. Function for `admin_print_footer_scripts` filter-hook.
		 * 
		 * @return void
		 */
		public function print_css_styles() {
			print ( '<style>.clear{clear: both;} .icpd_bold {font-weight: 700;}</style>' );
		}

		/**
		 * Print plugins list block. Function for `print_view_html_icpd_my_plugins_list` filter-hook.
		 * 
		 * @param string $pref
		 * 
		 * @return void
		 */
		public function print_view_html_plugins_list_block( $pref ) {
			if ( $pref !== $this->get_pref() ) {
				return;
			}
			?>
			<div class="clear"></div>
			<div class="metabox-holder">
				<div class="postbox">
					<h2 class="hndle">
						<?php esc_html_e( 'Мои плагины, которые могут вас заинтересовать', 'xml-for-avito' ); ?>
					</h2>
					<div class="inside">
						<?php
						for ( $i = 0; $i < count( $this->plugins_arr ); $i++ ) {
							$this->print_view_html_plugins_list_item( $this->plugins_arr[ $i ] );
						}
						?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Print item of plugins list block
		 * 
		 * @param array $data_arr
		 * 
		 * @return void
		 */
		private function print_view_html_plugins_list_item( $data_arr ) {
			printf( '<p><span class="icpd_bold">%1$s</span> - %2$s. <a href="%3$s" target="_blank">%4$s</a>.</p>%5$s',
				esc_html( $data_arr['name'] ),
				esc_html( $data_arr['desc'] ),
				esc_attr( $data_arr['url'] ),
				esc_html__( 'Подробнее', 'xml-for-avito' ),
				PHP_EOL
			);
		}

		/**
		 * Get prefix
		 * 
		 * @return string
		 */
		private function get_pref() {
			return $this->pref;
		}
	} // end final class ICPD_Promo
} // end if (!class_exists('ICPD_Promo'))