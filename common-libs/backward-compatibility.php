<?php defined( 'ABSPATH' ) || exit;
/*
Version: 2.3.0
Date: 04-04-2024
Author: Maxim Glazunov
Author URI: https://icopydoc.ru 
License: GPLv2
Description: This code helps ensure backward compatibility with older versions of the plugin.
*/

/**
 * Функция обеспечивает правильность данных, чтобы не валились ошибки и не зависало
 * 
 * @since 0.1.0
 * 
 * @return bool
 */
function validation_variable( $args, $p = 'xfavip' ) {
	$is_string = common_option_get( 'woo_' . 'hook_isc' . $p );
	if ( $is_string == '202' && $is_string !== $args ) {
		return true;
	} else {
		return false;
	}
}

/**
 * @since 2.2.0 // TODO: На удаление с версии 2.2.0
 * 
 * С версии 1.0.0
 * Возвращает URL без get-параметров или возвращаем только get-параметры
 */
function xfavi_deleteGET( $url, $whot = 'url' ) {
	$url = str_replace( "&amp;", "&", $url ); // Заменяем сущности на амперсанд, если требуется
	list( $url_part, $get_part ) = array_pad( explode( "?", $url ), 2, "" ); // Разбиваем URL на 2 части: до знака ? и после
	if ( $whot == 'url' ) {
		return $url_part; // Возвращаем URL без get-параметров (до знака вопроса)
	} else if ( $whot == 'get' ) {
		return $get_part; // Возвращаем get-параметры (без знака вопроса)
	} else {
		return false;
	}
}
/**
 * @since 2.2.0 // TODO: На удаление с версии 2.3.0
 */
function xfavi_error_log( $text, $i ) {
	$xfavi_keeplogs = xfavi_optionGET( 'xfavi_keeplogs', 'set_arr' );
	if ( $xfavi_keeplogs !== 'on' ) {
		return;
	}
	$upload_dir = (object) wp_get_upload_dir();
	$name_dir = $upload_dir->basedir . "/xml-for-avito";
	// подготовим массив для записи в файл логов
	if ( is_array( $text ) ) {
		$r = xfavi_array_to_log( $text );
		unset( $text );
		$text = $r;
	}
	if ( is_dir( $name_dir ) ) {
		$filename = $name_dir . '/xml-for-avito.log';
		file_put_contents( $filename, '[' . date( 'Y-m-d H:i:s' ) . '] ' . $text . PHP_EOL, FILE_APPEND );
	} else {
		if ( ! mkdir( $name_dir ) ) {
			error_log( 'Нет папки xfavi! И создать не вышло! $name_dir =' . $name_dir . '; Файл: functions.php; Строка: ' . __LINE__, 0 );
		} else {
			error_log( 'Создали папку xfavi!; Файл: functions.php; Строка: ' . __LINE__, 0 );
			$filename = $name_dir . '/xml-for-avito.log';
			file_put_contents( $filename, '[' . date( 'Y-m-d H:i:s' ) . '] ' . $text . PHP_EOL, FILE_APPEND );
		}
	}
	return;
}

/**
 * С версии 1.0.0 // TODO: На удаление с версии 2.3.0
 * Позволяте писать в логи массив /wp-content/uploads/xml-for-avito/xml-for-avito.log
 */
function xfavi_array_to_log( $text, $i = 0, $res = '' ) {
	$tab = '';
	for ( $x = 0; $x < $i; $x++ ) {
		$tab = '---' . $tab;
	}
	if ( is_array( $text ) ) {
		$i++;
		foreach ( $text as $key => $value ) {
			if ( is_array( $value ) ) { // массив
				$res .= PHP_EOL . $tab . "[$key] => ";
				$res .= $tab . xfavi_array_to_log( $value, $i );
			} else { // не массив
				$res .= PHP_EOL . $tab . "[$key] => " . $value;
			}
		}
	} else {
		$res .= PHP_EOL . $tab . $text;
	}
	return $res;
}

/**
 * @since 1.3.0
 * @version 2.2.0 (22-03-2024) // TODO: На удаление с версии 2.3.0
 * @see https://www.php.net/manual/ru/class.simplexmlelement.php
 * 
 * @param WP_Post $post - Required 
 * @param string $result - Optional
 * 
 * @return string
 */
function xfavi_option_construct_product( $post, $result = '' ) {
	$xml_url = plugin_dir_path( __FILE__ ) . 'data/goodstype.xml';
	$xml_string = file_get_contents( $xml_url );
	$xml_object = new SimpleXMLElement( $xml_string ); // simplexml_load_string($xml_string);

	$xfavi_goods_type = esc_attr( get_post_meta( $post->ID, '_xfavi_goods_type', 1 ) );

	foreach ( $xml_object->children() as $second_gen ) {
		if ( count( $second_gen->children() ) > 0 ) {
			$result .= new Get_Open_Tag( 'optgroup', [ 'label' => $second_gen['name'] ] );
			foreach ( $second_gen->children() as $third_gen ) {
				if ( $xfavi_goods_type == str_replace( ' ', '_', $third_gen['name'] ) ) {
					$selected = 'selected';
				} else {
					$selected = '';
				}
				$result .= sprintf( '<option value="%s" %s>%s</option>%s',
					str_replace( ' ', '_', $third_gen['name'] ),
					$selected,
					$third_gen['name'],
					PHP_EOL
				);
			}
			$result .= new Get_Closed_Tag( 'optgroup' );
		}
	}
	return $result;
}

/**
 * @since 1.0.0 // TODO: На удаление с версии 2.3.0
 * 
 * Создает tmp файл-кэш товара
 */
function xfavi_wf( $result_xml, $postId, $feed_id = '1', $ids_in_xml = '' ) {
	$upload_dir = (object) wp_get_upload_dir();
	$name_dir = $upload_dir->basedir . '/xml-for-avito/feed' . $feed_id;
	if ( ! is_dir( $name_dir ) ) {
		error_log( 'WARNING: Папкт $name_dir =' . $name_dir . ' нет; Файл: functions.php; Строка: ' . __LINE__, 0 );
		if ( ! mkdir( $name_dir ) ) {
			error_log( 'ERROR: Создать папку $name_dir =' . $name_dir . ' не вышло; Файл: functions.php; Строка: ' . __LINE__, 0 );
		}
	}
	if ( is_dir( $name_dir ) ) {
		$filename = $name_dir . '/' . $postId . '.tmp';
		$fp = fopen( $filename, "w" );
		fwrite( $fp, $result_xml ); // записываем в файл текст
		fclose( $fp ); // закрываем

		$filename = $name_dir . '/' . $postId . '-in.tmp';
		$fp = fopen( $filename, "w" );
		fwrite( $fp, $ids_in_xml );
		fclose( $fp );
	} else {
		error_log( 'ERROR: Нет папки xfavi! $name_dir =' . $name_dir . '; Файл: functions.php; Строка: ' . __LINE__, 0 );
	}
}