<?php 
/**
 * Print the tab another page
 * 
 * @version 2.1.0 (10-08-2023)
 * @see     
 * @package 
 * 
 * @param $view_arr['feed_id']
 * @param $view_arr['tab_name']
 */
defined( 'ABSPATH' ) || exit;

$args_arr = [ 'tab_name' => $view_arr['tab_name'], 'feed_id' => $view_arr['feed_id'] ];
$args_arr = apply_filters( 'xfavi_f_another_tab_args_arr', $args_arr );
?>
<div class="postbox">
	<?php do_action_ref_array( 'xfavi_a_before_another_tab', $args_arr ); ?>
	<div class="inside">
		<table class="form-table" role="presentation">
			<tbody>
				<?php do_action_ref_array( 'xfavi_a_prepend_another_tab', $args_arr ); ?>
				<?php XFAVI_Settings_Page::print_view_html_fields( $view_arr['tab_name'], $view_arr['feed_id'] ); ?>
				<?php do_action_ref_array( 'xfavi_a_append_another_tab', $args_arr ); ?>
			</tbody>
		</table>
	</div>
	<?php do_action_ref_array( 'xfavi_a_after_another_tab', $args_arr ); ?>
</div>
<?php
do_action( 'xfavi_switch_get_tab', [ 'tab_name' => $view_arr['tab_name'], 'feed_id' => $view_arr['feed_id'] ] );