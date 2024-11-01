<?php
/**
 * Traits Common_Ad for simple products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.2.0 (22-03-2024)
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
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Common_Ad {
	/**
	 * Summary of get_common_ad
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_common_ad( $result_xml = '' ) {
		$xfavi_listing_fee = common_option_get( 'xfavi_listing_fee', false, $this->get_feed_id(), 'xfavi' );
		if ( empty ( $xfavi_listing_fee ) || $xfavi_listing_fee == 'disabled' ) {
		} else {
			$result_xml = new Get_Paired_Tag( 'ListingFee', $xfavi_listing_fee );
		}

		$result_xml = apply_filters(
			'xfavi_f_common_ad',
			$result_xml,
			[ 'product' => $this->get_product() ],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}