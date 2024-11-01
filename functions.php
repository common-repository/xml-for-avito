<?php defined( 'ABSPATH' ) || exit;
/**
 * @since 1.0.0
 *
 * @param string $optName (require)
 * @param string $value (require)
 * @param string $n (not require)
 * @param string $autoload (not require) (@since v1.3.7)
 * @param string $type (not require) (@since 1.4.0)
 * @param string $source_settings_name (not require) (@since 1.4.0)
 *
 * @return true/false
 * Возвращает то, что может быть результатом update_blog_option, update_option
 */
function xfavi_optionUPD( $option_name, $value = '', $n = '', $autoload = 'yes', $type = '', $source_settings_name = '' ) {
	if ( $option_name == '' ) {
		return false;
	}
	switch ( $type ) {
		case "set_arr":
			if ( $n === '' ) {
				$n = '1';
			}
			$xfavi_settings_arr = xfavi_optionGET( 'xfavi_settings_arr' );
			$xfavi_settings_arr[ $n ][ $option_name ] = $value;
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), 'xfavi_settings_arr', $xfavi_settings_arr );
			} else {
				return update_option( 'xfavi_settings_arr', $xfavi_settings_arr, $autoload );
			}
		case "custom_set_arr":
			if ( $source_settings_name === '' ) {
				return false;
			}
			if ( $n === '' ) {
				$n = '1';
			}
			$xfavi_settings_arr = xfavi_optionGET( $source_settings_name );
			$xfavi_settings_arr[ $n ][ $option_name ] = $value;
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), $source_settings_name, $xfavi_settings_arr );
			} else {
				return update_option( $source_settings_name, $xfavi_settings_arr, $autoload );
			}
		default:
			if ( $n === '1' ) {
				$n = '';
			}
			$option_name = $option_name . $n;
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), $option_name, $value );
			} else {
				return update_option( $option_name, $value, $autoload );
			}
	}
}
/**
 * @since 1.0.0
 *
 * @param string $optName (require)
 * @param string $n (not require)
 *
 * @return Значение опции или false
 * Возвращает то, что может быть результатом get_blog_option, get_option
 */
function xfavi_optionGET( $option_name, $n = '', $type = '', $source_settings_name = '' ) {
	if ( $option_name == 'xfavi_status_sborki' && $n == '1' ) {
		if ( is_multisite() ) {
			return get_blog_option( get_current_blog_id(), $option_name );
		} else {
			return get_option( $option_name );
		}
	}

	if ( defined( 'xfavip_VER' ) ) {
		$pro_ver_number = xfavip_VER;
	} else {
		$pro_ver_number = '1.0.5';
	}
	if ( version_compare( $pro_ver_number, '1.1.0', '<' ) ) { // если версия PRO ниже 1.1.0
		if ( $option_name === 'xfavip_compare_value' ) {
			if ( $n === '1' ) {
				$n = '';
			}
			$option_name = $option_name . $n;
			if ( is_multisite() ) {
				return get_blog_option( get_current_blog_id(), $option_name );
			} else {
				return get_option( $option_name );
			}
		}
		if ( $option_name === 'xfavip_compare' ) {
			if ( $n === '1' ) {
				$n = '';
			}
			$option_name = $option_name . $n;
			if ( is_multisite() ) {
				return get_blog_option( get_current_blog_id(), $option_name );
			} else {
				return get_option( $option_name );
			}
		}
	}

	if ( $option_name == '' ) {
		return false;
	}
	switch ( $type ) {
		case "set_arr":
			if ( $n === '' ) {
				$n = '1';
			}
			$xfavi_settings_arr = xfavi_optionGET( 'xfavi_settings_arr' );
			if ( isset( $xfavi_settings_arr[ $n ][ $option_name ] ) ) {
				return $xfavi_settings_arr[ $n ][ $option_name ];
			} else {
				return false;
			}
		case "custom_set_arr":
			if ( $source_settings_name === '' ) {
				return false;
			}
			if ( $n === '' ) {
				$n = '1';
			}
			$xfavi_settings_arr = xfavi_optionGET( $source_settings_name );
			if ( isset( $xfavi_settings_arr[ $n ][ $option_name ] ) ) {
				return $xfavi_settings_arr[ $n ][ $option_name ];
			} else {
				return false;
			}
		case "for_update_option":
			if ( $n === '1' ) {
				$n = '';
			}
			$option_name = $option_name . $n;
			if ( is_multisite() ) {
				return get_blog_option( get_current_blog_id(), $option_name );
			} else {
				return get_option( $option_name );
			}
		default:
			if ( $n === '1' ) {
				$n = '';
			}
			$option_name = $option_name . $n;
			if ( is_multisite() ) {
				return get_blog_option( get_current_blog_id(), $option_name );
			} else {
				return get_option( $option_name );
			}
	}
}

/**
 * @since 1.3.0
 * @version 2.4.11 (29-09-2024)
 * @see https://www.php.net/manual/ru/class.simplexmlelement.php
 * 
 * @param WP_Term $term - Required 
 * @param int $cur_level - Optional
 * @param SimpleXMLElement|null $xml_object - Optional
 * @param string|null $parent_name - Optional
 * @param array $result_arr - Optional
 *
 * @return array
 */
