<?php
/**
 * The main class for getting the XML-code of the product 
 *
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.3.1 (05-04-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @param       string      $post_id - Required
 * @param       string      $feed_id - Required
 *
 * @depends                 classes:    WC_Product_Variation
 *                                      XFAVI_Get_Unit_Offer
 *                                      (XFAVI_Get_Unit_Offer_Simple)
 *                                      (XFAVI_Get_Unit_Offer_Varible)
 *                          traits:     XFAVI_T_Get_Post_Id
 *                                      XFAVI_T_Get_Feed_Id;
 *                                      XFAVI_T_Get_Product
 *                                      XFAVI_T_Get_Skip_Reasons_Arr
 *                          methods:    
 *                          functions:  common_option_get
 *                          constants:  
 *                          actions:    
 *                          filters:    
 */
defined( 'ABSPATH' ) || exit;

class XFAVI_Get_Unit {
	use XFAVI_T_Get_Post_Id;
	use XFAVI_T_Get_Feed_Id;
	use XFAVI_T_Get_Product;
	use XFAVI_T_Get_Skip_Reasons_Arr;

	/**
	 * Result XML code
	 * @var string
	 */
	protected $result_xml;

	/**
	 * Result stock XML code
	 * @var string
	 */
	protected $result_stock_xml;

	/**
	 * Product IDs in xml feed
	 * @var string
	 */
	protected $ids_in_xml = '';

	/**
	 * The main class for getting the XML-code of the product
	 * 
	 * @param mixed $post_id
	 * @param string $feed_id
	 */
	public function __construct( $post_id, $feed_id ) {
		$this->post_id = $post_id;
		$this->feed_id = $feed_id;

		$args_arr = [ 'post_id' => $post_id, 'feed_id' => $feed_id ];

		do_action( 'before_wc_get_product', $args_arr );

		$product = wc_get_product( $post_id );

		do_action( 'after_wc_get_product', $args_arr, $product );
		$this->product = $product;
		do_action( 'after_wc_get_product_this_product', $args_arr, $product );

		$this->create_code(); // создаём код одного простого или вариативного товара и заносим в $result_xml
	}

	/**
	 * Get result XML code
	 * 
	 * @return string
	 */
	public function get_result() {
		return $this->result_xml;
	}

	/**
	 * Get result stock XML code
	 * 
	 * @return string
	 */
	public function get_stock_xml() {
		return $this->result_stock_xml;
	}

	/**
	 * Get product IDs in xml feed
	 * 
	 * @return string
	 */
	public function get_ids_in_xml() {
		return $this->ids_in_xml;
	}

	/**
	 * Creates the XML code of the product
	 * 
	 * @return string
	 */
	protected function create_code() {
		if ( null == $this->get_product() ) {
			$this->result_xml = '';
			array_push( $this->skip_reasons_arr, __( 'Нет товара с таким ID', 'xml-for-avito' ) );
			return $this->get_result();
		}

		if ( $this->get_product()->is_type( 'variable' ) ) {
			$variations_arr = $this->get_product()->get_available_variations();
			$variation_count = count( $variations_arr );
			for ( $i = 0; $i < $variation_count; $i++ ) {
				$offer_id = $variations_arr[ $i ]['variation_id'];
				$offer = new WC_Product_Variation( $offer_id ); // получим вариацию

				$args_arr = [ 
					'feed_id' => $this->get_feed_id(),
					'product' => $this->get_product(),
					'offer' => $offer,
					'variation_count' => $variation_count
				];

				$offer_variable_obj = new XFAVI_Get_Unit_Offer_Variable( $args_arr );
				$r = $this->set_result( $offer_variable_obj );
				if ( true === $r ) {
					$this->ids_in_xml .= sprintf( '%s;%s;%s;%s;%s',
						$this->get_product()->get_id(),
						$this->get_product()->get_id(),
						$offer_variable_obj->get_feed_price(),
						$offer_variable_obj->get_feed_category_id(),
						PHP_EOL
					);
					$this->result_stock_xml = $offer_variable_obj->get_xml_stock_item();
				}

				$one_variable = common_option_get( 'xfavi_one_variable', false, $this->get_feed_id(), 'xfavi' );
				if ( $one_variable == 'on' ) {
					break;
				}

				$stop_flag = false;
				$stop_flag = apply_filters(
					'xfavi_f_after_variable_offer_stop_flag',
					$stop_flag,
					[ 
						'i' => $i,
						'variation_count' => $variation_count,
						'product' => $this->get_product(),
						'offer' => $offer
					],
					$this->get_feed_id()
				);

				// TODO: потенциально удалить фильтр...
				$n = '';
				$special_data_for_flag = '';
				$stop_flag = apply_filters(
					'xfavi_after_variable_offer_stop_flag',
					$stop_flag, $i, $n, $variation_count, $offer->get_id(), $offer, $special_data_for_flag, $this->feed_id
				);
				// TODO: потенциально удалить фильтр...
				if ( true == $stop_flag ) {
					break;
				}
			}
		} else {
			$args_arr = [ 
				'feed_id' => $this->get_feed_id(),
				'product' => $this->get_product()
			];
			$offer_simple_obj = new XFAVI_Get_Unit_Offer_Simple( $args_arr );
			$r = $this->set_result( $offer_simple_obj );
			if ( true === $r ) {
				$this->ids_in_xml .= sprintf( '%s;%s;%s;%s;%s',
					$this->get_product()->get_id(),
					$this->get_product()->get_id(),
					$offer_simple_obj->get_feed_price(),
					$offer_simple_obj->get_feed_category_id(),
					PHP_EOL
				);
				$this->result_stock_xml = $offer_simple_obj->get_xml_stock_item();
			}
		}

		return $this->get_result();
	}

	/**
	 * Set result
	 * 
	 * @param XFAVI_Get_Unit_Offer $offer_obj
	 * 
	 * @return bool
	 */
	protected function set_result( XFAVI_Get_Unit_Offer $offer_obj ) {
		if ( ! empty( $offer_obj->get_skip_reasons_arr() ) ) {
			foreach ( $offer_obj->get_skip_reasons_arr() as $value ) {
				array_push( $this->skip_reasons_arr, $value );
			}
		}
		if ( true === $offer_obj->get_do_empty_product_xml() ) {
			$this->result_xml = '';
			return false;
		} else { // если нет причин пропускать товар
			$this->result_xml .= $offer_obj->get_product_xml();
			return true;
		}
	}
}