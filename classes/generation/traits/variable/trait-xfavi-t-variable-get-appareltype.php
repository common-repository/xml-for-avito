<?php
/**
 * Traits ApparelType for variable products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.3.0 (04-04-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_feed_id
 *                                      get_product
 *                                      get_offer
 *                                      is_default_value
 *                                      get_feed_category_id
 *                          functions:  
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Variable_Get_Appareltype {
	/**
	 * Summary of get_appareltype
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_appareltype( $tag_name = 'ApparelType', $result_xml = '' ) {
		$tag_value = '';

		if ( true === $this->is_default_value( '_xfavi_appareltype' ) ) {
			// если в карточке товара запрет - проверяем значения по дефолту
			if ( get_term_meta( $this->get_feed_category_id(), 'xfavi_default_appareltype', true ) !== '' ) {
				$tag_value = get_term_meta( $this->get_feed_category_id(), 'xfavi_default_appareltype', true );
			}
		} else {
			$tag_value = get_post_meta( $this->get_product()->get_id(), '_xfavi_appareltype', true );
		}

		$tag_value = apply_filters(
			'xfavi_f_simple_tag_value_appareltype',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);

		if ( empty( $tag_value ) || $tag_value === 'disabled' ) {

		} else {
			$result_xml .= new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$tag_value = apply_filters(
			'xfavi_f_simple_tag_appareltype',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);

		return $result_xml;
	}
}