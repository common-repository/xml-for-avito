<?php
/**
 * Traits Title for variable products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.5.0 (04-10-2024)
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

trait XFAVI_T_Variable_Get_Title {
	/**
	 * Get `title` tag
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_title( $tag_name = 'title', $result_xml = '' ) {
		if ( get_post_meta( $this->get_product()->get_id(), '_xfavi_product_name', true ) !== '' ) {
			$result_xml_name = get_post_meta( $this->get_product()->get_id(), '_xfavi_product_name', true );
		} else {
			$result_xml_name = $this->get_product()->get_title(); // название товара
			$result_xml_name = apply_filters(
				'xfavi_f_variable_tag_value_name',
				$result_xml_name,
				[ 
					'product' => $this->get_product(),
					'offer' => $this->get_offer()
				],
				$this->get_feed_id()
			);
		}

		$xfavi_add_attr_to_title = common_option_get( 'xfavi_add_attr_to_title', false, $this->get_feed_id(), 'xfavi' );
		if ( $xfavi_add_attr_to_title === 'add_only_value' ) {
			// получаем все атрибуты товара
			$attributes = $this->get_product()->get_attributes();
			foreach ( $attributes as $param ) {
				if ( true === $param->get_variation() ) {
					$param_val = $this->get_offer()->get_attribute( wc_attribute_taxonomy_name_by_id( $param->get_id() ) );
					$param_name = wc_attribute_label( wc_attribute_taxonomy_name_by_id( $param->get_id() ) );
					// если пустое имя атрибута или значение - пропускаем
					if ( empty( $param_name ) || ( empty( $param_val ) && $param_val !== '0' ) ) {
						continue;
					} else {
						$result_xml_name = sprintf( '%s, %s', $result_xml_name, urldecode( $param_val ) );
					}
				}
			}
		}

		$result_xml_name = mb_substr( $result_xml_name, 0, 50 );
		$result_xml_name = htmlspecialchars( $result_xml_name, ENT_NOQUOTES );
		$result_xml = new Get_Paired_Tag( $tag_name, $result_xml_name );

		$result_xml = apply_filters(
			'xfavi_f_variable_tag_name',
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