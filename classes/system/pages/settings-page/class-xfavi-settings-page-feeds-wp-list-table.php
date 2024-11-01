<?php
/**
 * The this class manages the list of feeds
 *
 * @package                 
 * @subpackage              XML for Avito
 * @since                   0.1.0
 * 
 * @version                 2.4.12 (03-10-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     https://2web-master.ru/wp_list_table-%E2%80%93-poshagovoe-rukovodstvo.html 
 *                          https://wp-kama.ru/function/wp_list_table
 * 
 * @param         
 *
 * @depends                 classes:    WP_List_Table
 *                                      XFAVI_Data_Arr
 *                          traits:     
 *                          methods:    
 *                          functions:  common_option_get 
 *                                      xfavi_optionGET
 *                          constants:  
 *                          options:    
 */
defined( 'ABSPATH' ) || exit;

class XFAVI_Settings_Page_Feeds_WP_List_Table extends WP_List_Table {
	/**	
	 * The this class manages the list of feeds
	 */
	function __construct() {
		global $status, $page;
		parent::__construct( [ 
			// По умолчанию: '' ($this->screen->base); Название для множественного числа, используется во всяких
			// заголовках, например в css классах, в заметках, например 'posts', тогда 'posts' будет добавлен в класс table.
			'plural' => '',

			// По умолчанию: ''; Название для единственного числа, например 'post'. 
			'singular' => '',

			// По умолчанию: false; Должна ли поддерживать таблица AJAX. Если true, класс будет вызывать метод 
			// _js_vars() в подвале, чтобы передать нужные переменные любому скрипту обрабатывающему AJAX события.
			'ajax' => false,

			// По умолчанию: null; Строка содержащая название хука, нужного для определения текущей страницы. 
			// Если null, то будет установлен текущий экран.
			'screen' => null
		] );

		add_action( 'admin_footer', [ $this, 'print_style_footer' ] ); // меняем ширину колонок
	}

	/**	
	 * Печатает форму
	 * 
	 * @return void
	 */
	public function print_html_form() {
		echo '<form method="get"><input type="hidden" name="xfavi_form_id" value="xfavi_wp_list_table" />';
		wp_nonce_field( 'xfavi_nonce_action_f', 'xfavi_nonce_field_f' );
		printf( '<input type="hidden" name="page" value="%s" />', esc_attr( $_REQUEST['page'] ) );
		$this->prepare_items();
		$this->display();
		echo '</form>';
	}

	/**	
	 * Сейчас у таблицы стандартные стили WordPress. Чтобы это исправить, вам нужно адаптировать классы CSS, которые
	 * были автоматически применены к каждому столбцу. Название класса состоит из строки «column-» и ключевого имени 
	 * массива $columns, например «column-isbn» или «column-author».
	 * В качестве примера мы переопределим ширину столбцов (для простоты, стили прописаны непосредственно 
	 * в HTML разделе head)
	 * 
	 * @return void
	 */
	public function print_style_footer() {
		print ( '<style type="text/css">#xfavi_feed_id, .column-xfavi_feed_id {width: 7%;}</style>' );
	}

	/**
	 * Метод get_columns() необходим для маркировки столбцов внизу и вверху таблицы. 
	 * Ключи в массиве должны быть теми же, что и в массиве данных, 
	 * иначе соответствующие столбцы не будут отображены.
	 * 
	 * @return array
	 */
	function get_columns() {
		$columns = [ 
			'cb' => '<input type="checkbox" />', // флажок сортировки. см get_bulk_actions и column_cb
			'xfavi_feed_id' => __( 'Фид ID', 'xml-for-avito' ),
			'xfavi_url_xml_file' => __( 'Файл XML', 'xml-for-avito' ),
			'xfavi_run_cron' => __( 'Автоматическое создание файла', 'xml-for-avito' ),
			'xfavi_step_export' => __( 'Шаг экспорта', 'xml-for-avito' ),
			'xfavi_date_sborki_end' => __( 'Сгенерирован', 'xml-for-avito' ),
			'xfavi_count_products_in_feed' => __( 'Товаров', 'xml-for-avito' )
		];
		return $columns;
	}

