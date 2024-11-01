<?php
/**
 * Settings page
 * 
 * @version 2.4.12 (03-10-2024)
 * @see     
 * @package 
 * 
 * @param $view_arr['feed_id']
 * @param $view_arr['tab_name']
 */
defined( 'ABSPATH' ) || exit;
$utm = sprintf(
	'?utm_source=%1$s&utm_medium=organic&utm_campaign=in-plugin-%1$s&utm_content=settings&utm_term=main-instruction',
	'xml-for-avito'
);
?>
<div class="wrap">
	<h1>
		<?php
		printf( '%s (<small><a href="https://icopydoc.ru/kak-sozdat-fid-dlya-avito-instruktsiya/%s" target="_blank">%s</a></small>)',
			esc_html__( 'Экспорт Avito', 'xml-for-avito' ),
			esc_attr( $utm ),
			esc_html__( 'как создать XML фид', 'xml-for-avito' )
		);
		?>
	</h1>
	<div id="poststuff">
		<?php include_once __DIR__ . '/html-admin-settings-page-feeds-list.php'; ?>
		<div id="post-body" class="columns-2">

			<div id="postbox-container-1" class="postbox-container">
				<div class="meta-box-sortables">
					<?php
					// TODO: удалить (v2.4.8-02-10-24) include_once __DIR__ . '/html-admin-settings-page-info-block.php';
					do_action( 'xfavi_activation_forms' );

					do_action( 'xfavi_feedback_block' );

					do_action( 'xfavi_before_container_1', $view_arr['feed_id'] );

					do_action( 'xfavi_between_container_1', $view_arr['feed_id'] );

					do_action( 'xfavi_append_container_1', $view_arr['feed_id'] );
					?>
				</div>
			</div><!-- /postbox-container-1 -->

			<div id="postbox-container-2" class="postbox-container">
				<div class="meta-box-sortables">
					<?php if ( $view_arr['feed_id'] == '' ) : ?>
						<div style="margin: 0 auto; max-width: 200px;">
							<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post"
								enctype="multipart/form-data">
								<?php wp_nonce_field( 'xfavi_nonce_action_add_new_feed', 'xfavi_nonce_field_add_new_feed' ); ?>
								<p style="display: block; margin: 10px auto; text-align: center;">
									<?php esc_html_e( 'Сначала нажмите кнопку', 'xml-for-avito' ); ?>:
								</p>
								<input style="display: block; margin: 10px auto;" class="button" type="submit"
									name="xfavi_submit_add_new_feed"
									value="<?php esc_html_e( 'Добавить фид', 'xml-for-avito' ); ?>" />
							</form>
						</div>
					<?php else : ?>
						<?php include_once __DIR__ . '/html-admin-settings-page-tabs.php'; ?>

						<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post"
							enctype="multipart/form-data">
							<input type="hidden" name="xfavi_feed_id_for_save"
								value="<?php echo esc_attr( $view_arr['feed_id'] ); ?>">
							<?php
							switch ( $view_arr['tab_name'] ) {
								case 'tags_settings_tab':
									include_once __DIR__ . '/html-admin-settings-page-tab-tags.php';
									break;
								default:
									$html_template = __DIR__ . '/html-admin-settings-page-tab-another.php';
									$html_template = apply_filters( 'x4avi_f_html_template_tab',
										$html_template,
										[ 
											'tab_name' => $view_arr['tab_name'],
											'view_arr' => $view_arr
										]
									);
									include_once $html_template;
							}

							do_action( 'xfavi_between_container_2', $view_arr['feed_id'] );

							include_once __DIR__ . '/html-admin-settings-page-save-btn.php';
							?>
						</form>
					<?php endif; ?>
				</div>
			</div><!-- /postbox-container-2 -->

		</div>
	</div><!-- /poststuff -->
	<?php
	do_action( 'print_view_html_icp_banners', 'xfavi' );
	do_action( 'print_view_html_icpd_my_plugins_list', 'xfavi' );
	?>
</div>