<?php
/**
 * Traits for simple products
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
 *                                      get_duplicate_number
 *                          functions:  
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Simple_Get_Id {
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

		$xfavi_simple_source_id = common_option_get( 'xfavi_simple_source_id', false, $this->get_feed_id(), 'xfavi' );
		switch ( $xfavi_simple_source_id ) {
			case "product_id":
				$tag_value = $this->get_product()->get_id();
				break;
			case "product_sku":
				$tag_value = $this->get_product()->get_sku();
				break;
			default:
				$tag_value = $this->get_product()->get_id();
		}

		$tag_value = apply_filters(
			'xfavi_f_simple_tag_value_id',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'duplicate_number' => $this->get_duplicate_number()
			],
			$this->get_feed_id()
		);
		if ( ! empty ( $tag_value ) ) {
			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'xfavi_f_simple_tag_id',
			$result_xml,
			[ 
				'product' => $this->get_product(),
				'duplicate_number' => $this->get_duplicate_number()
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}