	/**	
	 * Метод вытаскивает из БД данные, которые будут лежать в таблице
	 * $this->table_data();
	 * 
	 * @return array
	 */
	private function table_data() {
		$xfavi_settings_arr = common_option_get( 'xfavi_settings_arr' );
		$result_arr = [];
		if ( $xfavi_settings_arr == '' || empty( $xfavi_settings_arr ) ) {
			return $result_arr;
		}
		$xfavi_settings_arr_keys_arr = array_keys( $xfavi_settings_arr );
		for ( $i = 0; $i < count( $xfavi_settings_arr_keys_arr ); $i++ ) {
			$key = $xfavi_settings_arr_keys_arr[ $i ];

			$text_column_xfavi_feed_id = $key;

			if ( $xfavi_settings_arr[ $key ]['xfavi_file_url'] === '' ) {
				$text_column_xfavi_url_xml_file = __( 'Ещё не создавался', 'xml-for-avito' );
			} else {
				$text_column_xfavi_url_xml_file = sprintf(
					'%1$s:<br /><a target="_blank" href="%2$s">%2$s</a><br />%3$s:<br /><a target="_blank" href="%4$s">%4$s</a>',
					__( 'Товарный фид', 'xml-for-avito' ),
					urldecode( $xfavi_settings_arr[ $key ]['xfavi_file_url'] ),
					__( 'Фид остатков', 'xml-for-avito' ),
					urldecode( $xfavi_settings_arr[ $key ]['xfavi_stock_file_url'] )
				);
			}
			if ( $xfavi_settings_arr[ $key ]['xfavi_feed_assignment'] === '' ) {

			} else {
				$text_column_xfavi_url_xml_file = sprintf( '%1$s<br/>(%2$s: %3$s)',
					$text_column_xfavi_url_xml_file,
					__( 'Назначение фида', 'xml-for-avito' ),
					$xfavi_settings_arr[ $key ]['xfavi_feed_assignment']
				);
			}

			$xfavi_status_cron = $xfavi_settings_arr[ $key ]['xfavi_status_cron'];
			switch ( $xfavi_status_cron ) {
				case 'off':
					$text_status_cron = __( 'Отключено', 'xml-for-avito' );
					break;
				case 'once':
					$text_status_cron = sprintf( '%s (%s)',
						__( 'Создать фид один раз', 'xml-for-avito' ),
						__( 'запустить сейчас', 'xml-for-avito' )
					);
					break;
				case 'hourly':
					$text_status_cron = __( 'Раз в час', 'xml-for-avito' );
					break;
				case 'six_hours':
					$text_status_cron = __( 'Каждые 6 часов', 'xml-for-avito' );
					break;
				case 'twicedaily':
					$text_status_cron = __( '2 раза в день', 'xml-for-avito' );
					break;
				case 'daily':
					$text_status_cron = __( 'Раз в день', 'xml-for-avito' );
					break;
				case 'week':
					$text_status_cron = __( 'Раз в неделю', 'xml-for-avito' );
					break;
				default:
					$text_status_cron = __( 'Отключено', 'xml-for-avito' );
			}

			$cron_info = wp_get_scheduled_event( 'xfavi_cron_sborki', [ (string) $key ] );
			if ( false === $cron_info ) {
				$cron_info = wp_get_scheduled_event( 'xfavi_cron_period', [ (string) $key ] );
				if ( false === $cron_info ) {
					$text_column_xfavi_run_cron = sprintf( '%s<br/><small>%s</small>',
						$text_status_cron,
						__( 'Нет запланированных CRON задач на сборку фида', 'xml-for-avito' )
					);
				} else {
					$text_column_xfavi_run_cron = sprintf( '%s<br/><small>%s:<br/>%s</small>',
						$text_status_cron,
						__( 'Следующая сборка фида запланирована на', 'xml-for-avito' ),
						wp_date( 'Y-m-d H:i:s', $cron_info->timestamp )
					);
				}

			} else {
				$after_time = $cron_info->timestamp - current_time( 'timestamp', 1 );
				if ( $after_time < 0 ) {
					$after_time = 0;
				}
				$text_column_xfavi_run_cron = sprintf( '%s<br/><small>%s...<br/>%s:<br/>%s (%s %s %s)</small>',
					$text_status_cron,
					__( 'Фид создается', 'xml-for-avito' ),
					__( 'Следующий шаг запланирован на', 'xml-for-avito' ),
					wp_date( 'Y-m-d H:i:s', $cron_info->timestamp ),
					__( 'через', 'xml-for-avito' ),
					$after_time,
					__( 'сек', 'xml-for-avito' )
				);
			}

			$text_date_sborki_end = $xfavi_settings_arr[ $key ]['xfavi_date_sborki_end'];
			if ( isset( $xfavi_settings_arr[ $key ]['xfavi_critical_errors'] ) ) {
				$text_date_sborki_end .= '<br/>' . $xfavi_settings_arr[ $key ]['xfavi_critical_errors'];
			}

			if ( $xfavi_settings_arr[ $key ]['xfavi_count_products_in_feed'] === '-1' ) {
				$text_count_products_in_feed = '-';
			} else {
				$text_count_products_in_feed = $xfavi_settings_arr[ $key ]['xfavi_count_products_in_feed'];
			}

			$result_arr[ $i ] = [ 
				'xfavi_feed_id' => $text_column_xfavi_feed_id,
				'xfavi_url_xml_file' => $text_column_xfavi_url_xml_file,
				'xfavi_run_cron' => $text_column_xfavi_run_cron,
				'xfavi_step_export' => $xfavi_settings_arr[ $key ]['xfavi_step_export'],
				'xfavi_date_sborki_end' => $xfavi_settings_arr[ $key ]['xfavi_date_sborki_end'],
				'xfavi_count_products_in_feed' => $xfavi_settings_arr[ $key ]['xfavi_count_products_in_feed']
			];
		}

		return $result_arr;
	}

