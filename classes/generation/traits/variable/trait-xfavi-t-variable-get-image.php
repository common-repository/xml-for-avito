<?php
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
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Variable_Get_Image {
	public function get_image( $tag_name = 'Image', $result_xml = '' ) {
		$product = $this->product;
		$offer = $this->offer;
		$picture_xml = '';

		$thumb_xml = get_the_post_thumbnail_url( $offer->get_id(), 'full' );
		if ( empty ( $thumb_xml ) ) {
			// убираем default.png из фида
			$no_default_png_products = xfavi_optionGET( 'xfavi_no_default_png_products', $this->get_feed_id(), 'set_arr' );
			if ( ( $no_default_png_products === 'on' ) && ( ! has_post_thumbnail( $product->get_id() ) ) ) {
				$picture_xml = '';
			} else {
				$thumb_id = get_post_thumbnail_id( $product->get_id() );
				$thumb_url = wp_get_attachment_image_src( $thumb_id, 'full', true );
				$tag_value = $thumb_url[0]; /* урл оригинал миниатюры товара */
				$tag_value = get_from_url( $tag_value );
				$picture_xml = '<Image url="' . $tag_value . '"/>' . PHP_EOL;
				// $picture_xml = new Get_Paired_Tag($tag_name, $tag_value);
			}
		} else {
			$tag_value = get_from_url( $thumb_xml );
			$picture_xml = '<Image url="' . $tag_value . '"/>' . PHP_EOL;
			// $picture_xml = new Get_Paired_Tag($tag_name, $tag_value);
		}
		$picture_xml = apply_filters( 'xfavi_pic_variable_offer_filter', $picture_xml, $product, $offer, $this->get_feed_id() );
		// пропускаем вариации без картинок
		$xfavi_skip_products_without_pic = xfavi_optionGET( 'xfavi_skip_products_without_pic', $this->get_feed_id(), 'set_arr' );
		if ( ( $xfavi_skip_products_without_pic === 'on' ) && ( $picture_xml == '' ) ) {
			$this->add_skip_reason( [ 
				'offer_id' => $offer->get_id(),
				'reason' => __( 'Product has no images', 'xfavi' ),
				'post_id' => $offer->get_id(),
				'file' => 'trait-xfavi-t-variable-get-picture.php',
				'line' => __LINE__
			] );
			return '';
		}

		if ( $picture_xml !== '' ) {
			$result_xml .= '<Images>' . PHP_EOL . $picture_xml . '</Images>' . PHP_EOL;
		}
		// $result_xml = $picture_xml;

		//	$result_xml = xfavi_replace_domain($result_xml, $this->get_feed_id());
		$result_xml = apply_filters(
			'xfavi_f_variable_tag_picture',
			$result_xml,
			[ 
				'product' => $product,
				'offer' => $offer
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}