<?php
/**
 * Set and Get the Plugin Data // ! currently not in use
 *
 * @package                 iCopyDoc Plugins (v1, core 16-08-2023)
 * @subpackage              XML for Avito
 * @since                   2.1.21
 * 
 * @version                 2.2.0 (22-03-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param       array       $data_arr - Optional
 *
 * @depends                 classes:    
 *                          traits:     
 *                          methods:    
 *                          functions:  
 *                          constants:  
 *                          options:    
 */
defined( 'ABSPATH' ) || exit;

class XFAVI_Rules_List {
	/**
	 * The rules of feeds array
	 *
	 * @var array
	 */
	private $rules_arr = [];

	/**
	 * Summary of __construct
	 * 
	 * @param array $rules_arr - Optional
	 */
	public function __construct( $rules_arr = [] ) {
		if ( empty( $rules_arr ) ) {
			$this->rules_arr = [ 
				'lichnye_veshi' => [ 
					'ad'
				]
			];
		} else {
			$this->rules_arr = $rules_arr;
		}

		$this->rules_arr = apply_filters( 'xfavi_f_set_rules_arr', $this->get_rules_arr() );
	}

	/**
	 * Get the rules of feeds array
	 * 
	 * @return array
	 */
	public function get_rules_arr() {
		return $this->rules_arr;
	}
}