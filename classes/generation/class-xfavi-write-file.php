<?php
/**
 * Writes files (`tmp`, `xml` and etc)
 *
 * @package                 iCopyDoc Plugins (v1, core 10-06-2024)
 * @subpackage              XML for Avito
 * @since                   2.3.0
 * 
 * @version                 2.4.2 (11-06-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param     string        $xml_string - Required
 * @param     array         $args_arr - Required
 * @param     string        $feed_id - Optional
 *
 * @depends                 classes     
 *                          traits      
 *                          methods     
 *                          functions:  
 *                          constants:  XFAVI_PLUGIN_UPLOADS_DIR_PATH
 *                          options:    
 *                          actions:    
 *                          filters:    
 */
defined( 'ABSPATH' ) || exit;

final class XFAVI_Write_File {
	/**
	 * Text to tmp
	 * @var string
	 */
	protected $xml_string;
	/**
	 * Path to the tmp file
	 * @var string
	 */
	protected $tmp_file_path; // /home/site.ru/public_html/wp-content/uploads/xml-for-avito/feed/12345.tmp

	/**
	 * Writes files (`tmp`, `xml` and etc)
	 * 
	 * @param string $xml_string - Required
	 * @param array $args_arr - Required -
	 * $args_arr = [
	 *	'file_name' => (string). Required,
	 *	'file_ext' => (string). Optional. Default value `tmp`,
	 *	'tmp_dir_name' => (string). Optional. Default value `XFAVI_PLUGIN_UPLOADS_DIR_PATH`,
	 *	'level' => (int). Optional. Default value `1`,
	 *	'action' => (string). Optional. Default value  `create`. May be `create` or `append`. 
	 * ]
	 * @param string $feed_id - Optional
	 * 
	 * @return void
	 */
	public function __construct( $xml_string, $args_arr, $feed_id = null ) {
		// краткое имя файла
		if ( isset( $args_arr['file_name'] ) ) {
			$file_name = $args_arr['file_name'];
		} else {
			return;
		}
		// расширение файла
		if ( isset( $args_arr['file_ext'] ) ) {
			$ext = $args_arr['file_ext'];
		} else {
			$ext = 'tmp';
		}

		if ( isset( $args_arr['tmp_dir_name'] ) ) {
			$tmp_dir_name = $args_arr['tmp_dir_name'];
		} else {
			$tmp_dir_name = XFAVI_PLUGIN_UPLOADS_DIR_PATH;
		}

		if ( isset( $args_arr['level'] ) ) {
			$level = $args_arr['level'];
		} else {
			$level = 1;
		}
		if ( 1 === $level ) {
			$feed_folder_name = sprintf( 'feed%1$s/', $feed_id );
		} else {
			$feed_folder_name = '';
		}

		if ( isset( $args_arr['action'] ) ) {
			$action = $args_arr['action'];
		} else {
			$action = 'create';
		}

		$this->xml_string = $xml_string;
		if ( is_dir( $tmp_dir_name ) ) {
			$this->tmp_file_path = sprintf( '%1$s/%2$s%3$s.%4$s', $tmp_dir_name, $feed_folder_name, $file_name, $ext );
		} else {
			if ( mkdir( $tmp_dir_name ) ) {
				$this->tmp_file_path = sprintf( '%1$s/%2$s%3$s.%4$s', $tmp_dir_name, $feed_folder_name, $file_name, $ext );
			} else {
				$this->tmp_file_path = false;
				error_log( 'ERROR: XFAVI_Write_File : No folder "' . $tmp_dir_name . '"; Line: ' . __LINE__, 0 );
			}
		}

		if ( false === $this->get_file_path() ) {
			return;
		}

		if ( $action === 'create' ) {
			$this->create_file( $xml_string );
		} else {
			$this->append_to_file( $xml_string );
		}
	}

	/**
	 * Save tmp file
	 * 
	 * @param string $xml_string
	 * 
	 * @return void
	 */
	protected function create_file( $xml_string ) {
		if ( empty( $xml_string ) ) {
			$xml_string = ' ';
		}
		$fp = fopen( $this->get_file_path(), "w" );
		if ( false === $fp ) {
			error_log(
				'ERROR: XFAVI_Write_File : File opening return (bool) false "' . $this->get_file_path() . '"; Line: ' . __LINE__, 0
			);
		} else {
			fwrite( $fp, $xml_string ); // записываем в файл текст
			fclose( $fp ); // закрываем
		}
	}

	/**
	 * Append to tmp file
	 * 
	 * @param string $xml_string
	 * 
	 * @return void
	 */
	protected function append_to_file( $xml_string ) {
		file_put_contents(
			$this->get_file_path(), $xml_string, FILE_APPEND
		);
	}

	/**
	 * Returns the path to the tmp file
	 * 
	 * @return string
	 */
	protected function get_file_path() {
		return $this->tmp_file_path;
	}
}