<?php
/**
 * Traits Avito_Id for variable products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   2.1.21
 * 
 * @version                 2.2.0 (22-03-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    XFAVI_Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_feed_id
 *                                      get_product
 *                                      get_offer
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Variable_Get_Avito_Id {
	/**
	 * Summary of get_avito_id
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_avito_id( $tag_name = 'AvitoId', $result_xml = '' ) {
		$tag_value = '';

		if ( ! empty( get_post_meta( $this->get_offer()->get_id(), '_xfavi_avito_id', true ) ) ) {
			$tag_value = get_post_meta( $this->get_offer()->get_id(), '_xfavi_avito_id', true );
		} else if ( ! empty( get_post_meta( $this->get_product()->get_id(), '_xfavi_avito_id', true ) ) ) {
			$tag_value = get_post_meta( $this->get_product()->get_id(), '_xfavi_avito_id', true );
		}

		$tag_value = apply_filters(
			'xfavi_f_variable_tag_value_get_avito_id',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'xfavi_f_variable_tag_name_get_avito_id',
				$tag_name,
				[ 
					'product' => $this->get_product(),
					'offer' => $this->get_offer()
				],
				$this->get_feed_id()
			);
			$result_xml = get_several_tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'xfavi_f_variable_tag_get_avito_id',
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