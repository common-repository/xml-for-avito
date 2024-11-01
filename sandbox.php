<?php defined( 'ABSPATH' ) || exit;
/**
 * Sandbox function.
 * 
 * @since 0.1.0
 * @version 2.5.1 (29-10-2024)
 *
 * @return void
 */
function xfavi_run_sandbox() {

	$x = false; // установите true, чтобы использовать песочницу
	if ( true === $x ) {
		printf( '%s<br/>',
			esc_html__( 'Песочница работает. Результат появится ниже', 'xml-for-avito' )
		);
		$time_start = microtime( true );
		/* вставьте ваш код ниже */
		// Example:
		// $product = wc_get_product(8303);
		// echo $product->get_price();

		/* дальше не редактируем */
		$time_end = microtime( true );
		$time = $time_end - $time_start;
		printf( '<br/>%s<br/>%s %d %s',
			esc_html__( __( 'Песочница работает правильно', 'xml-for-avito' ) ),
			esc_html__( __( 'Время выполнения тестового скрипта составило', 'xml-for-avito' ) ),
			esc_html( $time ),
			esc_html__( __( 'секунд', 'xml-for-avito' ) )
		);
	} else {
		printf( '%s sanbox.php',
			esc_html__( 'Песочница не активна. Чтобы активировать ее, отредактируйте файл', 'xml-for-avito' )
		);
	}

}