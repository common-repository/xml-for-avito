<?php
/**
 * Print Debug page
 * 
 * @version 2.1.0 (10-08-2023)
 * @see     
 * @package 
 * 
 * @param $view_arr['simulated_post_id']
 * @param $view_arr['feed_id']
 * @param $view_arr['feed_assignment']
 * @param $view_arr['simulation_result_report']
 * @param $view_arr['simulation_result']
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="postbox">
	<h2 class="hndle">
		<?php _e( 'Симуляция запроса', 'xml-for-avito' ); ?>
	</h2>
	<div class="inside">
		<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post" enctype="multipart/form-data">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="xfavi_simulated_post_id">postId</label></th>
						<td class="overalldesc">
							<input type="number" min="1" name="xfavi_simulated_post_id"
								value="<?php echo esc_attr( $view_arr['simulated_post_id'] ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="xfavi_feed_id">feed_id</label></th>
						<td class="overalldesc">
							<select style="width: 100%" name="xfavi_feed_id" id="xfavi_feed_id">
								<?php
								if ( is_multisite() ) {
									$cur_blog_id = get_current_blog_id();
								} else {
									$cur_blog_id = '0';
								}
								$xfavi_settings_arr = xfavi_optionGET( 'xfavi_settings_arr' );
								$xfavi_settings_arr_keys_arr = array_keys( $xfavi_settings_arr );
								for ( $i = 0; $i < count( $xfavi_settings_arr_keys_arr ); $i++ ) :
									$feed_id = (string) $xfavi_settings_arr_keys_arr[ $i ];
									if ( $xfavi_settings_arr[ $feed_id ]['xfavi_feed_assignment'] === '' ) {
										$feed_assignment = '';
									} else {
										$feed_assignment = sprintf( ' (%s)',
											$xfavi_settings_arr[ $feed_id ]['xfavi_feed_assignment']
										);
									}

									printf( '<option value="%s" %s>%s %s: feed-xml-%s.xml%s</option>',
										$feed_id,
										selected( $view_arr['feed_id'], $feed_id, false ),
										__( 'Feed', 'xml-for-avito' ),
										$feed_id,
										$cur_blog_id,
										$feed_assignment
									);
									?>
								<?php endfor; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row" colspan="2">
							<textarea style="width: 100%;" rows="4"><?php
							echo htmlspecialchars( $view_arr['simulation_result_report'] );
							?></textarea>
						</th>
					</tr>
					<tr>
						<th scope="row" colspan="2">
							<textarea rows="16" style="width: 100%;"><?php
							echo htmlspecialchars( $view_arr['simulation_result'] );
							?></textarea>
						</th>
					</tr>
				</tbody>
			</table>
			<?php wp_nonce_field( 'xfavi_nonce_action_simulated', 'xfavi_nonce_field_simulated' ); ?>
			<input class="button-primary" type="submit" name="xfavi_submit_simulated"
				value="<?php _e( 'Симуляция', 'xml-for-avito' ); ?>" />
		</form>
	</div>
</div>