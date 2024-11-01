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

trait XFAVI_T_Variable_Get_Condition {
	public function get_condition($tag_name = 'Condition', $result_xml = '') {
		$product = $this->get_product();
		$offer = $this->offer;
 
		if (in_array($this->get_feed_category_avito_name(), array(
			'Собаки', 
			'Кошки', 
			'Птицы', 
			'Аквариум', 
		))) {return $result_xml;}

		$result_xml_condition = '';
		if (get_post_meta($product->get_id(), '_xfavi_condition', true) === '') {	
			$xfavi_condition = xfavi_optionGET('xfavi_condition', $this->get_feed_id(), 'set_arr');
		} else {
			$xfavi_condition = get_post_meta($product->get_id(), '_xfavi_condition', true);
		}
		if ($xfavi_condition === 'new') {
			if (in_array($this->get_feed_category_avito_name(), array('Товары для детей и игрушки', 'Детская одежда и обувь'))) {
				$result_xml = '<Condition>Новый</Condition>'.PHP_EOL;
			} else if (in_array($this->get_feed_category_avito_name(), array('Одежда, обувь, аксессуары'))) {
				$result_xml = '<Condition>Новое с биркой</Condition>'.PHP_EOL;	
			} else {
				$result_xml = '<Condition>Новое</Condition>'.PHP_EOL;
			}
		} else {
			$result_xml = '<Condition>Б/у</Condition>'.PHP_EOL;
		}

		return $result_xml;
	}
}
?>