<?php defined( 'ABSPATH' ) || exit;
/**
 * Traits Generation for simple products
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
 *                          functions:  
 *                          constants:  
 */

trait XFAVI_T_Simple_Get_Generation {
	/**
	 * Summary of get_generation
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_generation( $tag_name = 'Generation', $result_xml = '' ) {
		$tag_value = '';

		$generation = xfavi_optionGET( 'xfavi_generation', $this->get_feed_id(), 'set_arr' );
		if ( empty( $generation ) || $generation === 'disabled' ) {
		} else {
			$generation = (int) $generation;
			$tag_value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $generation ) );
		}

		$tag_value = apply_filters(
			'xfavi_f_simple_tag_value_generation',
			$tag_value,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'xfavi_f_simple_tag_name_generation',
				$tag_name,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);
			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'xfavi_f_simple_tag_generation',
			$result_xml,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}