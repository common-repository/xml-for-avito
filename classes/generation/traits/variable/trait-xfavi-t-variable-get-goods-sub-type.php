<?php
/**
 * Traits GoodsSubType for variable products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.1.20 (09-02-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_feed_id
 *                                      get_product
 *                                      get_offer
 *                                      get_feed_category_id
 *                          functions:  
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Variable_Get_Goods_Sub_Type {
	/**
	 * Summary of get_goods_sub_type
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_goods_sub_type( $tag_name = 'GoodsSubType', $result_xml = '' ) {
		$tag_value = '';

		if ( get_post_meta( $this->get_product()->get_id(), '_xfavi_goods_subtype', true ) === 'disabled' ) {
			$tag_value = 'disabled';
		} else if ( get_post_meta( $this->get_product()->get_id(), '_xfavi_goods_subtype', true ) == ''
			|| get_post_meta( $this->get_product()->get_id(), '_xfavi_goods_subtype', true ) === 'default' ) {
			$tag_value = get_term_meta( $this->get_feed_category_id(), 'xfavi_default_goods_subtype', true );
			$tag_value = str_replace( '_', ' ', $tag_value );
		} else {
			$tag_value = get_post_meta( $this->get_product()->get_id(), '_xfavi_goods_subtype', true );
		}
		if ( $tag_value == '' || $tag_value == 'disabled' ) {

		} else {
			$goods_type_val = trim( strip_tags( $this->get_goods_type( 'GoodsType' ) ) );
			if ( $goods_type_val == 'Часы' ) {
				$tag_name = 'ProductType';
			}
			if ( $goods_type_val == 'Промышленное' ) {
				$tag_name = 'GoodsPromType';
			}

			$tag_value = apply_filters(
				'xfavi_f_variable_tag_value_goods_sub_type',
				$tag_value,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);
			$result_xml .= new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'xfavi_f_variable_tag_goods_sub_type',
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