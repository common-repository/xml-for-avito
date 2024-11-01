<?php defined( 'ABSPATH' ) || exit;
/**
 * Traits Condition for simple products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.0.5 (07-08-2023)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    XFAVI_Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_feed_id
 *                                      get_product
 *                          functions:  common_option_get
 *                          constants:  
 */

trait XFAVI_T_Simple_Get_Condition {
	/**
	 * Summary of get_condition
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_condition( $tag_name = 'Condition', $result_xml = '' ) {
		$product = $this->get_product();

		if ( in_array( $this->get_feed_category_avito_name(), [
			'Собаки',
			'Кошки',
			'Птицы',
			'Аквариум',
			] ) 
		) {
			return $result_xml;
		}

		$result_xml_condition = '';
		if ( get_post_meta( $product->get_id(), '_xfavi_condition', true ) === '' ) {
			$xfavi_condition = xfavi_optionGET( 'xfavi_condition', $this->get_feed_id(), 'set_arr' );
		} else {
			$xfavi_condition = get_post_meta( $product->get_id(), '_xfavi_condition', true );
		}
		if ( $xfavi_condition === 'new' ) {
			if ( in_array( $this->get_feed_category_avito_name(), [ 'Товары для детей и игрушки', 'Детская одежда и обувь' ] ) ) {
				$result_xml = '<Condition>Новый</Condition>' . PHP_EOL;
			} else if ( in_array( $this->get_feed_category_avito_name(), [ 'Одежда, обувь, аксессуары' ] ) ) {
				$result_xml = '<Condition>Новое с биркой</Condition>' . PHP_EOL;
			} else {
				$result_xml = '<Condition>Новое</Condition>' . PHP_EOL;
			}
		} else {
			$result_xml = '<Condition>Б/у</Condition>' . PHP_EOL;
		}

		return $result_xml;
	}
}