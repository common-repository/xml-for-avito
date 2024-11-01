<?php
/**
 * The abstract class for getting the XML-code or skip reasons
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.3.0 (04-04-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @param       array
 *
 * @depends                 classes:    XFAVI_Error_Log
 *                          traits:     XFAVI_T_Get_Feed_Id
 *                                      XFAVI_T_Get_Product
 *                                      XFAVI_T_Get_Skip_Reasons_Arr
 *                          methods:    
 *                          functions:  
 *                          constants:  
 *                          actions:    
 *                          filters:    
 */
defined( 'ABSPATH' ) || exit;

abstract class XFAVI_Get_Unit_Offer {
	use XFAVI_T_Get_Feed_Id;
	use XFAVI_T_Get_Product;
	use XFAVI_T_Get_Skip_Reasons_Arr;

	/**
	 * Имя категории на Авито
	 * @var string
	 */
	public $feed_category_avito_name;
	/**
	 * Цена в фиде
	 * @var string // ? возможно тут float
	 */
	public $feed_price;
	/**
	 * Массив, который пришёл в класс. Этот массив используется в фильтрах трейтов
	 * @var array
	 */
	protected $input_data_arr;

	/**
	 * Product variation object
	 * @var WC_Product_Variation
	 */
	protected $offer = null;
	/**
	 * Product variation array
	 * @var array
	 */
	protected $variations_arr = null;
	/**
	 * Result product XML
	 * @var string
	 */
	protected $result_product_xml;
	/**
	 * Flag `do_empty_product_xml`
	 * @var bool
	 */
	protected $do_empty_product_xml = false;

	/**
	 * Product duplicate number
	 * @var int
	 */
	protected $duplicate_number;

	/**
	 * The class for getting the XML-code or skip reasons
	 * 
	 * @param array $args_arr [
	 *    'feed_id'            - string - Required,
	 *    'product'            - object - Required,
	 *    'offer'              - object - Optional,
	 *    'variation_count'    - int - Optional
	 * ]
	 */
	public function __construct( $args_arr ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php'; // без этого не будет работать вне адмники is_plugin_active

		$this->input_data_arr = $args_arr;
		$this->feed_id = $args_arr['feed_id'];
		$this->product = $args_arr['product'];

		if ( isset( $args_arr['offer'] ) ) {
			$this->offer = $args_arr['offer'];
		}
		if ( isset( $args_arr['variation_count'] ) ) {
			$this->variation_count = $args_arr['variation_count'];
		}

		$r = $this->generation_product_xml();

		// если нет нужды пропускать
		if ( empty( $this->get_skip_reasons_arr() ) ) {
			$this->result_product_xml = $r;
		} else {
			// !!! - тут нужно ещё раз подумать и проверить
			// с простыми товарами всё чётко
			$this->result_product_xml = '';
			if ( null == $this->get_offer() ) { // если прстой товар - всё чётко
				$this->set_do_empty_product_xml( true );
			} else {
				// если у нас вариативный товар, то как быть, если все вариации пропущены
				// мы то возвращаем false (см ниже), возможно надо ещё вести учёт вариций
				// также см функцию set_result() в классе class-xfavi-get-unit.php
				$this->set_do_empty_product_xml( false );
			}
		}
	}

	abstract public function generation_product_xml();

	/**
	 * Summary of get_product_xml
	 * 
	 * @return string
	 */
	public function get_product_xml() {
		return $this->result_product_xml;
	}

	/**
	 * Set `do_empty_product_xml` flag
	 * @param bool $v
	 * 
	 * @return void
	 */
	public function set_do_empty_product_xml( bool $v ) {
		$this->do_empty_product_xml = $v;
	}

	/**
	 * Set duplicate number
	 * 
	 * @param int $duplicate_number
	 * 
	 * @return void
	 */
	public function set_duplicate_number( $duplicate_number ) {
		$this->duplicate_number = $duplicate_number;
	}

	/**
	 * Get `do_empty_product_xml` flag
	 * 
	 * @return bool
	 */
	public function get_do_empty_product_xml() {
		return $this->do_empty_product_xml;
	}

	/**
	 * Get the name of the Avito category in the feed
	 * 
	 * @return string
	 */
	public function get_feed_category_avito_name() {
		return $this->feed_category_avito_name;
	}

	/**
	 * Get product price in the feed
	 * 
	 * @return string
	 */
	public function get_feed_price() {
		return $this->feed_price;
	}

	/**
	 * Add skip reason
	 * 
	 * @param mixed $reason
	 * 
	 * @return void
	 */
	protected function add_skip_reason( $reason ) {
		if ( isset( $reason['offer_id'] ) ) {
			$reason_string = sprintf(
				'FEED № %1$s; Вариация товара (postId = %2$s, offer_id = %3$s) пропущена. Причина: %4$s; Файл: %5$s; Строка: %6$s',
				$this->feed_id, $reason['post_id'], $reason['offer_id'], $reason['reason'], $reason['file'], $reason['line']
			);
		} else {
			$reason_string = sprintf(
				'FEED № %1$s; Товар с postId = %2$s пропущен. Причина: %3$s; Файл: %4$s; Строка: %5$s',
				$this->feed_id, $reason['post_id'], $reason['reason'], $reason['file'], $reason['line']
			);
		}
		$this->set_skip_reasons_arr( $reason_string );
		new XFAVI_Error_Log( $reason_string );
	}

	/**
	 * Возвращает массив, который пришёл в класс. Этот массив используется в фильтрах трейтов
	 * 
	 * @return array
	 */
	protected function get_input_data_arr() {
		return $this->input_data_arr;
	}

	/**
	 * Get offer
	 * 
	 * @return WC_Product_Variation
	 */
	protected function get_offer() {
		return $this->offer;
	}

	/**
	 * Get duplicate number
	 * 
	 * @return int
	 */
	protected function get_duplicate_number() {
		return $this->duplicate_number;
	}
}