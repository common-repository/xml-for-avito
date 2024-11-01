<?php
/**
 * Print Debug page
 * 
 * @version 2.1.0 (10-08-2023)
 * @see     
 * @package 
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="postbox">
	<h2 class="hndle">
		<?php _e( 'Возможные проблемы', 'xml-for-avito' ); ?>
	</h2>
	<div class="inside">
		<?php
		$possible_problems_arr = XFAVI_Debug_Page::get_possible_problems_list();
		;
		if ( $possible_problems_arr[1] > 0 ) {
			printf( '<ol>%s</ol>', $possible_problems_arr[0] );
		} else {
			printf( '<p>%s</p>', __( 'Функции самодиагностики не выявили потенциальных проблем', 'xml-for-avito' ) );
		}
		?>
	</div>
</div>