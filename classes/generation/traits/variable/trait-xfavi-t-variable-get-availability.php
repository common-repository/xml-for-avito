<?php
/**
 * Traits Availability for variable products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   2.1.7
 * 
 * @version                 2.1.7 (24-10-2023)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    XFAVI_Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_feed_id
 *                                      get_product
 *                                      get_offer
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Variable_Get_Availability {
	/**
	 * Summary of get_availability
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_availability( $tag_name = 'Availability', $result_xml = '' ) {
		$tag_value = '';

		if ( true == $this->get_offer()->get_manage_stock() ) { // включено управление запасом
			if ( $this->get_offer()->get_stock_quantity() > 0 ) {
				$tag_value = 'В наличии';
			} else {
				if ( $this->get_offer()->get_backorders() === 'no' ) { // предзаказ запрещен
					$tag_value = 'Под заказ';
				} else {
					$xfavi_behavior_onbackorder = common_option_get( 'xfavi_behavior_onbackorder', false, $this->get_feed_id(), 'xfavi' );
					if ( $xfavi_behavior_onbackorder === 'false' ) {
						$tag_value = 'Под заказ';
					} else {
						$tag_value = 'В наличии';
					}
				}
			}
		} else { // отключено управление запасом
			if ( $this->get_offer()->get_stock_status() === 'instock' ) {
				$tag_value = 'В наличии';
			} else if ( $this->get_offer()->get_stock_status() === 'outofstock' ) {
				$tag_value = 'Под заказ';
			} else {
				$xfavi_behavior_onbackorder = common_option_get( 'xfavi_behavior_onbackorder', false, $this->get_feed_id(), 'xfavi' );
				if ( $xfavi_behavior_onbackorder === 'false' ) {
					$tag_value = 'Под заказ';
				} else {
					$tag_value = 'В наличии';
				}
			}
		}

		if ( ! empty( get_post_meta( $this->get_product()->get_id(), '_xfavi_availability', true ) ) ) {
			$tag_value = get_post_meta( $this->get_product()->get_id(), '_xfavi_availability', true );
		}

		$tag_value = apply_filters(
			'xfavi_f_variable_tag_value_availability',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'xfavi_f_variable_tag_name_availability',
				$tag_name,
				[ 
					'product' => $this->get_product(),
					'offer' => $this->get_offer()
				],
				$this->get_feed_id()
			);
			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'xfavi_f_variable_tag_availability',
			$result_xml,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}