function xfavi_option_construct(
	$term,
	$cur_level = 0,
	$xml_object = null,
	$parent_name = null,
	$result_arr = [ 0 => '', 1 => '', 2 => '', 3 => '', 4 => '' ]
) {
	if ( null === $xml_object ) {
		$xml_url = plugin_dir_path( __FILE__ ) . 'data/goodstype.xml';
		$xml_string = file_get_contents( $xml_url );
		$xml_object = new SimpleXMLElement( $xml_string );
	}
	switch ( $cur_level ) {
		case 0:
			$level_term_meta_val = esc_attr( get_term_meta( $term->term_id, 'xfavi_avito_product_category', true ) );
			break;
		case 1:
			$level_term_meta_val = esc_attr( get_term_meta( $term->term_id, 'xfavi_default_goods_type', true ) );
			break;
		case 2:
			$level_term_meta_val = esc_attr( get_term_meta( $term->term_id, 'xfavi_default_goods_subtype', true ) );
			break;
		case 3:
			$level_term_meta_val = esc_attr( get_term_meta( $term->term_id, 'xfavi_default_another_type', true ) );
			break;
		default:
			$level_term_meta_val = '';
	}
	foreach ( $xml_object as $children_xml_object ) {
		if ( 0 === $cur_level ) {
			$parent_name = $children_xml_object['avito_standart'];
		}
		if ( $level_term_meta_val == str_replace( ' ', '_', $children_xml_object['name'] ) ) {
			$selected = 'selected';
		} else {
			$selected = '';
		}
		if ( isset( $children_xml_object['type_name'] ) ) {
			$type_name = sprintf( ' (%s)', $children_xml_object['type_name'] );
		} else {
			$type_name = '';
		}
		$result_arr[ $cur_level ] .= sprintf( '<option value="%1$s" data-chained="%2$s" %3$s>%4$s%5$s</option>%6$s',
			str_replace( ' ', '_', $children_xml_object['name'] ),
			$parent_name,
			$selected,
			$children_xml_object['name'],
			$type_name,
			PHP_EOL
		);
		if ( count( $children_xml_object->children() ) > 0 && $cur_level < 4 ) {
			$result_arr = xfavi_option_construct(
				$term,
				$cur_level + 1,
				$children_xml_object->children(),
				str_replace( ' ', '_', $children_xml_object['name'] ),
				$result_arr
			);
		} else {
			if ( $cur_level < 4 ) {
				if ( $level_term_meta_val == 'disabled' ) {
					$selected = 'selected';
				} else {
					$selected = '';
				}
				$result_arr[ $cur_level + 1 ] .= sprintf( '<option value="%1$s" data-chained="%2$s" %3$s>%4$s</option>%5$s',
					'disabled',
					$parent_name,
					$selected,
					'Отключено',
					PHP_EOL
				);
			}
		}
	}
	return $result_arr;
}

/**
 * Получает первый фид. Используется на случай если get-параметр numFeed не указан
 * 
 * @since 1.5.0
 * @version 2.2.0 (22-03-2024)
 *
 * @return string (feed ID or (string)'')
 */
function xfavi_get_first_feed_id() {
	$xfavi_settings_arr = univ_option_get( 'xfavi_settings_arr' );
	if ( ! empty( $xfavi_settings_arr ) ) {
		return (string) array_key_first( $xfavi_settings_arr );
	} else {
		return (string) '';
	}
}

if ( ! function_exists( 'get_several_tag' ) ) {
	/**
	 * Splits the tag value into several parts using the ` | ` separator
	 * 
	 * @since 2.1.4
	 * 
	 * @param string $tag_name - Required
	 * @param string $tag_value - Required
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	function get_several_tag( $tag_name, $tag_value, $result_xml = '' ) {
		$elements = explode( " | ", $tag_value );
		if ( count( $elements ) > 1 ) {
			$result_xml .= new Get_Open_Tag( $tag_name );
			foreach ( $elements as $element ) {
				$result_xml .= new Get_Paired_Tag( 'Option', $element );
			}
			$result_xml .= new Get_Closed_Tag( $tag_name );
		} else if ( count( $elements ) === (int) 1 ) {
			$result_xml .= new Get_Paired_Tag( $tag_name, $elements[0] );
		}
		return $result_xml;
	}
}

if ( ! function_exists( 'forced_cron' ) ) {
	/**
	 * Forced to start wp-cron.php if CRON tasks are overdue by more than `85` seconds
	 * 
	 * @since 2.5.0
	 * 
	 * @param int $sec
	 * 
	 * @return void
	 */
	function forced_cron( $sec = -80 ) {
		$cron_arr = _get_cron_array();
		if ( ! empty( $cron_arr ) ) {
			$first_key = array_key_first( $cron_arr );
			if ( $sec > $first_key - current_time( 'timestamp', 1 ) ) {
				wp_remote_get( home_url() . '/wp-cron.php' );
			}
		}
	}
}