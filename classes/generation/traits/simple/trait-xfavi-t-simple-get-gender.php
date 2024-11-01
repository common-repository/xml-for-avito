<?php
/**
 * Traits Gender for simple products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.1.16 (19-12-2023)
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

trait XFAVI_T_Simple_Get_Gender {
	/**
	 * Summary of get_gender
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_gender( $tag_name = 'Gender', $result_xml = '' ) {
		$tag_value = '';

		$gender = common_option_get( 'xfavi_gender', false, $this->get_feed_id(), 'xfavi' );
		switch ( $gender ) { 
			case "disabled": 
				break;
			default:
				$gender = (int) $gender;
				$tag_value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $gender ) );
		}

		if ( ! empty( $gender ) && $gender !== 'disabled' ) { // значение внутри карточки товара в приоритете
			if ( get_post_meta( $this->get_product()->get_id(), '_xfavi_gender', true ) !== '' ) {
				$tag_value = get_post_meta( $this->get_product()->get_id(), '_xfavi_gender', true );
			}
		}

		$tag_value = apply_filters(
			'xfavi_f_simple_tag_value_gender',
			$tag_value,
			[ 'product' => $this->get_product() ],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'xfavi_f_simple_tag_name_gender',
				$tag_name,
				[ 'product' => $this->get_product() ],
				$this->get_feed_id()
			);

			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'xfavi_f_simple_tag_gender',
			$result_xml,
			[ 'product' => $this->get_product() ],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}