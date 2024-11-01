<?php
/**
 * Traits for variable products
 *
 * @author		Maxim Glazunov
 * @link			https://icopydoc.ru/
 * @since		1.6.0
 *
 * @return 		$result_xml (string)
 *
 * @depends		class:		XFAVI_Get_Paired_Tag
 *				methods: 	get_product
 *							get_offer
 *							get_feed_id
 *				functions:	xfavi_optionGET
 *				variable:	feed_category_id (set it)
 */
defined( 'ABSPATH' ) || exit;

trait XFAVI_T_Common_Skips {
	public function get_skips() {
		$product = $this->get_product();
		$skip_flag = false;

		if ( $product == null ) {
			$this->add_skip_reason( [ 
				'reason' => __( 'Нет товара с таким ID', 'xfavi' ),
				'post_id' => $product->get_id(),
				'file' => 'trait-xfavi-t-common-skips.php',
				'line' => __LINE__
			] );
			return '';
		}

		if ( $product->is_type( 'grouped' ) ) {
			$this->add_skip_reason( [ 
				'reason' => __( 'Сгруппированный товар', 'xfavi' ),
				'post_id' => $product->get_id(),
				'file' => 'trait-xfavi-t-common-skips.php',
				'line' => __LINE__
			] );
			return '';
		}

		if ( $product->is_type( 'external' ) ) {
			$this->add_skip_reason( [ 
				'reason' => __( 'Внешний/Партнерский товар', 'xfavi' ),
				'post_id' => $product->get_id(),
				'file' => 'trait-xfavi-t-common-skips.php',
				'line' => __LINE__
			] );
			return '';
		}

		// что выгружать
		$xfavi_whot_export = xfavi_optionGET( 'xfavi_whot_export', $this->get_feed_id(), 'set_arr' );
		if ( $product->is_type( 'variable' ) ) {
			if ( $xfavi_whot_export === 'simple' ) {
				$this->add_skip_reason( [ 
					'reason' => __( 'Простой товар', 'xfavi' ),
					'post_id' => $product->get_id(),
					'file' => 'trait-xfavi-t-common-skips.php',
					'line' => __LINE__
				] );
				return '';
			}
		}
		if ( $product->is_type( 'simple' ) ) {
			if ( $xfavi_whot_export === 'variable' ) {
				$this->add_skip_reason( [ 
					'reason' => __( 'Вариативный товар', 'xfavi' ),
					'post_id' => $product->get_id(),
					'file' => 'trait-xfavi-t-common-skips.php',
					'line' => __LINE__
				] );
				return '';
			}
		}

		if ( get_post_meta( $product->get_id(), 'xfavip_removefromxml', true ) === 'yes' ) {
			$this->add_skip_reason( [ 
				'reason' => __( 'Сработало условие "Удалить товар из фида"', 'xfavi' ),
				'post_id' => $product->get_id(),
				'file' => 'trait-xfavi-t-common-skips.php',
				'line' => __LINE__
			] );
			return '';
		}

		// ещё используется но потенциально заменить в прошке
		$special_data_for_flag = '';
		$skip_flag = apply_filters( 'xfavi_skip_flag', $skip_flag, $product->get_id(), $product, $special_data_for_flag, $this->get_feed_id() );
		if ( $skip_flag === true ) {
			$this->add_skip_reason( [ 
				'reason' => __( 'Флаг', 'xfavi' ),
				'post_id' => $product->get_id(),
				'file' => 'trait-xfavi-t-common-skips.php',
				'line' => __LINE__
			] );
			return '';
		}
		// ещё используется но потенциально заменить в прошке
		/* С версии 3.7.13 */
		$skip_flag = apply_filters(
			'xfavi_f_skip_flag',
			$skip_flag,
			[ 
				'product' => $product,
				'catid' => $this->get_feed_category_id()
			],
			$this->get_feed_id()
		);
		if ( false !== $skip_flag ) {
			$this->add_skip_reason( [ 
				'reason' => $skip_flag,
				'post_id' => $product->get_id(),
				'file' => 'trait-xfavi-t-common-skips.php',
				'line' => __LINE__
			] );
			return '';
		}

		// пропуск товаров, которых нет в наличии
		$xfavi_skip_missing_products = xfavi_optionGET( 'xfavi_skip_missing_products', $this->get_feed_id(), 'set_arr' );
		if ( $xfavi_skip_missing_products == 'on' ) {
			if ( $product->is_in_stock() == false ) {
				$this->add_skip_reason( [ 
					'reason' => __( 'Исключать товары которых нет в наличии', 'xfavi' ),
					'post_id' => $product->get_id(),
					'file' => 'trait-xfavi-t-common-skips.php',
					'line' => __LINE__
				] );
				return '';
			}
		}

		// пропускаем товары на предзаказ
		$skip_backorders_products = xfavi_optionGET( 'xfavi_skip_backorders_products', $this->get_feed_id(), 'set_arr' );
		if ( $skip_backorders_products == 'on' ) {
			if ( $product->get_manage_stock() == true ) { // включено управление запасом  
				if ( ( $product->get_stock_quantity() < 1 ) && ( $product->get_backorders() !== 'no' ) ) {
					$this->add_skip_reason( [ 
						'reason' => __( 'Исключать из фида товары для предзаказа', 'xfavi' ),
						'post_id' => $product->get_id(),
						'file' => 'trait-xfavi-t-common-skips.php',
						'line' => __LINE__
					] );
					return '';
				}
			} else {
				if ( $product->get_stock_status() !== 'instock' ) {
					$this->add_skip_reason( [ 
						'reason' => __( 'Исключать из фида товары для предзаказа', 'xfavi' ),
						'post_id' => $product->get_id(),
						'file' => 'trait-xfavi-t-common-skips.php',
						'line' => __LINE__
					] );
					return '';
				}
			}
		}

		if ( $product->is_type( 'variable' ) ) {
			$offer = $this->offer;

			// пропуск вариаций, которых нет в наличии
			$xfavi_skip_missing_products = xfavi_optionGET( 'xfavi_skip_missing_products', $this->get_feed_id(), 'set_arr' );
			if ( $xfavi_skip_missing_products == 'on' ) {
				if ( false == $offer->is_in_stock() ) {
					$this->add_skip_reason( [ 
						'offer_id' => $offer->get_id(),
						'reason' => __( 'Исключать товары которых нет в наличии', 'xfavi' ),
						'post_id' => $product->get_id(),
						'file' => 'traits-xfavi-variable.php',
						'line' => __LINE__
					] );
					return '';
				}
			}

			// пропускаем вариации на предзаказ
			$skip_backorders_products = xfavi_optionGET( 'xfavi_skip_backorders_products', $this->get_feed_id(), 'set_arr' );
			if ( $skip_backorders_products == 'on' ) {
				if ( true == $offer->get_manage_stock() ) { // включено управление запасом			  
					if ( ( $offer->get_stock_quantity() < 1 ) && ( $offer->get_backorders() !== 'no' ) ) {
						$this->add_skip_reason( [ 
							'offer_id' => $offer->get_id(),
							'reason' => __( 'Исключать из фида товары для предзаказа', 'xfavi' ),
							'post_id' => $product->get_id(),
							'file' => 'traits-xfavi-variable.php',
							'line' => __LINE__
						] );
						return '';
					}
				}
			}

			$skip_flag = apply_filters(
				'xfavi_f_skip_flag_variable',
				$skip_flag,
				[ 
					'product' => $product,
					'offer' => $offer,
					'catid' => $this->get_feed_category_id()
				],
				$this->get_feed_id()
			);
			if ( $skip_flag !== false ) {
				$this->add_skip_reason( [ 
					'offer_id' => $offer->get_id(),
					'reason' => $skip_flag,
					'post_id' => $product->get_id(),
					'file' => 'trait-xfavi-t-common-skips.php',
					'line' => __LINE__
				] );
				return '';
			}
		}
	}
}