<?php defined( 'ABSPATH' ) || exit;
/**
* Traits for variable products
*
* @author		Maxim Glazunov
* @link			https://icopydoc.ru/
* @since		1.6.7
*
* @return 		$result_xml (string)
*
* @depends		class:	XFAVI_Get_Paired_Tag
*				methods: add_skip_reason
*				functions: 
*/

trait XFAVI_T_Variable_Get_Model {
	public function get_model($tag_name = 'Model', $result_xml = '') {
		$product = $this->get_product();
		$offer = $this->get_offer();
		$tag_value = '';

		$model = xfavi_optionGET('xfavi_model', $this->get_feed_id(), 'set_arr');
		if (empty($model) || $model === 'disabled') { } else {
			$model = (int)$model;
			$tag_value = $offer->get_attribute(wc_attribute_taxonomy_name_by_id($model));
			if (empty($tag_value)) {	
				$tag_value = $product->get_attribute(wc_attribute_taxonomy_name_by_id($model));
			}
		}
		
		$tag_value = apply_filters('xfavi_f_variable_tag_value_model', $tag_value, array('product' => $product, 'offer' => $offer), $this->get_feed_id());
		if (!empty($tag_value)) {	
			$tag_name = apply_filters('xfavi_f_variable_tag_name_model', $tag_name, array('product' => $product, 'offer' => $offer), $this->get_feed_id());
			$result_xml = new Get_Paired_Tag($tag_name, $tag_value);
		}

		$result_xml = apply_filters('xfavi_f_variable_tag_model', $result_xml, array('product' => $product, 'offer' => $offer), $this->get_feed_id());
		return $result_xml;
	}
}
?>