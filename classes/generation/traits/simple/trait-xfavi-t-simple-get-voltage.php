<?php
/**
 * Traits Voltage for simple products
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

trait XFAVI_T_Simple_Get_Voltage {
	/**
	 * Summary of get_voltage
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_voltage( $tag_name = 'Voltage', $result_xml = '' ) {
		$tag_value = '';

		$voltage = common_option_get( 'xfavi_voltage', false, $this->get_feed_id(), 'xfavi' );
		switch ( $voltage ) {
			case "disabled":
				break;
			default:
				$voltage = (int) $voltage;
				$tag_value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $voltage ) );
		}

		if ( ! empty( $voltage ) && $voltage !== 'disabled' ) { // значение внутри карточки товара в приоритете
			if ( get_post_meta( $this->get_product()->get_id(), '_xfavi_voltage', true ) !== '' ) {
				$tag_value = get_post_meta( $this->get_product()->get_id(), '_xfavi_voltage', true );
			}
		}

		$tag_value = apply_filters(
			'xfavi_f_simple_tag_value_voltage',
			$tag_value,
			[ 'product' => $this->get_product() ],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'xfavi_f_simple_tag_name_voltage',
				$tag_name,
				[ 'product' => $this->get_product() ],
				$this->get_feed_id()
			);

			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'xfavi_f_simple_tag_voltage',
			$result_xml,
			[ 'product' => $this->get_product() ],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}