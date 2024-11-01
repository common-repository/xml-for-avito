<?php defined( 'ABSPATH' ) || exit;
/**
 * Traits Make for simple products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.0.5 (07-08-2023)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_feed_id
 *                                      get_product
 *                          functions:  common_option_get
 *                          constants:  
 */

trait XFAVI_T_Simple_Get_Make {
	/**
	 * Summary of get_make
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_make( $tag_name = 'Make', $result_xml = '' ) {
		$product = $this->get_product();
		$tag_value = '';

		$make = xfavi_optionGET( 'xfavi_make', $this->get_feed_id(), 'set_arr' );
		if ( empty( $make ) || $make === 'disabled' ) {
		} else {
			$make = (int) $make;
			$tag_value = $product->get_attribute( wc_attribute_taxonomy_name_by_id( $make ) );
		}

		$tag_value = apply_filters(
			'xfavi_f_simple_tag_value_make',
			$tag_value,
			[ 'product' => $product ],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'xfavi_f_simple_tag_name_make',
				$tag_name,
				[ 
					'product' => $product
				],
				$this->get_feed_id()
			);
			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'xfavi_f_simple_tag_make',
			$result_xml,
			[ 'product' => $product ],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}