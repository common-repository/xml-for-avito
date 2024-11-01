<?php
/**
 * Traits Vendorcode for variable products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   2.1.4
 * 
 * @version                 2.1.4 (19-09-2023)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_product
 *                                      get_offer
 *                                      get_feed_id
 *                          functions:  common_option_get
 *                          constants:
 */
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Variable_Get_Vendorcode {
	/**
	 * Summary of get_vendorcode
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_vendorcode( $tag_name = 'VendorCode', $result_xml = '' ) {
		$tag_value = '';

		$xfavi_vendorcode = common_option_get( 'xfavi_vendorcode', false, $this->get_feed_id(), 'xfavi' );
		switch ( $xfavi_vendorcode ) { /* disabled, sku, post_meta, germanized, id */
			case "disabled": // выгружать штрихкод нет нужды		
				break;
			case "sku": // выгружать из артикула
				$tag_value = $this->get_offer()->get_sku();
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_sku();
				}
				break;
			default:
				$tag_value = apply_filters(
					'xfavi_f_variable_tag_value_switch_barcode',
					$tag_value,
					[ 
						'product' => $this->get_product(),
						'offer' => $this->get_offer(),
						'switch_value' => $xfavi_vendorcode
					],
					$this->get_feed_id()
				);
				if ( $tag_value == '' ) {
					$xfavi_vendorcode = (int) $xfavi_vendorcode;
					$tag_value = $this->get_offer()->get_attribute( wc_attribute_taxonomy_name_by_id( $xfavi_vendorcode ) );
					if ( empty( $tag_value ) ) {
						$tag_value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $xfavi_vendorcode ) );
					}
				}
		}

		$tag_value = apply_filters(
			'xfavi_f_variable_tag_value_vendorcode',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'xfavi_f_variable_tag_name_vendorcode',
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
			'xfavi_f_variable_tag_vendorcode',
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