<?php
/**
 * Traits AdType for simple products
 *
 * @package                 XML for Avito
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.0.5 (07-08-2023)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_product
 *                                      get_feed_category_id
 *                          functions:  
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Simple_Get_Adtype {
	/**
	 * Возвращает AdType - Вид объявления
	 * 
	 * @param string $tag_name
	 * @param string $standart
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_adtype( $tag_name = 'AdType', $standart = '', $result_xml = '' ) {
		if ( get_post_meta( $this->get_product()->get_id(), '_xfavi_adType', true ) === 'disabled' ) {
			$result_ad_type = 'disabled';
		} else if ( get_post_meta( $this->get_product()->get_id(), '_xfavi_adType', true ) == ''
			|| get_post_meta( $this->get_product()->get_id(), '_xfavi_adType', true ) === 'default' ) {
			$result_ad_type = get_term_meta( $this->get_feed_category_id(), 'xfavi_adType', true );
			$result_ad_type = str_replace( '_', ' ', $result_ad_type );
		} else {
			$result_ad_type = get_post_meta( $this->get_product()->get_id(), '_xfavi_adType', true );
		}
		if ( $result_ad_type == '' || $result_ad_type == 'disabled' ) {
			if ( $standart !== 'zapchasti' ) {
				$this->add_skip_reason( [ 
					'reason' => __( 'Отсутствует AdType', 'xml-for-avito' ),
					'post_id' => $this->get_product()->get_id(),
					'file' => 'trait-xfavi-t-simple-get-adtype.php',
					'line' => __LINE__
				] );
				return '';
			}
		}

		if ( $result_ad_type !== 'disabled' ) {
			$result_xml .= new Get_Paired_Tag( $tag_name, $result_ad_type );
		}
		return $result_xml;
	}
}