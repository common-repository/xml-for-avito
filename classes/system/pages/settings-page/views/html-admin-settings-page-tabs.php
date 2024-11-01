<?php
/**
 * Print tabs
 * 
 * @version 2.1.5 (10-10-2023)
 * @see     
 * @package 
 * 
 * @param $view_arr['tabs_arr']
 * @param $view_arr['tab_name']
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="nav-tab-wrapper" style="margin-bottom: 10px;">
	<?php
	foreach ( $view_arr['tabs_arr'] as $tab => $name ) {
		if ( $tab === $view_arr['tab_name'] ) {
			$class = ' nav-tab-active';
		} else {
			$class = '';
		}
		if ( isset( $_GET['feed_id'] ) ) {
			$nf = '&feed_id=' . sanitize_text_field( $_GET['feed_id'] );
		} else {
			$nf = '&feed_id=' . xfavi_get_first_feed_id();
		}
		printf(
			'<a class="nav-tab%1$s" href="?page=xfaviexport&tab=%2$s%3$s">%4$s</a>',
			$class, $tab, $nf, $name
		);
	}
	?>
</div>