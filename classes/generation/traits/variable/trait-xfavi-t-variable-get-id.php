<?php
/**
 * Traits for variable products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.2.1 (26-03-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_feed_id
 *                                      get_product
 *                                      get_offer
 *                                      get_duplicate_number
 *                          functions:  
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Variable_Get_Id {
	/**
	 * Get product ID for XML feed
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_id( $tag_name = 'Id', $result_xml = '' ) {
		$tag_value = '';

		$xfavi_var_source_id = common_option_get( 'xfavi_var_source_id', false, $this->get_feed_id(), 'xfavi' );
		switch ( $xfavi_var_source_id ) {
			case "product_id":
				$tag_value = $this->get_product()->get_id();
				break;
			case "offer_id":
				$tag_value = $this->get_offer()->get_id();
				break;
			case "product_sku":
				$tag_value = $this->get_product()->get_sku();
				break;
			case "offer_sku":
				$tag_value = $this->get_offer()->get_sku();
				break;
			default:
				$tag_value = $this->get_product()->get_id();
		}

		$tag_value = apply_filters(
			'xfavi_f_variable_tag_value_id',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer(),
				'duplicate_number' => $this->get_duplicate_number()
			],
			$this->get_feed_id()
		);
		if ( ! empty ( $tag_value ) ) {
			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'xfavi_f_variable_tag_id',
			$result_xml,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer(),
				'duplicate_number' => $this->get_duplicate_number()
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}