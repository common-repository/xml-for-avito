<?php
/**
 * Traits Transmission_Spare_Part_Type for simple products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   2.4.5
 * 
 * @version                 2.4.6 (22-07-2024)
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

trait XFAVI_T_Simple_Get_Transmission_Spare_Part_Type {
	/**
	 * Get the `TransmissionSparePartType` tag
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_transmission_spare_part_type( $tag_name = 'TransmissionSparePartType', $result_xml = '' ) {
		$tag_value = '';

		if ( get_post_meta( $this->get_product()->get_id(), '_xfavi_transmission_spare_part_type', true ) !== '' ) {
			$tag_value = get_post_meta( $this->get_product()->get_id(), '_xfavi_transmission_spare_part_type', true );
		}

		if ( empty( $tag_value ) ) { // значение внутри карточки товара в приоритете
			$transmission_spare_part_type = get_term_meta( $this->get_feed_category_id(), 'xfavi_transmission_spare_part_type', true );
			$transmission_spare_part_type = str_replace( '_', ' ', $transmission_spare_part_type );
			if ( $transmission_spare_part_type === 'disabled' ) {
				$tag_value = '';
			} else {
				$tag_value = $transmission_spare_part_type;
			}
		}

		/*
		$transmission_spare_part_type = common_option_get( 'xfavi_transmission_spare_part_type', false, $this->get_feed_id(), 'xfavi' );
		switch ( $transmission_spare_part_type ) {
			case "disabled":
				break;
			default:
				$transmission_spare_part_type = (int) $transmission_spare_part_type;
				$tag_value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $transmission_spare_part_type ) );
		} */

		$tag_value = apply_filters(
			'xfavi_f_simple_tag_value_transmission_spare_part_type',
			$tag_value,
			[ 'product' => $this->get_product() ],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'xfavi_f_simple_tag_name_transmission_spare_part_type',
				$tag_name,
				[ 'product' => $this->get_product() ],
				$this->get_feed_id()
			);

			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'xfavi_f_simple_tag_transmission_spare_part_type',
			$result_xml,
			[ 'product' => $this->get_product() ],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}