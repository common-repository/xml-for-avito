<?php
/**
 * Traits Custom Type for simple products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   2.1.12
 * 
 * @version                 2.5.1 (29-10-2024)
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

trait XFAVI_T_Simple_Get_Custom_Type {
	/**
	 * Get custom tag
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_custom_type( $result_xml = '' ) {
		for ( $i = 1; $i < 5; $i++ ) {
			if ( get_term_meta( $this->get_feed_category_id(), 'xfavi_custom_type_tag_name' . $i, true ) !== '' ) {
				$tag_name = get_term_meta( $this->get_feed_category_id(), 'xfavi_custom_type_tag_name' . $i, true );
			} else {
				$tag_name = '';
			}
			$tag_name = apply_filters(
				'xfavi_f_simple_tag_name_custom_type',
				$tag_name,
				[ 
					'product' => $this->get_product(),
					'index' => $i
				],
				$this->get_feed_id()
			);

			if ( get_term_meta( $this->get_feed_category_id(), 'xfavi_custom_type_tag_value' . $i, true ) !== '' ) {
				$tag_value = get_term_meta( $this->get_feed_category_id(), 'xfavi_custom_type_tag_value' . $i, true );
			} else {
				$tag_value = '';
			}
			$tag_value = apply_filters(
				'xfavi_f_simple_tag_value_custom_type',
				$tag_value,
				[ 
					'product' => $this->get_product(),
					'index' => $i
				],
				$this->get_feed_id()
			);

			if ( ! empty( $tag_name ) && ! empty( $tag_value ) ) {
				$result_xml .= new Get_Paired_Tag( $tag_name, $tag_value );
			}
		}

		for ( $i = 1; $i < 4; $i++ ) {
			$tag_name = get_post_meta(
				$this->get_product()->get_id(),
				'_xfavi_custom_type_tag_name_' . $i,
				true
			);
			$tag_value = get_post_meta(
				$this->get_product()->get_id(),
				'_xfavi_custom_type_tag_value_' . $i,
				true
			);
			if ( ! empty( $tag_name ) && ! empty( $tag_value ) ) {
				$result_xml .= new Get_Paired_Tag( $tag_name, $tag_value );
			}
		}

		return $result_xml;
	}
}