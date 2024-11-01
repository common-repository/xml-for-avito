<?php defined( 'ABSPATH' ) || exit;
/**
 * Traits PlumbingType for variable products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.1.11 (23-11-2023)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_feed_id
 *                                      get_product
 *                                      get_offer
 *                          functions:  
 *                          constants:  
 */

trait XFAVI_T_Variable_Get_Plumbing_Type {
	/**
	 * Summary of get_plumbing_type
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_plumbing_type( $tag_name = 'PlumbingType', $result_xml = '' ) {
		$tag_value = '';

		if ( get_post_meta( $this->get_product()->get_id(), '_xfavi_plumbing_type', true ) === 'disabled' ) {
			$tag_value = 'disabled';
		} else if ( get_post_meta( $this->get_product()->get_id(), '_xfavi_plumbing_type', true ) == ''
			|| get_post_meta( $this->get_product()->get_id(), '_xfavi_plumbing_type', true ) === 'default' ) {
			$tag_value = get_term_meta( $this->get_feed_category_id(), 'xfavi_default_another_type', true );
			$tag_value = str_replace( '_', ' ', $tag_value );
		} else {
			$tag_value = get_post_meta( $this->get_product()->get_id(), '_xfavi_plumbing_type', true );
		}

		$tag_value = apply_filters(
			'xfavi_f_variable_tag_value_plumbing_type',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);

		if ( $tag_value == '' || $tag_value == 'disabled' ) {
		} else {
			if ($tag_value == 'Столы' || $tag_value == 'Кресла и стулья' || $tag_value == 'Другое') {
				$tag_name = 'DeskChairType';
			}
			$result_xml .= new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'xfavi_f_variable_tag_plumbing_type',
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