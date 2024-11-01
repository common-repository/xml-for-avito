<?php
/**
 * Traits Dimensions for simple products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   2.1.11
 * 
 * @version                 2.4.9 (18-09-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_product
 *                                      get_feed_id
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Simple_Get_Dimensions {
	/**
	 * Get dimensions tags
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * @param array $names_arr - Optional
	 * 
	 * @return string
	 */
	public function get_dimensions( $tag_name = 'dimensions', $result_xml = '',
		$names_arr = [ 
			'l_name' => 'Length',
			'w_name' => 'Width',
			'h_name' => 'Height',
			'l_name_for_delivery' => 'LengthForDelivery',
			'w_name_for_delivery' => 'WidthForDelivery',
			'h_name_for_delivery' => 'HeightForDelivery'
		] ) {
		// * к сожалению wc_get_dimension не всегда возвращает float и юзер может передать в размер что-то типа '13-18'
		// * потому юзаем gettype() === 'double'
		$length_xml = 0;
		$width_xml = 0;
		$height_xml = 0;

		$length = common_option_get( 'xfavi_length', false, $this->get_feed_id(), 'xfavi' );
		switch ( $length ) {
			case "disabled":
				$length_xml = '';
				break;
			case "woo_dimensions":
				if ( $this->get_product()->has_dimensions() ) {
					$length_xml = $this->get_product()->get_length();
					if ( ! empty( $length_xml ) && gettype( $length_xml ) === 'double' ) {
						$length_xml = round( wc_get_dimension( $length_xml, 'cm' ), 3 );
					}
				}
				break;
			default:
				$length = (int) $length;
				$length_xml = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $length ) );
		}

		$width = common_option_get( 'xfavi_width', false, $this->get_feed_id(), 'xfavi' );
		switch ( $width ) {
			case "disabled":
				$width_xml = '';
				break;
			case "woo_dimensions":
				if ( $this->get_product()->has_dimensions() ) {
					$width_xml = $this->get_product()->get_width();
					if ( ! empty( $width_xml ) && gettype( $width_xml ) === 'double' ) {
						$width_xml = round( wc_get_dimension( $width_xml, 'cm' ), 3 );
					}
				}
				break;
			default:
				$width = (int) $width;
				$width_xml = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $width ) );
		}

		$height = common_option_get( 'xfavi_height', false, $this->get_feed_id(), 'xfavi' );
		switch ( $height ) {
			case "disabled":
				$height_xml = '';
				break;
			case "woo_dimensions":
				if ( $this->get_product()->has_dimensions() ) {
					$height_xml = $this->get_product()->get_height();
					if ( ! empty( $height_xml ) && gettype( $height_xml ) === 'double' ) {
						$height_xml = round( wc_get_dimension( $height_xml, 'cm' ), 3 );
					}
				}
				break;
			default:
				$height = (int) $height;
				$height_xml = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $height ) );
		}

		if ( $length_xml > 0 ) {
			$result_xml .= new Get_Paired_Tag( $names_arr['l_name'], $length_xml );
			$result_xml .= new Get_Paired_Tag( 'Depth', $length_xml );
		}
		if ( $width_xml > 0 ) {
			$result_xml .= new Get_Paired_Tag( $names_arr['w_name'], $width_xml );
		}
		if ( $height_xml > 0 ) {
			$result_xml .= new Get_Paired_Tag( $names_arr['h_name'], $height_xml );
		}

		$length_xml = 0;
		$width_xml = 0;
		$height_xml = 0;
		$length = common_option_get( 'xfavi_length_for_delivery', false, $this->get_feed_id(), 'xfavi' );
		switch ( $length ) {
			case "disabled":
				$length_xml = '';
				break;
			case "woo_dimensions":
				if ( $this->get_product()->has_dimensions() ) {
					$length_xml = $this->get_product()->get_length();
					if ( ! empty( $length_xml ) && gettype( $length_xml ) === 'double' ) {
						$length_xml = round( wc_get_dimension( $length_xml, 'cm' ), 3 );
					}
				}
				break;
			default:
				$length = (int) $length;
				$length_xml = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $length ) );
		}

		$width = common_option_get( 'xfavi_width_for_delivery', false, $this->get_feed_id(), 'xfavi' );
		switch ( $width ) {
			case "disabled":
				$width_xml = '';
				break;
			case "woo_dimensions":
				if ( $this->get_product()->has_dimensions() ) {
					$width_xml = $this->get_product()->get_width();
					if ( ! empty( $width_xml ) && gettype( $width_xml ) === 'double' ) {
						$width_xml = round( wc_get_dimension( $width_xml, 'cm' ), 3 );
					}
				}
				break;
			default:
				$width = (int) $width;
				$width_xml = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $width ) );
		}

		$height = common_option_get( 'xfavi_height_for_delivery', false, $this->get_feed_id(), 'xfavi' );
		switch ( $height ) {
			case "disabled":
				$height_xml = '';
				break;
			case "woo_dimensions":
				if ( $this->get_product()->has_dimensions() ) {
					$height_xml = $this->get_product()->get_height();
					if ( ! empty( $height_xml ) && gettype( $height_xml ) === 'double' ) {
						$height_xml = round( wc_get_dimension( $height_xml, 'cm' ), 3 );
					}
				}
				break;
			default:
				$height = (int) $height;
				$height_xml = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $height ) );
		}

		if ( $length_xml > 0 ) {
			$result_xml .= new Get_Paired_Tag( $names_arr['l_name_for_delivery'], $length_xml );
		}
		if ( $width_xml > 0 ) {
			$result_xml .= new Get_Paired_Tag( $names_arr['w_name_for_delivery'], $width_xml );
		}
		if ( $height_xml > 0 ) {
			$result_xml .= new Get_Paired_Tag( $names_arr['h_name_for_delivery'], $height_xml );
		}

		$result_xml = apply_filters(
			'xfavi_f_simple_tag_dimensions',
			$result_xml,
			[ 'product' => $this->get_product() ],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}