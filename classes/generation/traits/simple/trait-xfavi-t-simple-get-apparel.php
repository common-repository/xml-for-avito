<?php
/**
 * Traits Apparel for simple products
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
 *                          methods:    get_product
 *                                      get_feed_id
 *                                      is_default_value
 *                                      get_feed_category_id
 *                          functions:  
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Simple_Get_Apparel {
	/**
	 * Возвращает Apparel - Тип товара
	 * 
	 * @param string $tag_name
	 * @param string $standart
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_apparel( $tag_name = 'Apparel', $standart = '', $result_xml = '' ) {
		$tag_value = '';

		if ( true === $this->is_default_value( '_xfavi_apparel' ) ) {
			// если в карточке товара запрет - проверяем значения по дефолту
			if ( get_term_meta( $this->get_feed_category_id(), 'xfavi_default_apparel', true ) !== '' ) {
				$tag_value = get_term_meta( $this->get_feed_category_id(), 'xfavi_default_apparel', true );
			}
		} else {
			$tag_value = get_post_meta( $this->get_product()->get_id(), '_xfavi_apparel', true );
		}

		$tag_value = apply_filters(
			'xfavi_f_simple_tag_value_apparel',
			$tag_value,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);

		if ( empty( $tag_value ) || $tag_value === 'disabled' ) {
			if ( $standart === 'lichnye_veshi'
				&& in_array( $this->get_feed_category_avito_name(), [ 'Одежда, обувь, аксессуары', 'Детская одежда и обувь' ] ) ) {
				$this->add_skip_reason( [ 
					'reason' => __( 'Отсутствует Apparel', 'xml-for-avito' ),
					'post_id' => $this->get_product()->get_id(),
					'file' => 'trait-xfavi-t-simple-get-apparel.php',
					'line' => __LINE__
				] );
				return $result_xml;
			}
		} else {
			if ( $tag_value == 'Сумки' ) {
				$result_xml .= $this->get_color();
				$result_xml .= $this->get_material();
			}

			$result_xml .= new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$tag_value = apply_filters(
			'xfavi_f_simple_tag_apparel',
			$tag_value,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);

		return $result_xml;
	}
}