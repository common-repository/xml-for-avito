<?php
/**
 * Traits Contact_Info for products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.4.8 (17-09-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_feed_id
 *                                      get_product
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Common_Contact_Info {
	/**
	 * Get contact info
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_contact_info( $result_xml = '' ) {
		$contact_info_arr = [ 
			'address' => stripslashes(
				htmlspecialchars( common_option_get( 'xfavi_address', false, $this->get_feed_id(), 'xfavi' ) )
			),
			'allow_email' => common_option_get( 'xfavi_allowEmail', false, $this->get_feed_id(), 'xfavi' ),
			'manager_name' => common_option_get( 'xfavi_managerName', false, $this->get_feed_id(), 'xfavi' ),
			'contact_phone' => common_option_get( 'xfavi_contactPhone', false, $this->get_feed_id(), 'xfavi' )
		];

		$contact_info_arr = apply_filters(
			'xfavi_f_contact_info_arr',
			$contact_info_arr,
			[ 
				'product' => $this->get_product(),
				'duplicate_number' => $this->get_duplicate_number()
			],
			$this->get_feed_id()
		);
		if ( $contact_info_arr['address'] !== '' ) {
			$result_xml .= new Get_Paired_Tag( 'Address', $contact_info_arr['address'] );
		} else {
			common_option_upd(
				'xfavi_critical_errors',
				'Фид пуст тк в настройках плагина не указан адрес!',
				'no',
				$this->get_feed_id(),
				'xfavi'
			);
			$this->add_skip_reason( [ 
				'reason' => __( 'В настройках плагина не указан адрес', 'xml-for-avito' ),
				'post_id' => $this->get_product()->get_id(),
				'file' => 'trait-xfavi-t-common-contact-info.php',
				'line' => __LINE__
			] );
			return '';
		}
		if ( ! empty( $contact_info_arr['allow_email'] ) ) {
			$result_xml .= new Get_Paired_Tag( 'AllowEmail', $contact_info_arr['allow_email'] );
		}
		if ( ! empty( $contact_info_arr['manager_name'] ) ) {
			$result_xml .= new Get_Paired_Tag( 'ManagerName', $contact_info_arr['manager_name'] );
		}
		if ( ! empty( $contact_info_arr['contact_phone'] ) ) {
			$result_xml .= new Get_Paired_Tag( 'ContactPhone', $contact_info_arr['contact_phone'] );
		}

		$xfavi_contact_method = common_option_get( 'xfavi_contact_method', false, $this->get_feed_id(), 'xfavi' );
		switch ( $xfavi_contact_method ) {
			case 'all':
				$msg = 'По телефону и в сообщениях';
				break;
			case 'phone':
				$msg = 'По телефону';
				break;
			case 'msg':
				$msg = 'В сообщениях';
				break;
			default:
				$msg = 'По телефону и в сообщениях';
		}
		$result_xml .= new Get_Paired_Tag( 'ContactMethod', $msg );

		$result_xml = apply_filters(
			'xfavi_f_xml_contact_info',
			$result_xml,
			[ 
				'product' => $this->get_product(),
				'duplicate_number' => $this->get_duplicate_number(),
				'contact_info_arr' => $contact_info_arr,
				'msg' => $msg
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}