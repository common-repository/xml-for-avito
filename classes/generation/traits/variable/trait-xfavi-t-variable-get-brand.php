<?php
/**
 * Traits Brand for variable products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.4.9 (18-09-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_product
 *                                      get_offer
 *                                      get_feed_id
 *                          functions:  
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Variable_Get_Brand {
	/**
	 * Get `brand` tag
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_brand( $tag_name = 'Brand', $result_xml = '' ) {
		$tag_value = '';

		$brand = xfavi_optionGET( 'xfavi_brand', $this->get_feed_id(), 'set_arr' );
		if ( empty( $brand ) || $brand === 'disabled' ) {
		} else {
			if ( ( is_plugin_active( 'perfect-woocommerce-brands/perfect-woocommerce-brands.php' )
				|| is_plugin_active( 'perfect-woocommerce-brands/main.php' )
				|| class_exists( 'Perfect_Woocommerce_Brands' ) ) && $brand === 'sfpwb' ) {
				$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'pwb-brand' );
				if ( $barnd_terms !== false ) {
					foreach ( $barnd_terms as $barnd_term ) {
						$tag_value = $barnd_term->name;
						break;
					}
				}
			} else if ( ( is_plugin_active( 'premmerce-woocommerce-brands/premmerce-brands.php' ) )
				&& ( $brand === 'premmercebrandsplugin' ) ) {
				$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'product_brand' );
				if ( $barnd_terms !== false ) {
					foreach ( $barnd_terms as $barnd_term ) {
						$tag_value = $barnd_term->name;
						break;
					}
				}
			} else if ( ( is_plugin_active( 'woocommerce-brands/woocommerce-brands.php' ) )
				&& ( $brand === 'woocommerce_brands' ) ) {
				$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'product_brand' );
				if ( $barnd_terms !== false ) {
					foreach ( $barnd_terms as $barnd_term ) {
						$tag_value = $barnd_term->name;
						break;
					}
				}
			} else if ( class_exists( 'woo_brands' ) && $brand === 'woo_brands' ) {
				$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'product_brand' );
				if ( $barnd_terms !== false ) {
					foreach ( $barnd_terms as $barnd_term ) {
						$tag_value = $barnd_term->name;
						break;
					}
				}
			} else if ( ( is_plugin_active( 'yith-woocommerce-brands-add-on/init.php' ) )
				&& ( $brand === 'yith_woocommerce_brands_add_on' ) ) {
				$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'yith_product_brand' );
				if ( $barnd_terms !== false ) {
					foreach ( $barnd_terms as $barnd_term ) {
						$tag_value = $barnd_term->name;
						break;
					}
				}
			} else if ( $brand == 'post_meta' ) {
				$brand_post_meta_id = common_option_get( 'xfavi_vendor_post_meta', false, $this->get_feed_id(), 'xfavi' );
				if ( get_post_meta( $this->get_product()->get_id(), $brand_post_meta_id, true ) !== '' ) {
					$brand_xml = get_post_meta( $this->get_product()->get_id(), $brand_post_meta_id, true );
					$tag_value = $brand_xml;
				}
			} else if ( $brand == 'default_value' ) {
				$brand_xml = common_option_get( 'xfavi_vendor_post_meta', false, $this->get_feed_id(), 'xfavi' );
				if ( $brand_xml !== '' ) {
					$tag_value = $brand_xml;
				}
			} else {
				$brand = (int) $brand;
				$tag_value = $this->get_offer()->get_attribute( wc_attribute_taxonomy_name_by_id( $brand ) );
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $brand ) );
				}
			}
		}

		$tag_value = apply_filters(
			'xfavi_f_variable_tag_value_brand',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'xfavi_f_variable_tag_name_brand',
				$tag_name,
				[ 
					'product' => $this->get_product(),
					'offer' => $this->get_offer()
				],
				$this->get_feed_id()
			);
			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'xfavi_f_variable_tag_brand',
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