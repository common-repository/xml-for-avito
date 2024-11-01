<?php
/**
 * Traits Image for simple products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.4.3 (16-06-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    Get_Paired_Tag
 *                                      Get_Open_Tag
 *                          traits:     
 *                          methods:    get_feed_id
 *                                      get_product
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Simple_Get_Image {
	/**
	 * Summary of get_image
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_image( $tag_name = 'Image', $result_xml = '' ) {
		$tag_value = '';

		// убираем default.png из фида
		$no_default_png_products = common_option_get( 'xfavi_no_default_png_products', false, $this->get_feed_id(), 'xfavi' );
		if ( ( $no_default_png_products === 'on' ) && ( ! has_post_thumbnail( $this->get_product()->get_id() ) ) ) {
			$tag_value = '';
		} else {
			$thumb_id = get_post_thumbnail_id( $this->get_product()->get_id() );
			$thumb_url = wp_get_attachment_image_src( $thumb_id, 'full', true );
			$tag_value = $thumb_url[0]; // урл оригинал миниатюры товара
			$tag_value = get_from_url( $tag_value );
			$tag_value = new Get_Open_Tag( $tag_name, [ 'url' => $tag_value ], true );
		}
		$tag_value = apply_filters( 'xfavi_pic_simple_offer_filter', $tag_value, $this->get_product(), $this->get_feed_id() );

		// пропускаем товары без картинок
		$skip_products_without_pic = common_option_get( 'xfavi_skip_products_without_pic', false, $this->get_feed_id(), 'xfavi' );
		if ( ( $skip_products_without_pic === 'on' ) && ( $tag_value == '' ) ) {
			$this->add_skip_reason( [ 
				'offer_id' => $this->get_product()->get_id(),
				'reason' => __( 'У товара нет изображений', 'xml-for-avito' ),
				'post_id' => $this->get_product()->get_id(),
				'file' => 'trait-xfavi-t-simple-get-picture.php',
				'line' => __LINE__
			] );
			return '';
		}

		if ( ! empty( $tag_value ) ) {
			$result_xml .= '<Images>' . PHP_EOL . $tag_value . '</Images>' . PHP_EOL;
		}

		$result_xml = apply_filters(
			'xfavi_f_simple_tag_picture',
			$result_xml,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}