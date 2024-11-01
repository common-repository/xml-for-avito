<?php defined( 'ABSPATH' ) || exit;
/**
 * Traits Price for simple products
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
 *                          methods:    get_feed_id
 *                                      get_product
 *                          functions:  common_option_get
 *                          constants:  
 */

trait XFAVI_T_Simple_Get_Price {
	/**
	 * Summary of get_price
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_price( $tag_name = 'price', $result_xml = '' ) {
		$product = $this->product;
		$product_category_id = $this->get_feed_category_id();

		/**
		 * $offer->get_price() - актуальная цена (равна sale_price или regular_price если sale_price пуст)
		 * $offer->get_regular_price() - обычная цена
		 * $offer->get_sale_price() - цена скидки
		 */
		$price_xml = $product->get_price();
		$price_xml = apply_filters( 'xfavi_simple_price_filter', $price_xml, $product, $this->get_feed_id() );
		$price_xml = apply_filters(
			'xfavi_f_simple_price',
			(float) $price_xml,
			[ 
				'product' => $product,
				'product_category_id' => $product_category_id
			],
			$this->get_feed_id()
		);

		if ( $price_xml == 0 || empty ( $price_xml ) ) {
			$this->add_skip_reason(
				[ 
					'reason' => __( 'Price not specified', 'xfavi' ),
					'post_id' => $product->get_id(),
					'file' => 'trait-xfavi-t-simple-get-price.php',
					'line' => __LINE__
				]
			);
			return '';
		}

		if ( class_exists( 'XmlforAvitoPro' ) ) {
			if ( ( xfavi_optionGET( 'xfavip_compare_value', $this->get_feed_id(), 'set_arr' ) !== false )
				&& ( xfavi_optionGET( 'xfavip_compare_value', $this->get_feed_id(), 'set_arr' ) !== '' ) ) {
				$xfavip_compare_value = xfavi_optionGET( 'xfavip_compare_value', $this->get_feed_id(), 'set_arr' );
				$xfavip_compare = xfavi_optionGET( 'xfavip_compare', $this->get_feed_id(), 'set_arr' );
				if ( $xfavip_compare == '>=' || $xfavip_compare == "&gt;=" ) {
					if ( $price_xml < $xfavip_compare_value ) {
						$this->add_skip_reason( [ 
							'reason' => __( 'Цена товара', 'xfavi' ) . ' ' . $product->get_price() . ': < ' . $xfavip_compare_value,
							'post_id' => $product->get_id(),
							'file' => 'trait-xfavi-t-simple-get-price.php',
							'line' => __LINE__
						] );
						return '';
					}
				} else {
					if ( $price_xml >= $xfavip_compare_value ) {
						$this->add_skip_reason( [ 
							'reason' => __( 'Цена товара', 'xfavi' ) . ' ' . $product->get_price() . ': >= ' . $xfavip_compare_value,
							'post_id' => $product->get_id(),
							'file' => 'trait-xfavi-t-simple-get-price.php',
							'line' => __LINE__
						] );
						return '';
					}
				}
			}
		}

		$skip_price_reason = false;
		$skip_price_reason = apply_filters(
			'xfavi_f_simple_skip_price_reason',
			$skip_price_reason,
			[ 
				'price_xml' => $price_xml,
				'product' => $product
			],
			$this->get_feed_id()
		);
		if ( $skip_price_reason !== false ) {
			$this->add_skip_reason( [ 
				'reason' => $skip_price_reason,
				'post_id' => $product->get_id(),
				'file' => 'trait-xfavi-t-simple-get-price.php',
				'line' => __LINE__
			] );
			return '';
		}

		$price_xml = apply_filters( 'xfavi_simple_price_xml_filter', $price_xml, $product, $this->get_feed_id() );

		$result_xml .= new Get_Paired_Tag( $tag_name, $price_xml );

		$result_xml = apply_filters(
			'xfavi_f_simple_tag_price',
			$result_xml,
			[ 
				'product' => $product,
				'product_category_id' => $product_category_id
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}