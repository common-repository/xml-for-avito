<?php
/**
 * Traits for different classes
 *
 * @package                 XML for Avito
 * @subpackage              
 * 
 * @version                 2.3.0 (04-04-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 */
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Get_Product {
	/**
	 * WooCommerce product object
	 * @var WC_Product
	 */
	protected $product;

	/**
	 * Get WooCommerce product object
	 * 
	 * @return WC_Product
	 */
	protected function get_product() {
		return $this->product;
	}

	/**
	 * Checks whether this parameter is set at the product level
	 * 
	 * @param string $meta_key - Required - The meta key to retrieve
	 * 
	 * @return bool `true` - if this parameter is not set at the product level; `false` - in other cases
	 */
	protected function is_default_value( $meta_key ) {
		if ( get_post_meta( $this->get_product()->get_id(), $meta_key, true ) == ''
			|| get_post_meta( $this->get_product()->get_id(), $meta_key, true ) === 'default' ) {
			return true;
		} else {
			return false;
		}
	}
}

trait XFAVI_T_Get_Feed_Id {
	/**
	 * Feed ID
	 * @var string
	 */
	protected $feed_id;

	/**
	 * Get feed ID
	 * 
	 * @return string
	 */
	protected function get_feed_id() {
		return $this->feed_id;
	}
}

trait XFAVI_T_Get_Post_Id {
	/**
	 * Post ID
	 * @var int|string
	 */
	protected $post_id;

	/**
	 * Get post ID
	 * 
	 * @return int|string
	 */
	protected function get_post_id() {
		return $this->post_id;
	}
}

trait XFAVI_T_Get_Skip_Reasons_Arr {
	/**
	 * Skip reasons array
	 * @var array
	 */
	protected $skip_reasons_arr = [];

	/**
	 * Set(add) skip reasons
	 *
	 * @param string $v
	 */
	public function set_skip_reasons_arr( $v ) {
		$this->skip_reasons_arr[] = $v;
	}

	/**
	 * Get skip reasons array
	 * 
	 * @return array
	 */
	public function get_skip_reasons_arr() {
		return $this->skip_reasons_arr;
	}
}