<?php 
/**
 * Traits AnotherType for simple products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   2.1.12
 * 
 * @version                 2.1.12 (24-11-2023)
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
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Simple_Get_Another_Type {
	/**
	 * Summary of get_another_type
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_another_type( $tag_name = 'AnotherType', $result_xml = '' ) {
		$tag_value = '';

		if ( ! empty( get_post_meta( $this->get_product()->get_id(), '_xfavi_default_another_type', true ) ) ) {
			$tag_value = get_post_meta( $this->get_product()->get_id(), '_xfavi_default_another_type', true );
		}

		$tag_value = apply_filters(
			'xfavi_f_simple_tag_value_another_type',
			$tag_value,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'xfavi_f_simple_tag_name_another_type',
				$tag_name,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);
			$result_xml = get_several_tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'xfavi_f_simple_tag_another_type',
			$result_xml,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}