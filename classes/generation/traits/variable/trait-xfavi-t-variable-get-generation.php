<?php defined( 'ABSPATH' ) || exit;
/**
* Traits for variable products
*
* @author		Maxim Glazunov
* @link			https://icopydoc.ru/
* @since		1.7.11
*
* @return 		$result_xml (string)
*
* @depends		class:	XFAVI_Get_Paired_Tag
*				methods: add_skip_reason
*				functions: 
*/

trait XFAVI_T_Variable_Get_Generation {
	public function get_generation($tag_name = 'Generation', $result_xml = '') {
		$tag_value = '';

		$generation = xfavi_optionGET('xfavi_generation', $this->get_feed_id(), 'set_arr');
		if (empty($generation) || $generation === 'disabled') { } else {
			$generation = (int)$generation;
			$tag_value = $this->get_offer()->get_attribute(wc_attribute_taxonomy_name_by_id($generation));
			if (empty($tag_value)) {	
				$tag_value = $this->get_product()->get_attribute(wc_attribute_taxonomy_name_by_id($generation));
			}
		}
		
		$tag_value = apply_filters(
			'xfavi_f_variable_tag_value_generation',
			$tag_value,
			[
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		if (!empty($tag_value)) {
			$tag_name = apply_filters(
				'xfavi_f_variable_tag_name_generation',
				$tag_name,
				[
					'product' => $this->get_product(),
					'offer' => $this->get_offer()
				],
				$this->get_feed_id()
			);
			$result_xml = new Get_Paired_Tag($tag_name, $tag_value);
		}

		$result_xml = apply_filters(
			'xfavi_f_variable_tag_generation',
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