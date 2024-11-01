<?php 
/**
 * Traits Material for simple products
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
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Simple_Get_Material {
	/**
	 * Summary of get_material
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_material( $tag_name = 'Material', $result_xml = '' ) {
		$tag_value = '';

		if ( ! empty( get_post_meta( $this->get_product()->get_id(), '_xfavi_material', true ) ) ) {
			$tag_value = get_post_meta( $this->get_product()->get_id(), '_xfavi_material', true );
		}

		$tag_value = apply_filters(
			'xfavi_f_simple_tag_value_material',
			$tag_value,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'xfavi_f_simple_tag_name_material',
				$tag_name,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);
			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'xfavi_f_simple_tag_material',
			$result_xml,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}