<?php
/**
 * This class starts feed generation
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.4.12 (03-10-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @param       string
 *
 * @depends                 classes:    WP_Query
 *                          traits:     
 *                          methods:    
 *                          functions:  
 *                          constants:  XFAVI_SITE_UPLOADS_DIR_PATH
 *                                      XFAVI_PLUGIN_UPLOADS_DIR_PATH
 *                                      XFAVI_PLUGIN_UPLOADS_DIR_URL
 *                          actions:    
 *                          filters:    
 *
 */
defined( 'ABSPATH' ) || exit;

class XFAVI_Generation_XML {
	/**
	 * Prefix
	 * @var string
	 */
	private $pref = 'xfavi';
	/**
	 * Feed ID
	 * @var string
	 */
	protected $feed_id;
	/**
	 * XML code
	 * @var string
	 */
	protected $result_xml = '';

	/**
	 * Starts feed generation
	 * 
	 * @param string|int $feed_id - Required
	 */
	public function __construct( $feed_id ) {
		$this->feed_id = (string) $feed_id;
	}

	/**
	 * Write file feed tmp
	 *
	 * @param string $result_xml - контент, который записываем в файл feed-avito-0-tmp.xml
	 * @param string $mode - тип доступа, который вы запрашиваете у потока
	 * 
	 * @return bool
	 */
	public function write_file_feed_tmp( $result_xml, $mode ) {
		$filename = urldecode( common_option_get( 'xfavi_file_file', false, $this->get_feed_id(), 'xfavi' ) );
		if ( empty( $filename ) ) {
			$filename = XFAVI_SITE_UPLOADS_DIR_PATH . "/" . $this->get_prefix_feed() . "feed-avito-0-tmp.xml";
		}

		// если временный файл в папке загрузок есть
		if ( file_exists( $filename ) ) {
			if ( ! $handle = fopen( $filename, $mode ) ) {
				new XFAVI_Error_Log( sprintf(
					'FEED № %1$s; Не могу открыть временный файл %2$s; Файл: %3$s; Строка: %4$s',
					$this->get_feed_id(),
					$filename,
					'class-xfavi-generation-xml.php',
					__LINE__
				) );
			}
			if ( false === fwrite( $handle, $result_xml ) ) {
				new XFAVI_Error_Log( sprintf(
					'FEED № %1$s; Не могу произвести запись во временный файл %2$s; Файл: %3$s; Строка: %4$s',
					$this->get_feed_id(),
					$filename,
					'class-xfavi-generation-xml.php',
					__LINE__
				) );
			} else {
				new XFAVI_Error_Log( sprintf(
					'FEED № %1$s; Временный файл %2$s успешно записан; Файл: %3$s; Строка: %4$s',
					$this->get_feed_id(),
					$filename,
					'class-xfavi-generation-xml.php',
					__LINE__
				) );
				return true;
			}
			fclose( $handle );
		} else {
			new XFAVI_Error_Log( sprintf(
				'FEED № %1$s; Временного файла $filename = %2$s еще нет; Файл: %3$s; Строка: %4$s',
				$this->get_feed_id(),
				$filename,
				'class-xfavi-generation-xml.php',
				__LINE__
			) );
			// файла еще нет. попытаемся создать
			if ( is_multisite() ) {
				$tmp_filename = $this->get_prefix_feed() . 'feed-avito-' . get_current_blog_id() . '-tmp.xml';
			} else {
				$tmp_filename = $this->get_prefix_feed() . 'feed-avito-0-tmp.xml';
			}
			// загружаем временный файл в папку загрузок
			$upload = wp_upload_bits( $tmp_filename, null, $result_xml );
			/**
			 *	для работы с csv или xml требуется в плагине разрешить загрузку таких файлов
			 *	$upload['file'] => '/var/www/wordpress/wp-content/uploads/2010/03/feed-xml.xml', // путь
			 *	$upload['url'] => 'http://site.ru/wp-content/uploads/2010/03/feed-xml.xml', // урл
			 *	$upload['error'] => false, // сюда записывается сообщение об ошибке в случае ошибки
			 */
			// проверим получилась ли запись
			if ( $upload['error'] ) {
				new XFAVI_Error_Log( sprintf(
					'FEED № %1$s; Ура! Запись вызвала ошибку: %2$s; Файл: %3$s; Строка: %4$s',
					$this->get_feed_id(),
					$upload['error'],
					'class-xfavi-generation-xml.php',
					__LINE__
				) );
			} else {
				new XFAVI_Error_Log( sprintf(
					'FEED № %1$s; Запись удалась! Путь файла: %2$s, УРЛ файла: %3$s; Файл: %4$s; Строка: %5$s',
					$this->get_feed_id(),
					$upload['file'],
					$upload['url'],
					'class-xfavi-generation-xml.php',
					__LINE__
				) );
				xfavi_optionUPD( 'xfavi_file_file', urlencode( $upload['file'] ), $this->get_feed_id(), 'yes', 'set_arr' );
				return true;
			}
		}
		return false;
	}

