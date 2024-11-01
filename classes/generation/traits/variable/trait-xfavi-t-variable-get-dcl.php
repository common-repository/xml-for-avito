<?php
/**
 * Traits DCL for variable products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.1.16 (19-12-2023)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_feed_id
 *                                      get_product
 *                                      get_offer
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Variable_Get_DCL {
	/**
	 * Summary of get_dcl
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_dcl( $tag_name = 'DCL', $result_xml = '' ) {
		$tag_value = '';

		$dcl = common_option_get( 'xfavi_dcl', false, $this->get_feed_id(), 'xfavi' );
		switch ( $dcl ) {
			case "disabled":
				break;
			default:
				$dcl = (int) $dcl;
				$tag_value = $this->get_offer()->get_attribute( wc_attribute_taxonomy_name_by_id( $dcl ) );
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $dcl ) );
				}
		}

		if ( ! empty( $dcl ) && $dcl !== 'disabled' ) { // значение внутри карточки товара в приоритете
			if ( get_post_meta( $this->get_product()->get_id(), '_xfavi_dcl', true ) !== '' ) {
				$tag_value = get_post_meta( $this->get_product()->get_id(), '_xfavi_dcl', true );
			}
		}

		$tag_value = apply_filters(
			'xfavi_f_variable_tag_value_dcl',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'xfavi_f_variable_tag_name_dcl',
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
			'xfavi_f_variable_tag_dcl',
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