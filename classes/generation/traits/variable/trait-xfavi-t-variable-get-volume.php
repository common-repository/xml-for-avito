<?php
/**
 * Traits Volume for variable products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.1.2 (31-08-2023)
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

trait XFAVI_T_Variable_Get_Volume {
	/**
	 * Summary of get_volume
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_volume( $tag_name = 'Volume', $result_xml = '' ) {
		$tag_value = '';

		if ( ! empty( get_post_meta( $this->get_product()->get_id(), '_xfavi_volume', true ) ) ) {
			$tag_value = get_post_meta( $this->get_product()->get_id(), '_xfavi_volume', true );
		}

		$tag_value = apply_filters(
			'xfavi_f_variable_tag_value_volume',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'xfavi_f_variable_tag_name_volume',
				$tag_name,
				[ 
					'product' => $this->get_product(),
					'offer' => $this->get_offer()
				],
				$this->get_feed_id()
			);
			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'xfavi_f_variable_tag_volume',
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