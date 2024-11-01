<?php
/**
 * Traits XML Stock Item for variable products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   2.3.0
 * 
 * @version                 2.3.0 (04-04-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    Get_Open_Tag
 *                                      Get_Closed_Tag
 *                          traits:     
 *                          methods:    get_feed_id
 *                                      get_product
 *                                      get_id
 *                                      get_avito_id
 *                                      get_stock
 *                          functions:  
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Variable_Get_XML_Stock_Item {
	/**
	 * Get XML Stock Item
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_xml_stock_item( $result_xml = '' ) {
		$stock_xml = $this->get_stock( 'stock' );
		if ( ! empty( $stock_xml ) ) {
			$duplicate_count = 0; // сколько дублей товара нужно. 0 - если дубли не нужны
			$duplicate_count = apply_filters(
				'xfavi_f_duplicate_count',
				$duplicate_count,
				[ 
					'feed_id' => $this->get_feed_id(),
					'product' => $this->get_product(),
					'offer' => $this->get_offer()
				],
				$this->get_feed_id()
			);
			$duplicate_number = -1;

			while ( $duplicate_number < $duplicate_count ) {
				$duplicate_number++;
				$this->set_duplicate_number( $duplicate_number );

				$result_xml .= new Get_Open_Tag( 'item' );
				$result_xml .= $this->get_id( 'id' );
				$result_xml .= $this->get_avito_id( 'avitoId' );
				$result_xml .= $stock_xml;
				$result_xml .= new Get_Closed_Tag( 'item' );
			}
		}

		$result_xml = apply_filters(
			'xfavi_f_variable_xml_stock_item',
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