	/**
	 * Gluing cache files into a single feed
	 * 
	 * @param array $id_arr
	 * 
	 * @return void
	 */
	public function gluing( $id_arr ) {
		/**	
		 * $id_arr[$i]['ID'] - ID товара
		 * $id_arr[$i]['post_modified_gmt'] - Время обновления карточки товара
		 * global $wpdb;
		 * $res = $wpdb->get_results("SELECT ID, post_modified_gmt FROM $wpdb->posts WHERE post_type = 'product' AND post_status = 'publish'");	
		 */
		$name_dir = XFAVI_SITE_UPLOADS_DIR_PATH . '/xml-for-avito/feed' . $this->get_feed_id();
		if ( ! is_dir( $name_dir ) ) {
			if ( ! mkdir( $name_dir ) ) {
				error_log(
					sprintf(
						'FEED № %s; ERROR: Нет папки xml-for-avito! И создать не вышло! $name_dir = %s; Файл: %s; Строка: %s',
						$this->get_feed_id(),
						$name_dir,
						'class-xfavi-generation-xml.php',
						__LINE__
						, 0 )
				);
			} else {
				error_log(
					sprintf(
						'FEED № %s; Создали папку $name_dir = %s; Файл: %s; Строка: %s',
						$this->get_feed_id(),
						$name_dir,
						'class-xfavi-generation-xml.php',
						__LINE__
						, 0 )
				);
			}
		}

		/** 
		 *	этот блок исправляет потенциальную проблему изменения относительных путей типа:
		 *	/home/c/can/can.beget.tech/public_html/wp-content/uploads/xml-for-avito/feed2/ids_in_xml.tmp 
		 *	/home/c/can/canpower.ru/public_html/wp-content/uploads/xml-for-avito/feed2/ids_in_xml.tmp
		 **/
		$xfavi_file_ids_in_xml = urldecode( common_option_get( 'xfavi_file_ids_in_xml', false, $this->get_feed_id(), 'xfavi' ) );
		if ( empty( $xfavi_file_ids_in_xml ) ||
			$xfavi_file_ids_in_xml !== XFAVI_PLUGIN_UPLOADS_DIR_PATH . '/feed' . $this->get_feed_id() . '/ids_in_xml.tmp'
		) { // если не указан адрес файла с id-шниками или они не равны
			$xfavi_file_ids_in_xml = XFAVI_PLUGIN_UPLOADS_DIR_PATH . '/feed' . $this->get_feed_id() . '/ids_in_xml.tmp';
			xfavi_optionUPD( 'xfavi_file_ids_in_xml', urlencode( $xfavi_file_ids_in_xml ), $this->get_feed_id(), 'yes', 'set_arr' );
		}

		$xfavi_date_save_set = common_option_get( 'xfavi_date_save_set', false, $this->get_feed_id(), 'xfavi' );
		clearstatcache(); // очищаем кэш дат файлов

		foreach ( $id_arr as $product ) {
			$filename = $name_dir . '/' . $product['ID'] . '.tmp';
			$filenameIn = $name_dir . '/' . $product['ID'] . '-in.tmp'; /* с версии 2.0.0 */
			new XFAVI_Error_Log( 'FEED № ' . $this->get_feed_id() . '; RAM ' . round( memory_get_usage() / 1024, 1 ) . ' Кб. ID товара/файл = ' . $product['ID'] . '.tmp; Файл: class-xfavi-generation-xml.php; Строка: ' . __LINE__ );
			if ( is_file( $filename ) && is_file( $filenameIn ) ) { // if (file_exists($filename)) {
				$last_upd_file = filemtime( $filename ); // 1318189167			
				if ( ( $last_upd_file < strtotime( $product['post_modified_gmt'] ) )
					|| ( $xfavi_date_save_set > $last_upd_file ) ) {
					// Файл кэша обновлен раньше чем время модификации товара
					// или файл обновлен раньше чем время обновления настроек фида
					new XFAVI_Error_Log( 'FEED № ' . $this->get_feed_id() . '; NOTICE: Файл кэша ' . $filename . ' обновлен РАНЬШЕ чем время модификации товара или время сохранения настроек фида! Файл: class-xfavi-generation-xml.php; Строка: ' . __LINE__ );
					$result_get_unit_obj = new XFAVI_Get_Unit( $product['ID'], $this->get_feed_id() );
					$result_xml = $result_get_unit_obj->get_result();
					$stock_xml = $result_get_unit_obj->get_stock_xml();
					$ids_in_xml = $result_get_unit_obj->get_ids_in_xml();

					new XFAVI_Write_File( $result_xml, [ 'file_name' => $product['ID'] ], $this->get_feed_id() );
					new XFAVI_Write_File( $stock_xml, [ 'file_name' => $product['ID'] . '-stock' ], $this->get_feed_id() );
					new XFAVI_Write_File( $ids_in_xml, [ 'file_name' => $product['ID'] . '-in' ], $this->get_feed_id() );
					new XFAVI_Error_Log( 'FEED № ' . $this->get_feed_id() . '; Обновили кэш товара. Файл: class-xfavi-generation-xml.php; Строка: ' . __LINE__ );
					file_put_contents( $xfavi_file_ids_in_xml, $ids_in_xml, FILE_APPEND );
				} else {
					// Файл кэша обновлен позже чем время модификации товара
					// или файл обновлен позже чем время обновления настроек фида
					new XFAVI_Error_Log( 'FEED № ' . $this->get_feed_id() . '; NOTICE: Файл кэша ' . $filename . ' обновлен ПОЗЖЕ чем время модификации товара или время сохранения настроек фида; Файл: class-xfavi-generation-xml.php; Строка: ' . __LINE__ );
					new XFAVI_Error_Log( 'FEED № ' . $this->get_feed_id() . '; Пристыковываем файл кэша без изменений; Файл: class-xfavi-generation-xml.php; Строка: ' . __LINE__ );
					$result_xml = file_get_contents( $filename );
					$ids_in_xml = file_get_contents( $filenameIn );
					file_put_contents( $xfavi_file_ids_in_xml, $ids_in_xml, FILE_APPEND );
				}
			} else { // Файла нет
				new XFAVI_Error_Log( 'FEED № ' . $this->get_feed_id() . '; NOTICE: Файла кэша товара ' . $filename . ' ещё нет! Создаем... Файл: class-xfavi-generation-xml.php; Строка: ' . __LINE__ );
				$result_get_unit_obj = new XFAVI_Get_Unit( $product['ID'], $this->get_feed_id() );
				$result_xml = $result_get_unit_obj->get_result();
				$stock_xml = $result_get_unit_obj->get_stock_xml();
				$ids_in_xml = $result_get_unit_obj->get_ids_in_xml();

				new XFAVI_Write_File( $result_xml, [ 'file_name' => $product['ID'] ], $this->get_feed_id() );
				new XFAVI_Write_File( $stock_xml, [ 'file_name' => $product['ID'] . '-stock' ], $this->get_feed_id() );
				new XFAVI_Write_File( $ids_in_xml, [ 'file_name' => $product['ID'] . '-in' ], $this->get_feed_id() );
				new XFAVI_Error_Log( 'FEED № ' . $this->get_feed_id() . '; Создали кэш товара. Файл: class-xfavi-generation-xml.php; Строка: ' . __LINE__ );
				file_put_contents( $xfavi_file_ids_in_xml, $ids_in_xml, FILE_APPEND );
			}
		}
	} // end function gluing()

