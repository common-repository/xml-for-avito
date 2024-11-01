<?php
/**
 * Traits Stock for simple products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   2.3.0
 * 
 * @version                 2.3.0 (04-04-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    Get_Open_Tag
 *                                      Get_Closed_Tag
 *                          traits:     
 *                          methods:    get_feed_id
 *                                      get_product
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Simple_Get_Stock {
	/**
	 * Get Stock
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_stock( $tag_name = 'stock', $result_xml = '' ) {
		$tag_value = '';

		if ( true == $this->get_product()->get_manage_stock() ) { // включено управление запасом
			if ( $this->get_product()->get_stock_quantity() > 0 ) {
				$tag_value = $this->get_product()->get_stock_quantity();
			} else {
				if ( $this->get_product()->get_backorders() === 'no' ) { // предзаказ запрещен
					$tag_value = 0;
				} else {
					$behavior_onbackorder = common_option_get( 'xfavi_behavior_onbackorder', false, $this->get_feed_id(), 'xfavi' );
					if ( $behavior_onbackorder === 'false' ) {
						$tag_value = 0;
					} else {
						$tag_value = 1;
					}
				}
			}
		} else { // отключено управление запасом
			if ( $this->get_product()->get_stock_status() === 'instock' ) {
				$tag_value = 1;
			} else if ( $this->get_product()->get_stock_status() === 'outofstock' ) {
				$tag_value = 0;
			} else {
				$behavior_onbackorder = common_option_get( 'xfavi_behavior_onbackorder', false, $this->get_feed_id(), 'xfavi' );
				if ( $behavior_onbackorder === 'false' ) {
					$tag_value = 0;
				} else {
					$tag_value = 1;
				}
			}
		}

		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'xfavi_f_simple_tag_name_stock',
				$tag_name,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);
			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'xfavi_f_simple_tag_stock',
			$result_xml,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}