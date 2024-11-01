<?php defined( 'ABSPATH' ) || exit;
/**
* Traits for variable products
*
* @author		Maxim Glazunov
* @link			https://icopydoc.ru/
* @since		1.6.0
*
* @return 		$result_xml (string)
*
* @depends		class:	XFAVI_Get_Paired_Tag
*				methods: add_skip_reason
*				functions: 
*/

trait XFAVI_T_Variable_Get_Category {
	public function get_category($tag_name = 'Category', $result_xml = '') {
		$product = $this->product;
		$offer = $this->offer;

		$result_xml_avito_cat = '';
		if ($this->get_feed_category_avito_name() !== '') {
			$result_xml_avito_cat = '<Category>'.$this->get_feed_category_avito_name().'</Category>'.PHP_EOL;
		} else {
			new XFAVI_Error_Log('FEED № '.$this->get_feed_id().'; Товар с postId = '.$product->get_id().' пропущен т.к отсутствует Category; Файл: trait-xfavi-t-variable-get-avito-category.php; Строка: '.__LINE__); return $result_xml;
		}

		$result_xml_avito_cat = apply_filters('xfavi_f_variable_tag_category', $result_xml_avito_cat, array('product' => $product, 'offer' => $offer), $this->get_feed_id());
		return $result_xml_avito_cat;
	}
}
?>