	/**
	 * Summary of clear_file_ids_in_xml
	 * 
	 * @param string $feed_id
	 * 
	 * @return void
	 */
	public function clear_file_ids_in_xml( $feed_id ) {
		$xfavi_file_ids_in_xml = urldecode( xfavi_optionGET( 'xfavi_file_ids_in_xml', $feed_id, 'set_arr' ) );
		if ( is_file( $xfavi_file_ids_in_xml ) ) {
			new XFAVI_Error_Log( sprintf(
				'FEED № %1$s; NOTICE: %2$s = %3$s; Файл: %4$s; Строка: %5$s',
				$this->get_feed_id(),
				'Обнуляем файл $xfavi_file_ids_in_xml',
				$xfavi_file_ids_in_xml,
				'class-xfavi-generation-xml.php',
				__LINE__
			) );
			file_put_contents( $xfavi_file_ids_in_xml, '' );
		} else {
			new XFAVI_Error_Log( sprintf(
				'FEED № %1$s; WARNING: %2$s = %3$s. %4$s; Файл: %5$s; Строка: %6$s',
				$this->get_feed_id(),
				'Нет файла c idшниками $xfavi_file_ids_in_xml',
				$xfavi_file_ids_in_xml,
				'Создадим пустой',
				'class-xfavi-generation-xml.php',
				__LINE__
			) );
			$xfavi_file_ids_in_xml = XFAVI_PLUGIN_UPLOADS_DIR_PATH . '/feed' . $feed_id . '/ids_in_xml.tmp';
			$res = file_put_contents( $xfavi_file_ids_in_xml, '' );
			if ( false !== $res ) {
				new XFAVI_Error_Log( sprintf(
					'FEED № %1$s; NOTICE: %2$s = %3$s. %4$s; Файл: %5$s; Строка: %6$s',
					$this->get_feed_id(),
					'Файл c idшниками $xfavi_file_ids_in_xml',
					$xfavi_file_ids_in_xml,
					'успешно создан',
					'class-xfavi-generation-xml.php',
					__LINE__
				) );
				xfavi_optionUPD( 'xfavi_file_ids_in_xml', urlencode( $xfavi_file_ids_in_xml ), $feed_id, 'yes', 'set_arr' );
			} else {
				new XFAVI_Error_Log( sprintf(
					'FEED № %1$s; ERROR: %2$s = %3$s; Файл: %4$s; Строка: %5$s',
					$this->get_feed_id(),
					'Ошибка создания файла $xfavi_file_ids_in_xml',
					$xfavi_file_ids_in_xml,
					'class-xfavi-generation-xml.php',
					__LINE__
				) );
			}
		}
	}

