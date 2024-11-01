<?php
/**
 * Print the Save button
 * 
 * @version 2.1.0 (10-08-2023)
 * @see     
 * @package 
 * 
 * @param $view_arr['tab_name']
 */
defined( 'ABSPATH' ) || exit;

if ( $view_arr['tab_name'] === 'no_submit_tab' ) {
	return;
}
?>
<div class="postbox">
	<div class="inside">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="button-primary"></label></th>
					<td class="overalldesc">
						<?php wp_nonce_field( 'xfavi_nonce_action', 'xfavi_nonce_field' ); ?>
						<input id="button-primary" class="button-primary" name="xfavi_submit_action" type="submit"
							value="<?php
							if ( $view_arr['tab_name'] === 'main_tab' ) {
								printf( '%s & %s',
									__( 'Сохранить', 'xml-for-avito' ),
									__( 'Создать фид', 'xml-for-avito' )
								);
							} else {
								_e( 'Сохранить', 'xml-for-avito' );
							}
							?>" /><br />
						<span class="description">
							<small>
								<?php _e( 'Нажмите, чтобы сохранить настройки', 'xml-for-avito' ); ?>
							</small>
						</span>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>