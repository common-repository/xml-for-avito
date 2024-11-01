<?php
/**
 * Traits OEMOil for variable products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   2.4.5
 * 
 * @version                 2.4.5 (22-07-2024)
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

trait XFAVI_T_Variable_Get_OEMOil {
	/**
	 * Get the `OEMOil` tag
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_oemoil( $tag_name = 'OEMOil', $result_xml = '' ) {
		$tag_value = '';

		$oemoil = common_option_get( 'xfavi_oemoil', false, $this->get_feed_id(), 'xfavi' );
		switch ( $oemoil ) { // disabled, sku, id
			case "disabled": // выгружать штрихкод нет нужды		
				break;
			case "sku": // выгружать из артикула
				$tag_value = $this->get_offer()->get_sku();
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_sku();
				}
				break;
			default:
				$oemoil = (int) $oemoil;
				$tag_value = $this->get_offer()->get_attribute( wc_attribute_taxonomy_name_by_id( $oemoil ) );
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $oemoil ) );
				}
		}

		if ( ! empty( $oemoil ) && $oemoil !== 'disabled' ) { // значение внутри карточки товара в приоритете
			if ( get_post_meta( $this->get_product()->get_id(), '_xfavi_oemoil', true ) !== '' ) {
				$tag_value = get_post_meta( $this->get_product()->get_id(), '_xfavi_oemoil', true );
			}
		}

		$tag_value = apply_filters(
			'xfavi_f_variable_tag_value_oemoil',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'xfavi_f_variable_tag_name_oemoil',
				$tag_name,
				[ 
					'product' => $this->get_product(),
					'offer' => $this->get_offer()
				],
				$this->get_feed_id()
			);
			$result_xml = get_several_tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'xfavi_f_variable_tag_oemoil',
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