	/**
	 * Summary of run
	 * 
	 * @return void
	 */
	public function run() {
		$result_xml = '';

		$step_export = (int) common_option_get( 'xfavi_step_export', false, $this->get_feed_id(), 'xfavi' );
		$status_sborki = (int) xfavi_optionGET( 'xfavi_status_sborki', $this->get_feed_id() ); // файл уже собран. На всякий случай отключим крон сборки

		new XFAVI_Error_Log( sprintf(
			'FEED № %1$s; %2$s %3$s; Файл: %4$s; Строка: %5$s',
			$this->get_feed_id(),
			'$status_sborki =',
			$status_sborki,
			'class-xfavi-generation-xml.php',
			__LINE__
		) );

		switch ( $status_sborki ) {
			case -1: // сборка завершена
				new XFAVI_Error_Log( sprintf(
					'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
					$this->get_feed_id(),
					'case -1',
					'class-xfavi-generation-xml.php',
					__LINE__
				) );

				wp_clear_scheduled_hook( 'xfavi_cron_sborki', [ $this->get_feed_id() ] );
				break;
			case 1: // сборка начата		
				new XFAVI_Error_Log( sprintf(
					'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
					$this->get_feed_id(),
					'case 1',
					'class-xfavi-generation-xml.php',
					__LINE__
				) );

				$result_xml = $this->get_feed_header(); // TODO: удалить строку после внедрения механизма см ниже строку
				// TODO: создаём пустой временный файл фида т.к заголовок у нас в -1.tmp $result_xml
				$result = $this->write_file_feed_tmp( $result_xml, 'w+' ); // TODO: write_file_feed_tmp( '', 'w+' );
				if ( true == $result ) {
					new XFAVI_Error_Log( sprintf(
						'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
						$this->get_feed_id(),
						'$this->write_file_feed_tmp отработала успешно',
						'class-xfavi-generation-xml.php',
						__LINE__
					) );
				} else {
					new XFAVI_Error_Log( sprintf(
						'FEED № %1$s; ERROR: %2$s %3$s; Файл: %4$s; Строка: %5$s',
						$this->get_feed_id(),
						'$this->write_file_feed_tmp вернула ошибку при записи временного файла фида! $result =',
						$result,
						'class-xfavi-generation-xml.php',
						__LINE__
					) );
					$this->stop();
					return;
				}
				$this->clear_file_ids_in_xml( $this->get_feed_id() );

				$status_sborki = 2; // * пересмотреть строку. Возможно тут будет целый блок кода

				new XFAVI_Error_Log( sprintf(
					'FEED № %1$s; %2$s %3$s %4$s %5$s; Файл: %6$s; Строка: %7$s',
					$this->get_feed_id(),
					'status_sborki увеличен на',
					$step_export,
					'и равен',
					$status_sborki,
					'class-xfavi-generation-xml.php',
					__LINE__
				) );

				xfavi_optionUPD( 'xfavi_status_sborki', $status_sborki, $this->get_feed_id() );
				break;
			default:
				new XFAVI_Error_Log( sprintf(
					'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
					$this->get_feed_id(),
					'case default',
					'class-xfavi-generation-xml.php',
					__LINE__
				) );

				$offset = ( ( $status_sborki - 1 ) * $step_export ) - $step_export; // $status_sborki - $step_export;
				$args = [ 
					'post_type' => 'product',
					'post_status' => 'publish',
					'posts_per_page' => $step_export,
					'offset' => $offset,
					'relation' => 'AND',
					'orderby' => 'ID'
				];
				$whot_export = common_option_get( 'xfavi_whot_export', false, $this->get_feed_id(), 'xfavi' );
				switch ( $whot_export ) {
					case "xfavi_vygruzhat":
						$args['meta_query'] = [ 
							[ 
								'key' => '_xfavi_vygruzhat',
								'value' => 'yes'
							]
						];
						break;
					case "xmlset":
						$xfavi_xmlset_number = '1';
						$xfavi_xmlset_number = apply_filters(
							'xfavi_xmlset_number_filter',
							$xfavi_xmlset_number,
							$this->get_feed_id()
						);
						$xfavi_xmlset_key = '_xfavi_xmlset' . $xfavi_xmlset_number;
						$args['meta_query'] = [ 
							[ 
								'key' => $xfavi_xmlset_key,
								'value' => 'yes'
							]
						];
						break;
				}
				$args = apply_filters( 'xfavi_query_arg_filter', $args, $this->get_feed_id() );

				new XFAVI_Error_Log( sprintf(
					'FEED № %1$s; %2$s = %3$s. $args =>; Файл: %4$s; Строка: %5$s',
					$this->get_feed_id(),
					'Полная сборка. $whot_export',
					$whot_export,
					'class-xfavi-generation-xml.php',
					__LINE__
				) );
				new XFAVI_Error_Log( $args );

				$featured_query = new \WP_Query( $args );
				$prod_id_arr = [];
				if ( $featured_query->have_posts() ) {
					new XFAVI_Error_Log( sprintf(
						'FEED № %1$s; %2$s = %3$s; Файл: %4$s; Строка: %5$s',
						$this->get_feed_id(),
						'Вернулось записей',
						count( $featured_query->posts ),
						'class-xfavi-generation-xml.php',
						__LINE__
					) );
					// ? если начинать с 0, то может возникнуть ситуация, когда в фиде задублится товар
					for ( $i = 0; $i < count( $featured_query->posts ); $i++ ) {
						$prod_id_arr[ $i ]['ID'] = $featured_query->posts[ $i ]->ID;
						$prod_id_arr[ $i ]['post_modified_gmt'] = $featured_query->posts[ $i ]->post_modified_gmt;
					}
					wp_reset_query(); /* Remember to reset */
					unset( $featured_query ); // чутка освободим память
					$this->gluing( $prod_id_arr );
					$status_sborki++; // = $status_sborki + $step_export;
					new XFAVI_Error_Log( sprintf(
						'FEED № %1$s; %2$s %3$s %4$s %5$s; Файл: %6$s; Строка: %7$s',
						$this->get_feed_id(),
						'status_sborki увеличен на',
						$step_export,
						'и равен',
						$status_sborki,
						'class-xfavi-generation-xml.php',
						__LINE__
					) );
					xfavi_optionUPD( 'xfavi_status_sborki', $status_sborki, $this->get_feed_id() );
				} else { // если постов нет, пишем концовку файла
					new XFAVI_Error_Log( sprintf(
						'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
						$this->get_feed_id(),
						'Постов нет',
						'class-xfavi-generation-xml.php',
						__LINE__
					) );
					$result_xml = $this->get_feed_footer();
					$result = $this->write_file_feed_tmp( $result_xml, 'a' );
					new XFAVI_Error_Log( sprintf(
						'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
						$this->get_feed_id(),
						'Файл фида готов. Осталось только переименовать временный файл в основной',
						'class-xfavi-generation-xml.php',
						__LINE__
					) );
					$res_rename = $this->rename_feed_file();
					$this->archiving( $res_rename );
					// xfavi_rename_file( $this->get_feed_id() );

					$this->stop();
				}
			// end default
		} // end switch($status_sborki)
		return; // final return from public function phase()
	}

