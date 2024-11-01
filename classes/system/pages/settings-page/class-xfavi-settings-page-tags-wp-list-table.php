<?php
/**
 * The this class manages the list of feeds
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   3.9.0
 * 
 * @version                 2.4.9 (18-09-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     https://2web-master.ru/wp_list_table-%E2%80%93-poshagovoe-rukovodstvo.html 
 *                          https://wp-kama.ru/function/wp_list_table
 * 
 * @param        
 *
 * @depends                 classes:    WP_List_Table
 *                                      XFAVI_Data_Arr
 *                          traits:     
 *                          methods:    
 *                          functions:  common_option_get
 *                                      xfavi_optionGET
 *                          constants:  
 *                          options:    
 */
defined( 'ABSPATH' ) || exit;

class XFAVI_Settings_Page_Tags_WP_List_Table extends WP_List_Table {
	private $feed_id;
	private $rules;

	function __construct( $feed_id ) {
		$this->feed_id = (string) $feed_id;
		$this->rules = common_option_get( 'xfavi_xml_rules', false, $feed_id, 'xfavi' );

		global $status, $page;
		parent::__construct( [ 
			'plural' => '', // По умолчанию: '' ($this->screen->base);
			// Название для множественного числа, используется во всяких 
			// заголовках, например в css классах, в заметках, например 'posts', тогда 'posts' будет добавлен в 
			// класс table.

			'singular' => '', // По умолчанию: ''; 
			// Название для единственного числа, например 'post'.

			'ajax' => false, // По умолчанию: false; 
			// Должна ли поддерживать таблица AJAX. Если true, класс будет вызывать метод 
			// _js_vars() в подвале, чтобы передать нужные переменные любому скрипту обрабатывающему AJAX события.

			'screen' => null, // По умолчанию: null; 
			// Строка содержащая название хука, нужного для определения текущей страницы. 
			// Если null, то будет установлен текущий экран. 
		] );
	}

	/**
	 * 	Метод get_columns() необходим для маркировки столбцов внизу и вверху таблицы. 
	 *	Ключи в массиве должны быть теми же, что и в массиве данных, 
	 *	иначе соответствующие столбцы не будут отображены.
	 */
	function get_columns() {
		$columns = [ 
			'xfavi_attr_name' => __( 'Атрибут', 'xml-for-avito' ),
			'xfavi_attr_desc' => __( 'Описание атрибута', 'xml-for-avito' ),
			'xfavi_attr_val' => __( 'Источник значения', 'xml-for-avito' ),
			'xfavi_def_val' => __( 'Значение по умолчанию', 'xml-for-avito' ),
		];
		return $columns;
	}

	private function attr_name_mask( $desc, $tag, $rules_arr = [] ) {
		$color = 'black';
		if ( ! empty( $tag ) ) {
			$tag = '[' . $tag . ']';
		}
		return sprintf( '<span class="xfavi_bold" style="color: %3$s;">%1$s</span><br/>%2$s',
			$desc,
			$tag,
			$color
		);
	}

	/**
	 *	Метод вытаскивает из БД данные, которые будут лежать в таблице
	 *	$this->table_data();
	 */
	private function table_data() {
		$result_arr = [];

		$data_arr_obj = new XFAVI_Data_Arr();
		$attr_arr = $data_arr_obj->get_data_for_tabs( 'wp_list_table' );

		for ( $i = 0; $i < count( $attr_arr ); $i++ ) {
			if ( $attr_arr[ $i ]['tab'] === 'wp_list_table' ) {
				$r_arr = [];
				$r_arr['xfavi_attr_name'] = $this->attr_name_mask(
					$attr_arr[ $i ]['data']['label'],
					$attr_arr[ $i ]['data']['tag_name'],
					$attr_arr[ $i ]['data']['rules']
				);
				$r_arr['xfavi_attr_desc'] = $attr_arr[ $i ]['data']['desc'];

				if ( $attr_arr[ $i ]['type'] === 'select' ) {
					$attr_val = $this->get_view_html_field_select( $attr_arr[ $i ] );
				} else if ( $attr_arr[ $i ]['type'] === 'text' ) {
					$attr_val = $this->get_view_html_field_input( $attr_arr[ $i ] );
				}
				$r_arr['xfavi_attr_val'] = $attr_val;

				if ( true === $attr_arr[ $i ]['data']['default_value'] ) {
					$i++;
					if ( $attr_arr[ $i ]['type'] === 'text' ) {
						$r_arr['xfavi_def_val'] = $this->get_view_html_field_input( $attr_arr[ $i ] );
					}
				} else {
					$r_arr['xfavi_def_val'] = __( 'Нет настроек по умолчанию', 'xml-for-avito' );
				}

				$result_arr[] = $r_arr;
				unset( $r_arr );
			}
		}

		return $result_arr;
	}

	private function get_view_html_field_input( $data_arr ) {
		return sprintf( '<input 
					type="text" 
					name="%1$s" 
					id="%1$s" 
					value="%2$s"
					placeholder="%3$s" /><br />',
			esc_attr( $data_arr['opt_name'] ),
			esc_attr( common_option_get( $data_arr['opt_name'], false, $this->get_feed_id(), 'xfavi' ) ),
			esc_html( $data_arr['data']['placeholder'] )
		);
	}

