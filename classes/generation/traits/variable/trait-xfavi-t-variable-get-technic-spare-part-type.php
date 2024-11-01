<?php
/**
 * Traits Technic_Spare_Part_Type for variable products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   2.4.10
 * 
 * @version                 2.4.10 (20-09-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_feed_id
 *                                      get_product
 *                                      get_offer
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Variable_Get_Technic_Spare_Part_Type {
	/**
	 * Get the `TechnicSparePartType` tag
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_technic_spare_part_type( $tag_name = 'TechnicSparePartType', $result_xml = '' ) {
		$tag_value = '';

		if ( get_post_meta( $this->get_product()->get_id(), '_xfavi_technic_spare_part_type', true ) !== '' ) {
			$tag_value = get_post_meta( $this->get_product()->get_id(), '_xfavi_technic_spare_part_type', true );
		}

		if ( empty( $tag_value ) ) { // значение внутри карточки товара в приоритете
			$technic_spare_part_type = get_term_meta( $this->get_feed_category_id(), 'xfavi_technic_spare_part_type', true );
			$technic_spare_part_type = str_replace( '_', ' ', $technic_spare_part_type );
			if ( $technic_spare_part_type === 'disabled' ) {
				$tag_value = '';
			} else {
				$tag_value = $technic_spare_part_type;
			}
		}

		/*
		$technic_spare_part_type = common_option_get( 'xfavi_technic_spare_part_type', false, $this->get_feed_id(), 'xfavi' );
		switch ( $technic_spare_part_type ) {
			case "disabled":
				break;
			default:
				$technic_spare_part_type = (int) $technic_spare_part_type;
				$tag_value = $this->get_offer()->get_attribute( wc_attribute_taxonomy_name_by_id( $technic_spare_part_type ) );
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $technic_spare_part_type ) );
				}
		}*/

		$tag_value = apply_filters(
			'xfavi_f_variable_tag_value_technic_spare_part_type',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'xfavi_f_variable_tag_name_technic_spare_part_type',
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
			'xfavi_f_variable_tag_technic_spare_part_type',
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