	/**
	 * @see https://2web-master.ru/wp_list_table-%E2%80%93-poshagovoe-rukovodstvo.html#screen-options
	 * 
	 * prepare_items определяет два массива, управляющие работой таблицы:
	 * $hidden - определяет скрытые столбцы
	 * $sortable - определяет, может ли таблица быть отсортирована по этому столбцу.
	 *
	 * @return void
	 */
	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = [];
		$sortable = $this->get_sortable_columns(); // вызов сортировки
		$this->_column_headers = [ $columns, $hidden, $sortable ];
		// пагинация 
		$per_page = 5;
		$current_page = $this->get_pagenum();
		$total_items = count( $this->table_data() );
		$found_data = array_slice( $this->table_data(), ( ( $current_page - 1 ) * $per_page ), $per_page );
		$this->set_pagination_args( [ 
			'total_items' => $total_items, // Мы должны вычислить общее количество элементов
			'per_page' => $per_page // Мы должны определить, сколько элементов отображается на странице
		] );
		// end пагинация 
		$this->items = $found_data; // $this->items = $this->table_data() // Получаем данные для формирования таблицы
	}

	/**
	 * Данные таблицы.
	 * Наконец, метод назначает данные из примера на переменную представления данных класса — items.
	 * Прежде чем отобразить каждый столбец, WordPress ищет методы типа column_{key_name}, например, 
	 * function column_xfavi_url_xml_file. Такой метод должен быть указан для каждого столбца. Но чтобы не создавать 
	 * эти методы для всех столбцов в отдельности, можно использовать column_default. Эта функция обработает все 
	 * столбцы, для которых не определён специальный метод
	 * 
	 * @return string
	 */
	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'xfavi_feed_id':
			case 'xfavi_url_xml_file':
			case 'xfavi_run_cron':
			case 'xfavi_step_export':
			case 'xfavi_date_sborki_end':
			case 'xfavi_count_products_in_feed':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); // Мы отображаем целый массив во избежание проблем
		}
	}

	/**
	 * Функция сортировки.
	 * Второй параметр в массиве значений $sortable_columns отвечает за порядок сортировки столбца. 
	 * Если значение true, столбец будет сортироваться в порядке возрастания, если значение false, столбец 
	 * сортируется в порядке убывания, или не упорядочивается. Это необходимо для маленького треугольника около 
	 * названия столбца, который указывает порядок сортировки, чтобы строки отображались в правильном направлении
	 * 
	 * @return array
	 */
	function get_sortable_columns() {
		$sortable_columns = [ 
			'xfavi_url_xml_file' => [ 'xfavi_url_xml_file', false ]
		];
		return $sortable_columns;
	}

	/**
	 * Действия.
	 * Эти действия появятся, если пользователь проведет курсор мыши над таблицей
	 * column_{key_name} - в данном случае для колонки xfavi_url_xml_file - function column_xfavi_url_xml_file
	 * 
	 * @return string
	 */
	function column_xfavi_url_xml_file( $item ) {
		$actions = [ 
			'edit' => sprintf( '<a href="?page=%s&action=%s&feed_id=%s">%s</a>',
				$_REQUEST['page'],
				'edit',
				$item['xfavi_feed_id'],
				esc_html__( 'Редактировать', 'xml-for-avito' )
			),
			'duplicate' => sprintf( '<a href="?page=%s&action=%s&feed_id=%s&_wpnonce=%s">%s</a>',
				$_REQUEST['page'],
				'duplicate',
				$item['xfavi_feed_id'],
				wp_create_nonce( 'nonce_duplicate' . $item['xfavi_feed_id'] ),
				esc_html__( 'Дублировать', 'xml-for-avito' )
			)
		];

		$xml_file_url = common_option_get( 'xfavi_file_url', false, $item['xfavi_feed_id'], 'xfavi' );
		if ( ! empty( $xml_file_url ) ) {
			$actions['download_file_url'] = sprintf( '<a href="%s" download>%s %s</a>',
				esc_attr( urldecode( $xml_file_url ) ),
				esc_html__( 'Скачать', 'xml-for-avito' ),
				esc_html__( 'Товарный фид', 'xml-for-avito' )
			);
		}
		$xml_stock_file_url = common_option_get( 'xfavi_file_url', false, $item['xfavi_feed_id'], 'xfavi' );
		if ( ! empty( $xml_stock_file_url ) ) {
			$actions['xml_stock_file_url'] = sprintf( '<a href="%s" download>%s %s</a>',
				esc_attr( urldecode( $xml_stock_file_url ) ),
				esc_html__( 'Скачать', 'xml-for-avito' ),
				esc_html__( 'Фид остатков', 'xml-for-avito' )
			);
		}

		return sprintf( '%1$s %2$s', $item['xfavi_url_xml_file'], $this->row_actions( $actions ) );
	}

	/**
	 * Массовые действия.
	 * Bulk action осуществляются посредством переписывания метода get_bulk_actions() и возврата связанного массива
	 * Этот код просто помещает выпадающее меню и кнопку «применить» вверху и внизу таблицы
	 * ВАЖНО! Чтобы работало нужно оборачивать вызов класса в form:
	 * <form id="events-filter" method="get"> 
	 * <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" /> 
	 * <?php $wp_list_table->display(); ?> 
	 * </form> 
	 * 
	 * @return array
	 */
	function get_bulk_actions() {
		$actions = [ 
			'delete' => __( 'Удалить', 'xml-for-avito' )
		];
		return $actions;
	}

	/**
	 * Флажки для строк должны быть определены отдельно. Как упоминалось выше, есть метод column_{column} для 
	 * отображения столбца. cb-столбец – особый случай:
	 * 
	 * @param array $item
	 * 
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="checkbox_xml_file[]" value="%s" />', $item['xfavi_feed_id']
		);
	}

	/**
	 * Нет элементов.
	 * Если в списке нет никаких элементов, отображается стандартное сообщение «No items found.». Если вы хотите 
	 * изменить это сообщение, вы можете переписать метод no_items()
	 * 
	 * @return void
	 */
	function no_items() {
		esc_html_e( 'XML фиды не найдены', 'xml-for-avito' );
	}
}