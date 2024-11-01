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
<div class="wrap">
	<h1>
		<?php
		printf( '%s v. %s', __( 'Страница отладки', 'xml-for-avito' ), esc_html( univ_option_get( 'xfavi_version' ) ) );
		?>
	</h1>
	<?php do_action( 'my_admin_notices' ); ?>
	<div id="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder">
			<div id="postbox-container-1" class="postbox-container">
				<div class="meta-box-sortables">
					<?php include_once __DIR__ . '/html-admin-debug-page-block-logs.php'; ?>
				</div>
			</div>
			<div id="postbox-container-2" class="postbox-container">
				<div class="meta-box-sortables">
					<?php include_once __DIR__ . '/html-admin-debug-page-block-simulation.php'; ?>
				</div>
			</div>
			<div id="postbox-container-3" class="postbox-container">
				<div class="meta-box-sortables">
					<?php include_once __DIR__ . '/html-admin-debug-page-block-possible-problems.php'; ?>
					<?php include_once __DIR__ . '/html-admin-debug-page-block-sandbox.php'; ?>
				</div>
			</div>
			<div id="postbox-container-4" class="postbox-container">
				<div class="meta-box-sortables">
					<?php
					do_action( 'xfavi_before_support_project' );
					do_action( 'xfavi_feedback_block' );
					?>
				</div>
			</div>
		</div>
	</div>
</div>