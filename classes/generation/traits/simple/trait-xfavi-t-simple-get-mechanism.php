<?php defined( 'ABSPATH' ) || exit;
/**
 * Traits Mechanism for simple products
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

trait XFAVI_T_Simple_Get_Mechanism {
	/**
	 * Summary of get_mechanism
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_mechanism( $tag_name = 'Mechanism', $result_xml = '' ) {
		$tag_value = '';

		if ( ! empty( get_post_meta( $this->get_product()->get_id(), '_xfavi_mechanism', true ) ) ) {
			$tag_value = get_post_meta( $this->get_product()->get_id(), '_xfavi_mechanism', true );
		}

		$tag_value = apply_filters(
			'xfavi_f_simple_tag_value_mechanism',
			$tag_value,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'xfavi_f_simple_tag_name_mechanism',
				$tag_name,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);
			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'xfavi_f_simple_tag_mechanism',
			$result_xml,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}