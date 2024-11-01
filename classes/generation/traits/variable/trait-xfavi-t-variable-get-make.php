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

trait XFAVI_T_Variable_Get_Make {
	public function get_make($tag_name = 'Make', $result_xml = '') {
		$product = $this->get_product();
		$offer = $this->get_offer();
		$tag_value = '';

		$make = xfavi_optionGET('xfavi_make', $this->get_feed_id(), 'set_arr');
		if (empty($make) || $make === 'disabled') { } else {
			$make = (int)$make;
			$tag_value = $offer->get_attribute(wc_attribute_taxonomy_name_by_id($make));
			if (empty($tag_value)) {	
				$tag_value = $product->get_attribute(wc_attribute_taxonomy_name_by_id($make));
			}
		}
		
		$tag_value = apply_filters('xfavi_f_variable_tag_value_make', $tag_value, array('product' => $product, 'offer' => $offer), $this->get_feed_id());
		if (!empty($tag_value)) {	
			$tag_name = apply_filters('xfavi_f_variable_tag_name_make', $tag_name, array('product' => $product, 'offer' => $offer), $this->get_feed_id());
			$result_xml = new Get_Paired_Tag($tag_name, $tag_value);
		}

		$result_xml = apply_filters('xfavi_f_variable_tag_make', $result_xml, array('product' => $product, 'offer' => $offer), $this->get_feed_id());
		return $result_xml;
	}
}
?>