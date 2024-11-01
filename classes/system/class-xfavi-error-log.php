<?php
/**
 * Writes plugin logs
 *
 * @packag                  iCopyDoc Plugins (v1, core 26-04-2024)
 * @subpackage              XML for Avito
 * @since                   0.1.0
 * 
 * @version                 2.4.1 (17-05-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param      mixed        $text_to_log - Required
 * @param      string       $log_dir_name - Optional
 *
 * @depends                 classes:    WP_Filesystem_Base
 *                          traits:     
 *                          methods:    
 *                          functions:  get_blog_option
 *                                      get_option
 *                          constants:  XFAVI_PLUGIN_UPLOADS_DIR_PATH
 *                          options:    xfavi_keeplogs
 */
defined( 'ABSPATH' ) || exit;

final class XFAVI_Error_Log {
	/**
	 * Text to log
	 * @var 
	 */
	protected $text_to_log;
	/**
	 * Path to the log file
	 * @var 
	 */
	protected $log_file_path; // /home/site.ru/public_html/wp-content/uploads/xml-for-avito/xml-for-avito.log

	/**
	 * Writes plugin logs
	 * 
	 * @param mixed $text_to_log - Required - Text to log
	 * @param string $log_dir_name - Optional - Location of the log file on your server
	 * 
	 * @return void
	 */
	public function __construct( $text_to_log, $log_dir_name = XFAVI_PLUGIN_UPLOADS_DIR_PATH ) {
		$this->text_to_log = $text_to_log;
		if ( is_dir( $log_dir_name ) ) {
			$this->log_file_path = $log_dir_name . '/xml-for-avito.log';
		} else {
			if ( mkdir( $log_dir_name ) ) {
				$this->log_file_path = $log_dir_name . '/xml-for-avito.log';
			} else {
				$this->log_file_path = false;
				error_log( 'ERROR: XFAVI_Error_Log: No folder "' . $log_dir_name . '"; Line: ' . __LINE__, 0 );
			}
		}

		if ( $this->keeplogs_status() ) { // если включено вести логи
			if ( false === $this->get_file_path() ) {
				return;
			} else {
				$this->save_log( $text_to_log );
			}
		}
	}

	/**
	 * Summary of __toString
	 * 
	 * @return mixed
	 */
	public function __toString() {
		return $this->get_array_as_string( $this->text_to_log );
	}

	/**
	 * Save log to file
	 * 
	 * @param mixed $text_to_log
	 * 
	 * @return void
	 */
	protected function save_log( $text_to_log ) {
		if ( is_array( $text_to_log ) || is_object( $text_to_log ) ) {
			$r = $this->get_array_as_string( $text_to_log );
			unset( $text_to_log );
			$text_to_log = $r;
		}
		file_put_contents(
			$this->get_file_path(),
			'[' . gmdate( 'Y-m-d H:i:s' ) . '] ' . $text_to_log . PHP_EOL, FILE_APPEND
		);
	}

	/**
	 * Returns the path to the log file
	 * 
	 * @return string
	 */
	protected function get_file_path() {
		return $this->log_file_path;
	}

	/**
	 * Checks whether logs is enabled
	 * 
	 * @return bool
	 */
	protected function keeplogs_status() {
		if ( is_multisite() ) {
			$v = get_blog_option( get_current_blog_id(), 'xfavi_keeplogs' );
		} else {
			$v = get_option( 'xfavi_keeplogs' );
		}
		if ( $v === 'on' ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Converts data to a string
	 * 
	 * @param mixed $text
	 * @param string $new_line
	 * @param int $i
	 * @param string $res
	 * 
	 * @return string
	 */
	protected function get_array_as_string( $text, $new_line = PHP_EOL, $i = 0, $res = '' ) {
		$tab = '';
		for ( $x = 0; $x < $i; $x++ ) {
			$tab = '---' . $tab;
		}
		if ( is_object( $text ) ) {
			$text = (array) $text;
		}
		if ( is_array( $text ) ) {
			$i++;
			foreach ( $text as $key => $value ) {
				if ( is_array( $value ) ) { // массив
					$res .= $new_line . $tab . "[$key] => (" . gettype( $value ) . ")";
					$res .= $tab . $this->get_array_as_string( $value, $new_line, $i );
				} else if ( is_object( $value ) ) { // объект
					$res .= $new_line . $tab . "[$key] => (" . gettype( $value ) . ")";
					$value = (array) $value;
					$res .= $tab . $this->get_array_as_string( $value, $new_line, $i );
				} else if ( is_bool( $value ) ) { // boolean
					if ( true === $value ) {
						$res .= $new_line . $tab . "[$key] => (boolean)true";
					} else {
						$res .= $new_line . $tab . "[$key] => (boolean)false";
					}
				} else {
					$res .= $new_line . $tab . "[$key] => (" . gettype( $value ) . ")" . $value;
				}
			}
		} else {
			$res .= $new_line . $tab . $text;
		}
		return $res;
	}
}