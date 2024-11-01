<?php 
/**
 * This trait sets the product category
 *
 * @package	                XML for Avito
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.1.5 (10-10-2023)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * 
 * @depends                 class:      XFAVI_Error_Log
 *                                      WPSEO_Primary_Term
 *                          methods:    
 *                          variable:   
 *                          methods:    get_product
 *                                      get_feed_id
 *                          functions:  
 *                          constants:  
 *                          variable:   feed_category_id (set it)
 */
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Common_Get_CatId {
	/**
	 * Summary of feed_category_id
	 * @var 
	 */
	protected $feed_category_id = null;

	/**
	 * Summary of set_category_id
	 * 
	 * @param mixed $catid
	 * 
	 * @return mixed
	 */
	public function set_category_id( $catid = null ) {
		// Yoast SEO
		if ( class_exists( 'WPSEO_Primary_Term' ) ) {
			$obj = new WPSEO_Primary_Term( 'product_cat', $this->get_product()->get_id() );
			$cat_id_yoast_seo = $obj->get_primary_term();
			if ( false === $cat_id_yoast_seo ) {
				$catid = $this->set_catid();
			} else {
				$catid = $cat_id_yoast_seo;
			}

			// Rank Math SEO
		} else if ( class_exists( 'RankMath' ) ) {
			$primary_cat_id = get_post_meta( $this->get_product()->get_id(), 'rank_math_primary_category', true );
			if ( $primary_cat_id ) {
				$product_cat = get_term( $primary_cat_id, 'product_cat' );
				$catid = $product_cat->term_id;
			} else {
				$catid = $this->set_catid();
			}

			// Standard WooCommerce сategory
		} else {
			$catid = $this->set_catid();
		}

		if ( empty( $catid ) ) {
			$this->add_skip_reason( [ 
				'reason' => __( 'Товар не имеет категории', 'xml-for-avito' ),
				'post_id' => $this->get_product()->get_id(),
				'file' => 'trait-xfavi-t-common-get-catid.php',
				'line' => __LINE__
			] );
			return '';
		}

		$this->feed_category_id = $catid;

		if ( ! empty( get_term_meta( $this->get_feed_category_id(), 'xfavi_avito_product_category', true ) ) ) {
			$avito_product_category = get_term_meta(
				$this->get_feed_category_id(),
				'xfavi_avito_product_category',
				true
			);
			$avito_product_category = str_replace( '_', ' ', $avito_product_category );
			$this->feed_category_avito_name = htmlspecialchars( $avito_product_category );
		}

		return $catid;
	}

	/**
	 * Summary of get_feed_category_id
	 * 
	 * @param mixed $catid
	 * 
	 * @return mixed
	 */
	public function get_feed_category_id( $catid = null ) {
		return $this->feed_category_id;
	}

	/**
	 * Summary of set_catid
	 * 
	 * @param mixed $catid
	 * 
	 * @return mixed
	 */
	private function set_catid( $catid = null ) {
		$termini = get_the_terms( $this->get_product()->get_id(), 'product_cat' );
		if ( false == $termini ) { // если база битая. фиксим id категорий
			$catid = $this->database_auto_boot();
		} else {
			$category_skip_flag = false;

			foreach ( $termini as $termin ) {

				$category_skip_flag = apply_filters(
					'x4avi_f_category_skip_flag',
					$category_skip_flag,
					[ 
						'product' => $this->product,
						'offer' => $this->offer,
						'feed_category_id' => $this->get_feed_category_id()
					],
					$this->get_feed_id()
				);
				if ( true === $category_skip_flag ) {
					continue;
				}

				$catid = $termin->term_id;
				break; // т.к. у товара может быть лишь 1 категория - выходим досрочно.
			}
		}
		return $catid;
	}

	/**
	 * Summary of database_auto_boot
	 * 
	 * @param mixed $catid
	 * 
	 * @return mixed
	 */
	private function database_auto_boot( $catid = null ) {
		new XFAVI_Error_Log( sprintf( 'FEED № %1$s; %2$s %3$s %4$s; Файл: %5$s; %6$s: %7$s',
			$this->get_feed_id(),
			'WARNING: Для товара $this->get_product()->get_id() =',
			$this->get_product()->get_id(),
			'get_the_terms = false. Возможно база битая. Пробуем задействовать wp_get_post_terms',
			'trait-xfavi-t-common-get-catid.php',
			__( 'строка', 'xml-for-avito' ),
			__LINE__
		) );
		$product_cats = wp_get_post_terms( $this->get_product()->get_id(), 'product_cat', [ 'fields' => 'ids' ] );
		// Раскомментировать строку ниже для автопочинки категорий в БД
		// wp_set_object_terms($this->get_product()->get_id(), $product_cats, 'product_cat');
		if ( is_array( $product_cats ) && count( $product_cats ) ) {
			$catid = $product_cats[0];
			new XFAVI_Error_Log( sprintf( 'FEED № %1$s; %2$s %3$s %4$s %5$s; Файл: %6$s; %7$s: %8$s',
				$this->get_feed_id(),
				'WARNING: Для товара $this->get_product()->get_id() =',
				$this->get_product()->get_id(),
				'база наверняка битая. wp_get_post_terms вернула массив. $catid = ',
				$catid,
				'trait-xfavi-t-common-get-catid.php',
				__( 'строка', 'xml-for-avito' ),
				__LINE__
			) );
		}
		return $catid;
	}
}