	/**
	 * @param array $data_arr
	 * 
	 * @return string
	 */
	private function get_view_html_field_select( $data_arr ) {
		if ( isset( $data_arr['data']['key_value_arr'] ) ) {
			$key_value_arr = $data_arr['data']['key_value_arr'];
		} else {
			$key_value_arr = [];
		}

		if ( isset( $data_arr['data']['brands'] ) ) {
			$brands = $data_arr['data']['brands'];
		} else {
			$brands = false;
		}

		return sprintf( '<select name="%1$s" id="%1$s" />%2$s</select>',
			esc_attr( $data_arr['opt_name'] ),
			$this->print_view_html_option_for_select(
				common_option_get(
					$data_arr['opt_name'],
					false,
					$this->get_feed_id(),
					'xfavi'
				),
				false,
				[ 
					'woo_attr' => $data_arr['data']['woo_attr'],
					'key_value_arr' => $key_value_arr,
					'brands' => $brands
				]
			)
		);
	}

	private function print_view_html_option_for_select( $opt_value, $opt_name = false, $params_arr = [], $res = '' ) {
		if ( ! empty( $params_arr['key_value_arr'] ) ) {
			for ( $i = 0; $i < count( $params_arr['key_value_arr'] ); $i++ ) {
				$res .= sprintf( '<option value="%1$s" %2$s>%3$s</option>' . PHP_EOL,
					esc_attr( $params_arr['key_value_arr'][ $i ]['value'] ),
					esc_attr( selected( $opt_value, $params_arr['key_value_arr'][ $i ]['value'], false ) ),
					esc_attr( $params_arr['key_value_arr'][ $i ]['text'] )
				);
			}
		}

		if ( isset( $params_arr['brands'] ) && ( true == $params_arr['brands'] ) ) {
			if ( is_plugin_active( 'perfect-woocommerce-brands/perfect-woocommerce-brands.php' )
				|| is_plugin_active( 'perfect-woocommerce-brands/main.php' )
				|| class_exists( 'Perfect_Woocommerce_Brands' ) ) {
				$res .= sprintf( '<option value="sfpwb" %s>%s Perfect Woocommerce Brands</option>',
					selected( $opt_value, 'sfpwb', false ),
					__( 'Подставлять из', 'xml-for-avito' )
				);
			}
			if ( is_plugin_active( 'premmerce-woocommerce-brands/premmerce-brands.php' ) ) {
				$res .= sprintf( '<option value="premmercebrandsplugin" %s>%s %s</option>',
					selected( $opt_value, 'premmercebrandsplugin', false ),
					__( 'Подставлять из', 'xml-for-avito' ),
					'Premmerce Brands for WooCommerce'
				);
			}
			if ( is_plugin_active( 'woocommerce-brands/woocommerce-brands.php' ) ) {
				$res .= sprintf( '<option value="woocommerce_brands" %s>%s %s</option>',
					selected( $opt_value, 'woocommerce_brands', false ),
					__( 'Подставлять из', 'xml-for-avito' ),
					'WooCommerce Brands'
				);
			}
			if ( class_exists( 'woo_brands' ) ) {
				$res .= sprintf( '<option value="woo_brands" %s>%s %s</option>',
					selected( $opt_value, 'woo_brands', false ),
					__( 'Подставлять из', 'xml-for-avito' ),
					'Woocomerce Brands Pro'
				);
			}
			if ( is_plugin_active( 'yith-woocommerce-brands-add-on/init.php' )
				|| is_plugin_active( 'perfect-woocommerce-brands/main.php' )
				|| class_exists( 'Perfect_Woocommerce_Brands' ) ) {
				$res .= sprintf( '<option value="yith_woocommerce_brands_add_on" %s>%s %s</option>',
					selected( $opt_value, 'yith_woocommerce_brands_add_on', false ),
					__( 'Подставлять из', 'xml-for-avito' ),
					'YITH WooCommerce Brands Add-On'
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
		return $res;
	}

	/**
	 *	prepare_items определяет два массива, управляющие работой таблицы:
	 *	$hidden - определяет скрытые столбцы 
	 *			(https://2web-master.ru/wp_list_table-%E2%80%93-poshagovoe-rukovodstvo.html#screen-options)
	 *	$sortable - определяет, может ли таблица быть отсортирована по этому столбцу
	 */
	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = [];
		$sortable = $this->get_sortable_columns(); // вызов сортировки
		$this->_column_headers = [ $columns, $hidden, $sortable ];
		// блок пагинации пропущен
		$this->items = $this->table_data();
	}

	/** 
	 * 	Данные таблицы.
	 *	Наконец, метод назначает данные из примера на переменную представления данных класса — items.
	 *	Прежде чем отобразить каждый столбец, WordPress ищет методы типа column_{key_name}, например,
	 *	function column_xfavi_url_xml_file. 
	 *	Такой метод должен быть указан для каждого столбца. Но чтобы не создавать эти методы для всех столбцов
	 *	в отдельности, можно использовать column_default. Эта функция обработает все столбцы, для которых не определён
	 *	специальный метод.
	 */
	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'xfavi_attr_name':
			case 'xfavi_attr_desc':
			case 'xfavi_attr_val':
			case 'xfavi_def_val':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); // Мы отображаем целый массив во избежание проблем
		}
	}

	/**
	 * Get feed ID
	 * 
	 * @return string
	 */
	private function get_feed_id() {
		return $this->feed_id;
	}
}