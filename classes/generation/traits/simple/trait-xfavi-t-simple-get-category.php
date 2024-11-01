<?php defined( 'ABSPATH' ) || exit;
/**
 * Traits for simple products
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
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_product
 *                                      get_feed_id
 *                          functions:  
 *                          constants:  
 */

trait XFAVI_T_Simple_Get_Category {
	public function get_category( $tag_name = 'Category', $result_xml = '' ) {
		$product = $this->get_product();

		$result_xml_avito_cat = '';
		if ( $this->get_feed_category_avito_name() !== '' ) {
			$result_xml_avito_cat = new Get_Paired_Tag( $tag_name, $this->get_feed_category_avito_name() );
		} else {
			new XFAVI_Error_Log( sprintf(
				'FEED № %1$s; Товар с postId = %2$s пропущен т.к отсутствует Category; Файл: %3$s Строка: %4$s',
				$this->get_feed_id(),
				$product->get_id(),
				'trait-xfavi-t-simple-get-avito-category.php',
				__LINE__
			) );
			return $result_xml;
		}

		$result_xml_avito_cat = apply_filters(
			'xfavi_f_simple_tag_categoryid',
			$result_xml_avito_cat,
			[ 'product' => $product ],
			$this->get_feed_id()
		);
		return $result_xml_avito_cat;
	}
}