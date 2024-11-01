<?php
/**
 * Print info block // TODO: удалить (v2.4.8-02-10-24)
 * 
 * @version 2.1.5 (10-10-2023)
 * @see     
 * @package 
 * 
 * @param $view_arr['feed_id']
 * @param $view_arr['prefix_feed'],
 * @param $view_arr['current_blog_id']
 */
defined( 'ABSPATH' ) || exit;

$status_sborki = (int) xfavi_optionGET( 'xfavi_status_sborki', $view_arr['feed_id'] );
$feed_url = urldecode( common_option_get( 'xfavi_file_url', false, $view_arr['feed_id'], 'xfavi' ) );
$date_sborki = common_option_get( 'xfavi_date_sborki', false, $view_arr['feed_id'], 'xfavi' );
$date_sborki_end = common_option_get( 'xfavi_date_sborki_end', false, $view_arr['feed_id'], 'xfavi' );
$count_products_in_feed = common_option_get( 'xfavi_count_products_in_feed', false, $view_arr['feed_id'], 'xfavi' );
$assignment = common_option_get( 'xfavi_feed_assignment', false, $view_arr['feed_id'], 'xfavi' );
$utm = sprintf(
	'?utm_source=%1$s&utm_medium=organic&utm_campaign=in-plugin-%1$s&utm_content=settings&utm_term=main-instruction',
	'xml-for-avito'
);
?>
<div class="postbox">
	<h2 class="hndle">
		<?php
		if ( ! empty( $assignment ) ) {
			$assignment = '(' . $assignment . ')';
		}
		printf( '%s: %sfeed-avito-%s.xml %s',
			__( 'Фид', 'xml-for-avito' ),
			$view_arr['prefix_feed'],
			$view_arr['current_blog_id'],
			$assignment
		); ?>
		<?php if ( empty( $feed_url ) ) : ?>
			<?php _e( 'ещё не создавался', 'xml-for-avito' ); ?>
		<?php else : ?>
			<?php if ( $status_sborki !== -1 ) : ?>
				<?php _e( 'обновляется', 'xml-for-avito' ); ?>
			<?php else : ?>
				<?php _e( 'создан', 'xml-for-avito' ); ?>
			<?php endif; ?>
		<?php endif; ?>
	</h2>
	<div class="inside">
		<p><strong style="color: green;">
				<?php _e( 'Инструкция', 'xml-for-avito' ); ?>:
			</strong> <a href="https://icopydoc.ru/kak-sozdat-fid-dlya-avito-instruktsiya/<?php echo $utm; ?>"
				target="_blank">
				<?php _e( 'Как создать XML фид', 'xml-for-avito' ); ?>
			</a></p>
		<?php if ( empty( $feed_url ) ) : ?>
			<?php if ( $status_sborki !== -1 ) : ?>
				<p>
					<?php _e(
						'Идет автоматическое создание файла. XML-фид в скором времени будет создан',
						'xml-for-avito'
					); ?>.
				</p>
			<?php else :
				printf( '<p><span class="xfavi_bold">%s. %s: %s. %s</span></p><p>%s</p><p>%s</p>',
					__(
						'Перейдите в "Товары" -> "Категории"', 'xml-for-avito' ),
					__(
						'Отредактируйте имющиеся у вас на сайте категории выбрав соответсвующие значения напротив пунктов',
						'xml-for-avito'
					),
					__(
						'"Обрабатывать согласно правилам Авито", "Авито Category" и "Авито GoodsType"',
						'xml-for-avito'
					),
					__( 'Заполните все поля на вкладке "Данные магазина"', 'xml-for-avito' ),
					__(
						'На вклакде "Автоматическое создание файла" выставите значение, отличное от значения "отключено" и нажмите "Сохранить"',
						'xml-for-avito'
					),
					__(
						'Через 1 - 7 минут (зависит от числа товаров), фид будет сгенерирован и вместо данного сообщения появится ссылка',
						'xml-for-avito'
					)
				);
			endif; ?>
		<?php else : ?>
			<?php if ( $status_sborki !== -1 ) : ?>
				<p>
					<?php _e( 'Идет автоматическое создание файла. XML-фид в скором времени будет создан', 'xml-for-avito' ); ?>.
				</p>
			<?php else : ?>
				<p><span class="xfavi_bold">
						<?php _e( 'Ваш фид здесь', 'xml-for-avito' ); ?>:
					</span>
					<br /><a target="_blank" href="<?php echo $feed_url; ?>">
						<?php echo $feed_url; ?>
					</a>
					<br />
					<?php _e( 'Размер файла', 'xml-for-avito' ); ?>:
					<?php clearstatcache();
					$feed_file_meta = new XFAVI_Feed_File_Meta( $view_arr['feed_id'] );
					$filenamefeed = sprintf( '%1$s/xml-for-avito/%2$s.%3$s',
						XFAVI_SITE_UPLOADS_DIR_PATH,
						$feed_file_meta->get_feed_filename(),
						$feed_file_meta->get_feed_extension()
					);
					if ( is_file( $filenamefeed ) ) {
						echo get_format_filesize( filesize( $filenamefeed ) );
					} else {
						echo $filenamefeed . '0 KB';
					} ?>
					<br />
					<?php _e( 'Начало генерации', 'xml-for-avito' ); ?>:
					<?php echo $date_sborki; ?>
					<br />
					<?php _e( 'Сгенерирован', 'xml-for-avito' ); ?>:
					<?php echo $date_sborki_end; ?>
					<br />
					<?php _e( 'Товаров', 'xml-for-avito' ); ?>:
					<?php echo $count_products_in_feed; ?>
				</p>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>