	/**
	 * Summary of stop
	 * 
	 * @return void
	 */
	public function stop() {
		if ( 'once' === common_option_get( 'xfavi_run_cron', false, $this->get_feed_id(), 'xfavi' ) ) {
			// если была одноразовая сборка - переводим переключатель в `отключено`
			common_option_upd( 'xfavi_run_cron', 'disabled', 'no', $this->get_feed_id(), 'xfavi' );
			common_option_upd( 'xfavi_status_cron', 'disabled', 'no', $this->get_feed_id(), 'xfavi' );
		}
		$status_sborki = -1;
		xfavi_optionUPD( 'xfavi_status_sborki', $status_sborki, $this->get_feed_id() );
		wp_clear_scheduled_hook( 'xfavi_cron_sborki', [ $this->get_feed_id() ] );
		do_action( 'xfavi_after_construct', $this->get_feed_id(), 'full' ); // сборка закончена
	}

	/**
	 * Проверим, нужна ли пересборка фида при обновлении поста
	 * 
	 * @param mixed $post_id
	 * 
	 * @return bool
	 */
	public function check_ufup( $post_id ) {
		$xfavi_ufup = common_option_get( 'xfavi_ufup', false, $this->get_feed_id(), 'xfavi' );
		if ( $xfavi_ufup === 'on' ) {
			$status_sborki = (int) xfavi_optionGET( 'xfavi_status_sborki', $this->get_feed_id() );
			if ( $status_sborki > -1 ) { // если идет сборка фида - пропуск
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * Get header of XML feed 
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	protected function get_feed_header( $result_xml = '' ) {
		// обнуляем лог ошибок
		common_option_upd( 'xfavi_critical_errors', '', 'no', $this->get_feed_id(), 'xfavi' );
		new XFAVI_Error_Log( sprintf(
			'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
			$this->get_feed_id(),
			'Стартовала xfavi_feed_header',
			'class-xfavi-generation-xml.php',
			__LINE__
		) );
		$result_xml .= new Get_Open_Tag( 'Ads', [ 'formatVersion' => '3', 'target' => 'Avito.ru' ] );
		$unixtime = current_time( 'Y-m-d H:i' );
		common_option_upd( 'xfavi_date_sborki', $unixtime, 'no', $this->get_feed_id(), 'xfavi' );
		do_action( 'xfavi_before_items' );
		return $result_xml;
	}

	/**
	 * Summary of get_ids_in_xml
	 * 
	 * @param string $file_content
	 * 
	 * @return array
	 */
	protected function get_ids_in_xml( $file_content ) {
		/**
		 * $file_content - содержимое файла (Обязательный параметр)
		 * Возвращает массив в котором ключи - это id товаров в БД WordPress, попавшие в фид
		 */
		$res_arr = [];
		$file_content_string_arr = explode( PHP_EOL, $file_content );
		for ( $i = 0; $i < count( $file_content_string_arr ) - 1; $i++ ) {
			$r_arr = explode( ';', $file_content_string_arr[ $i ] );
			$res_arr[ $r_arr[0] ] = '';
		}
		return $res_arr;
	}

	/**
	 * Get body feed
	 * 
	 * @param string $result_xml
	 * @param string $result_stock_xml
	 * 
	 * @return string
	 */
	protected function get_feed_body( $result_xml = '', $result_stock_xml = '' ) {
		$xfavi_file_ids_in_xml = urldecode( common_option_get( 'xfavi_file_ids_in_xml', false, $this->get_feed_id(), 'xfavi' ) );
		if ( empty( $xfavi_file_ids_in_xml ) ||
			$xfavi_file_ids_in_xml !== XFAVI_PLUGIN_UPLOADS_DIR_PATH . '/feed' . $this->get_feed_id() . '/ids_in_xml.tmp'
		) { // если не указан адрес файла с id-шниками или они не равны
			$xfavi_file_ids_in_xml = XFAVI_PLUGIN_UPLOADS_DIR_PATH . '/feed' . $this->get_feed_id() . '/ids_in_xml.tmp';
			xfavi_optionUPD( 'xfavi_file_ids_in_xml', urlencode( $xfavi_file_ids_in_xml ), $this->get_feed_id(), 'yes', 'set_arr' );
		}

		$file_content = file_get_contents( $xfavi_file_ids_in_xml );
		$ids_in_xml_arr = $this->get_ids_in_xml( $file_content );

		$name_dir = XFAVI_SITE_UPLOADS_DIR_PATH . '/xml-for-avito/feed' . $this->get_feed_id();

		$result_stock_xml = new Get_Open_Tag(
			'items', [ 
				'date' => (string) current_time( 'Y-m-d\TH:i:s' ), // 2022-07-17T17:47:00
				'formatVersion' => '1'
			]
		);
		foreach ( $ids_in_xml_arr as $key => $value ) {
			$product_id = (int) $key;
			$filename = $name_dir . '/' . $product_id . '.tmp';
			$result_xml .= file_get_contents( $filename );

			$stock_filename = $name_dir . '/' . $product_id . '-stock.tmp';
			$result_stock_xml .= file_get_contents( $stock_filename );
		}
		$result_stock_xml .= new Get_Closed_Tag( 'items' );
		new XFAVI_Write_File(
			$result_stock_xml,
			[ 
				'file_name' => 'stock-' . $this->get_feed_id(),
				'file_ext' => 'xml',
				'tmp_dir_name' => XFAVI_PLUGIN_UPLOADS_DIR_PATH,
				'level' => 0,
				'action' => 'create'
			],
			$this->get_feed_id()
		);
		$stock_file_url = XFAVI_PLUGIN_UPLOADS_DIR_URL . '/stock-' . $this->get_feed_id() . '.xml';
		xfavi_optionUPD( 'xfavi_stock_file_url', urlencode( $stock_file_url ), $this->get_feed_id(), 'yes', 'set_arr' );

		xfavi_optionUPD( 'xfavi_count_products_in_feed', count( $ids_in_xml_arr ), $this->get_feed_id(), 'yes', 'set_arr' );
		// товаров попало в фид - count($ids_in_xml_arr);

		return $result_xml;
	}

	/**
	 * Get footer feed
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	protected function get_feed_footer( $result_xml = '' ) {
		$result_xml .= $this->get_feed_body( $result_xml );

		$result_xml = apply_filters( 'xfavi_after_offers_filter', $result_xml, $this->get_feed_id() );
		$result_xml .= new Get_Closed_Tag( 'Ads' );

		xfavi_optionUPD( 'xfavi_date_sborki_end', current_time( 'Y-m-d H:i' ), $this->get_feed_id(), 'yes', 'set_arr' );

		return $result_xml;
	}

	/**
	 * Get feed ID
	 * 
	 * @return string
	 */
	protected function get_feed_id() {
		return $this->feed_id;
	}

	/**
	 * Get prefix of feed
	 * 
	 * @return string
	 */
	protected function get_prefix_feed() {
		if ( $this->get_feed_id() === '1' ) {
			return '';
		} else {
			return $this->get_feed_id();
		}
	}

	/**
	 * Summary of onlygluing
	 * 
	 * @param bool $without_header - Optional
	 * 
	 * @return void
	 */
	public function onlygluing() {
		$result_xml = $this->get_feed_header();
		/* создаем файл или перезаписываем старый удалив содержимое */
		$result = $this->write_file_feed_tmp( $result_xml, 'w+' );
		if ( true !== $result ) {
			new XFAVI_Error_Log( sprintf(
				'FEED № %1$s; $this->write_file вернула ошибку! $result = %2$s; Файл: %3$s; Строка: %4$s',
				$this->get_feed_id(),
				$result,
				'class-xfavi-generation-xml.php',
				__LINE__
			) );
		}

		xfavi_optionUPD( 'xfavi_status_sborki', '-1', $this->get_feed_id() );
		$whot_export = common_option_get( 'xfavi_whot_export', false, $this->get_feed_id(), 'xfavi' );

		$result_xml = '';
		$step_export = -1;
		$prod_id_arr = [];

		if ( $whot_export === 'xfavi_vygruzhat' ) {
			$args = [ 
				'post_type' => 'product',
				'post_status' => 'publish',
				'posts_per_page' => $step_export, // сколько выводить товаров
				// 'offset' => $offset,
				'relation' => 'AND',
				'orderby' => 'ID',
				'fields' => 'ids',
				'meta_query' => [ 
					[ 
						'key' => '_xfavi_vygruzhat',
						'value' => 'yes'
					]
				]
			];
		} else { //  if ($whot_export == 'all' || $whot_export == 'simple')
			$args = [ 
				'post_type' => 'product',
				'post_status' => 'publish',
				'posts_per_page' => $step_export, // сколько выводить товаров
				// 'offset' => $offset,
				'relation' => 'AND',
				'orderby' => 'ID',
				'fields' => 'ids'
			];
		}

		$args = apply_filters( 'xfavi_query_arg_filter', $args, $this->get_feed_id() );
		new XFAVI_Error_Log( sprintf(
			'FEED № %1$s; Быстрая сборка. $whot_export = %2$s; Файл: %3$s; Строка: %4$s',
			$this->get_feed_id(),
			$whot_export,
			'class-xfavi-generation-xml.php',
			__LINE__
		) );
		new XFAVI_Error_Log( $args );
		new XFAVI_Error_Log( sprintf(
			'FEED № %1$s; NOTICE: onlygluing до запуска WP_Query RAM %2$s Кб; Файл: %3$s; Строка: %4$s',
			$this->get_feed_id(),
			round( memory_get_usage() / 1024, 1 ),
			'class-xfavi-generation-xml.php',
			__LINE__
		) );
		$featured_query = new \WP_Query( $args );
		new XFAVI_Error_Log( sprintf(
			'FEED № %1$s; NOTICE: onlygluing после запуска WP_Query RAM %2$s Кб; Файл: %3$s; Строка: %4$s',
			$this->get_feed_id(),
			round( memory_get_usage() / 1024, 1 ),
			'class-xfavi-generation-xml.php',
			__LINE__
		) );

		global $wpdb;
		if ( $featured_query->have_posts() ) {
			new XFAVI_Error_Log( sprintf(
				'FEED № %1$s; Вернулось записей = %2$s; Файл: %3$s; Строка: %4$s',
				$this->get_feed_id(),
				count( $featured_query->posts ),
				'class-xfavi-generation-xml.php',
				__LINE__
			) );
			for ( $i = 0; $i < count( $featured_query->posts ); $i++ ) {
				/**
				 *	если не юзаем 'fields'  => 'ids'
				 *	$prod_id_arr[$i]['ID'] = $featured_query->posts[$i]->ID;
				 *	$prod_id_arr[$i]['post_modified_gmt'] = $featured_query->posts[$i]->post_modified_gmt;
				 */
				$curID = $featured_query->posts[ $i ];
				$prod_id_arr[ $i ]['ID'] = $curID;
				$res = $wpdb->get_results( $wpdb->prepare( "SELECT post_modified_gmt FROM $wpdb->posts WHERE id=%d", $curID ), ARRAY_A );
				$prod_id_arr[ $i ]['post_modified_gmt'] = $res[0]['post_modified_gmt'];
				// get_post_modified_time('Y-m-j H:i:s', true, $featured_query->posts[$i]);
			}
			wp_reset_query(); /* Remember to reset */
			unset( $featured_query ); // чутка освободим память
		}
		if ( ! empty( $prod_id_arr ) ) {
			new XFAVI_Error_Log( sprintf(
				'FEED № %1$s; NOTICE: %2$s; Файл: %3$s; Строка: %4$s',
				$this->get_feed_id(),
				'onlygluing передала управление this->gluing',
				'class-xfavi-generation-xml.php',
				__LINE__
			) );
			$this->gluing( $prod_id_arr );
		}

		// если постов нет, пишем концовку файла
		$result_xml = $this->get_feed_footer();
		$result = $this->write_file_feed_tmp( $result_xml, 'a' );
		new XFAVI_Error_Log( sprintf(
			'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
			$this->get_feed_id(),
			'Файл фида готов. Осталось только переименовать временный файл в основной',
			'class-xfavi-generation-xml.php',
			__LINE__
		) );
		$res_rename = $this->rename_feed_file();
		$this->archiving( $res_rename );

		$this->stop();
	} // end function onlygluing()

	/**
	 * Перименовывает временный файл фида в основной
	 * 
	 * @return array|false
	 */
	private function rename_feed_file() {
		new XFAVI_Error_Log( sprintf(
			'FEED № %1$s; Cтартовала $this->rename_feed_file; Файл: %2$s; Строка: %3$s',
			$this->get_feed_id(),
			'class-xfavi-generation-xml.php',
			__LINE__
		) );

		$feed_file_meta = new XFAVI_Feed_File_Meta( $this->get_feed_id() );
		$file_feed_name = $feed_file_meta->get_feed_filename();

		// /home/site.ru/public_html/wp-content/uploads/xml-for-avito/feed-avito-0.xml
		$feed_basedir_old = urldecode( common_option_get( 'xfavi_file_file', false, $this->get_feed_id(), 'xfavi' ) );
		if ( empty( $feed_basedir_old ) ) {
			$feed_basedir_old = XFAVI_SITE_UPLOADS_DIR_PATH . "/" . $this->get_prefix_feed() . "feed-avito-0-tmp.xml";
		}

		// /home/site.ru/public_html/wp-content/uploads/xml-for-avito/feed-avito-0.xml
		// ? надо придумать как поулчить урл загрузок конкретного блога, например, используя BLOGUPLOADDIR
		$feed_basedir_new = sprintf(
			'%1$s/%2$s.%3$s', XFAVI_SITE_UPLOADS_DIR_PATH, $file_feed_name, $feed_file_meta->get_feed_extension()
		);

		// https://site.ru/wp-content/uploads/feed-avito-2.xml
		$feed_url_new = sprintf(
			'%1$s/%2$s.%3$s', XFAVI_SITE_UPLOADS_URL, $file_feed_name, $feed_file_meta->get_feed_extension()
		);

		$file_name = $file_feed_name . "." . $feed_file_meta->get_feed_extension();
		$file_name_zip = $file_feed_name . ".zip";

		new XFAVI_Error_Log( sprintf(
			'FEED № %1$s; $feed_basedir_old = %2$s; Файл: %3$s; Строка: %4$s',
			$this->get_feed_id(),
			$feed_basedir_old,
			'class-xfavi-generation-xml.php',
			__LINE__
		) );

		new XFAVI_Error_Log( sprintf(
			'FEED № %1$s; $feed_basedir_new = %2$s; Файл: %3$s; Строка: %4$s',
			$this->get_feed_id(),
			$feed_basedir_new,
			'class-xfavi-generation-xml.php',
			__LINE__
		) );

		if ( false === rename( $feed_basedir_old, $feed_basedir_new ) ) {
			new XFAVI_Error_Log( sprintf(
				'FEED № %1$s; Не могу переименовать файл из %2$s в %3$s; Файл: %4$s; Строка: %5$s',
				$this->get_feed_id(),
				$feed_basedir_old,
				$feed_basedir_new,
				'class-xfavi-generation-xml.php',
				__LINE__
			) );
			return false;
		} else {
			xfavi_optionUPD( 'xfavi_file_url', urlencode( $feed_url_new ), $this->get_feed_id(), 'yes', 'set_arr' );
			new XFAVI_Error_Log( sprintf(
				'FEED № %1$s; Файл успешно переименован из %2$s в %3$s; Файл: %4$s; Строка: %5$s',
				$this->get_feed_id(),
				$feed_basedir_old,
				$feed_basedir_new,
				'class-xfavi-generation-xml.php',
				__LINE__
			) );

			return [ 
				'file_name_zip' => $file_name_zip,
				'file_name' => $file_name,
				'file_url' => $feed_url_new,
				'file_basedir' => $feed_basedir_new
			];
		}
	}

	/**
	 * Archiving to ZIP
	 * 
	 * @param mixed $res_rename
	 * 
	 * @return void
	 */
	private function archiving( $res_rename ) {
		$archive_to_zip = common_option_get( 'xfavi_archive_to_zip', false, $this->get_feed_id(), 'xfavi' );
		if ( $archive_to_zip === 'enabled' && is_array( $res_rename ) ) {
			new XFAVI_Error_Log( sprintf( 'FEED №%1$s; %2$s; Файл: %3$s; Строка: %4$s',
				$this->get_feed_id(),
				'Приступаем к архивированию файла;',
				'class-xfavi-generation-xml.php',
				__LINE__
			) );
			$zip = new ZipArchive();
			$zip->open(
				XFAVI_SITE_UPLOADS_DIR_PATH . '/' . $res_rename['file_name_zip'],
				ZipArchive::CREATE | ZipArchive::OVERWRITE
			);
			$zip->addFile( $res_rename['file_basedir'], $res_rename['file_name'] );
			$zip->close();
			xfavi_optionUPD(
				'xfavi_file_url',
				urlencode( XFAVI_SITE_UPLOADS_URL . '/' . $res_rename['file_name_zip'] ),
				$this->get_feed_id(),
				'yes',
				'set_arr'
			);
			new XFAVI_Error_Log( sprintf( 'FEED №%1$s; %2$s; Файл: %3$s; Строка: %4$s',
				$this->get_feed_id(),
				'Архивирование успешно;',
				'class-xfavi-generation-xml.php',
				__LINE__
			) );
		}
	}
}