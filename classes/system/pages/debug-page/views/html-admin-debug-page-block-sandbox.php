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
		<?php _e( 'Песочница', 'xml-for-avito' ); ?>
	</h2>
	<div class="inside">
		<?php
		require_once XFAVI_PLUGIN_DIR_PATH . '/sandbox.php';
		try {
			xfavi_run_sandbox();
		} catch (Exception $e) {
			echo 'Exception: ', $e->getMessage(), "\n";
		}
		?>
	</div>
</div>