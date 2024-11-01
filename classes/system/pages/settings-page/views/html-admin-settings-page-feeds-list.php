<?php
/**
 * Print feeds list
 * 
 * @version 2.1.0 (10-08-2023)
 * @see     
 * @package 
 */
defined( 'ABSPATH' ) || exit;

$feed_list_table = new XFAVI_Settings_Page_Feeds_WP_List_Table(); ?>
<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post" enctype="multipart/form-data">
	<?php wp_nonce_field( 'xfavi_nonce_action_add_new_feed', 'xfavi_nonce_field_add_new_feed' ); ?>
	<input class="button" type="submit" name="xfavi_submit_add_new_feed"
		value="<?php _e( 'Добавить фид', 'xml-for-avito' ); ?>" />
</form>
<?php $feed_list_table->print_html_form();