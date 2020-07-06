<?php
/**
 * The 404 Monitor Module
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Monitor
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Monitor;

use RankMath\Helper;
use MyThemeShop\Admin\List_Table;
use RankMath\Redirections\Cache;

defined( 'ABSPATH' ) || exit;

/**
 * Table class.
 */
class Table extends List_Table {

	/**
	 * The Constructor.
	 */
	public function __construct() {

		parent::__construct(
			[
				'singular' => 'event',
				'plural'   => 'events',
				'no_items' => esc_html__( 'The 404 error log is empty.', 'rank-math' ),
			]
		);
	}

	/**
	 * Prepares the list of items for displaying.
	 */
	public function prepare_items() {
		global $per_page;

		$per_page = $this->get_items_per_page( 'rank_math_404_monitor_per_page', 100 );
		$search   = $this->get_search();

		$data = DB::get_logs(
			[
				'limit'   => $per_page,
				'order'   => $this->get_order(),
				'orderby' => $this->get_orderby( 'accessed' ),
				'paged'   => $this->get_pagenum(),
				'search'  => $search ? $search : '',
			]
		);

		$this->items = $data['logs'];

		foreach ( $this->items as $i => $item ) {
			$this->items[ $i ]['uri_decoded'] = urldecode( $item['uri'] );
		}

		$this->set_pagination_args(
			[
				'total_items' => $data['count'],
				'per_page'    => $per_page,
			]
		);
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination.
	 *
	 * @param string $which Where to show nav.
	 */
	protected function extra_tablenav( $which ) {
		if ( empty( $this->items ) ) {
			return;
		}
		?>
		<div class="alignleft actions">
			<input type="button" class="button button-link-delete action rank-math-clear-logs" value="<?php esc_attr_e( 'Clear Log', 'rank-math' ); ?>">
		</div>
		<?php
	}

	/**
	 * Handles the checkbox column output.
	 *
	 * @param object $item The current item.
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="log[]" value="%s" />', $item['id'] );
	}

	/**
	 * Handle the URI column.
	 *
	 * @param object $item The current item.
	 */
	protected function column_uri( $item ) {
		return esc_html( $item['uri_decoded'] ) . $this->column_actions( $item );
	}

	/**
	 * Handle the referer column.
	 *
	 * @param object $item The current item.
	 */
	protected function column_referer( $item ) {
		return '<a href="' . esc_url( $item['referer'] ) . '" target="_blank">' . esc_html( $item['referer'] ) . '</a>';
	}

	/**
	 * Handles the default column output.
	 *
	 * @param object $item        The current item.
	 * @param string $column_name The current column name.
	 */
	public function column_default( $item, $column_name ) {
		if ( in_array( $column_name, [ 'times_accessed', 'accessed', 'user_agent' ], true ) ) {
			return esc_html( $item[ $column_name ] );
		}

		return print_r( $item, true );
	}

	/**
	 * Generate row actions div.
	 *
	 * @param object $item The current item.
	 */
	public function column_actions( $item ) {
		$actions = [];

		if ( Helper::get_module( 'redirections' ) ) {
			$this->add_redirection_actions( $item, $actions );
		}

		$actions['delete'] = sprintf(
			'<a href="%s" class="rank-math-404-delete">' . esc_html__( 'Delete', 'rank-math' ) . '</a>',
			Helper::get_admin_url(
				'404-monitor',
				[
					'action'   => 'delete',
					'log'      => $item['id'],
					'security' => wp_create_nonce( '404_delete_log' ),
				]
			)
		);

		return $this->row_actions( $actions );
	}

	/**
	 * Add redirection actions.
	 *
	 * @param object $item    The current item.
	 * @param array  $actions Array of actions.
	 */
	private function add_redirection_actions( $item, &$actions ) {
		$redirection = Cache::get_by_url( $item['uri_decoded'] );

		if ( $redirection ) {
			$url = esc_url(
				Helper::get_admin_url(
					'redirections',
					[
						'redirection' => $redirection->redirection_id,
						'security'    => wp_create_nonce( 'redirection_list_action' ),
					]
				)
			);

			$actions['view_redirection'] = sprintf( '<a href="%s" target="_blank">' . esc_html__( 'View Redirection', 'rank-math' ) . '</a>', $url );
			return;
		}

		$url = esc_url(
			Helper::get_admin_url(
				'redirections',
				[
					'url' => $item['uri_decoded'],
				]
			)
		);

		$actions['redirect'] = sprintf(
			'<a href="%1$s" class="rank-math-404-redirect-btn">%2$s</a>',
			$url,
			esc_html__( 'Redirect', 'rank-math' )
		);
	}

	/**
	 * Get a list of columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = [
			'cb'             => '<input type="checkbox" />',
			'uri'            => esc_html__( 'URI', 'rank-math' ),
			'referer'        => esc_html__( 'Referer', 'rank-math' ),
			'user_agent'     => esc_html__( 'User-Agent', 'rank-math' ),
			'times_accessed' => esc_html__( 'Hits', 'rank-math' ),
			'accessed'       => esc_html__( 'Access Time', 'rank-math' ),
		];

		if ( 'simple' === Helper::get_settings( 'general.404_monitor_mode' ) ) {
			unset( $columns['referer'], $columns['user_agent'] );
			return $columns;
		}

		unset( $columns['times_accessed'] );
		return $columns;
	}

	/**
	 * Get a list of sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return [
			'uri'            => [ 'uri', false ],
			'times_accessed' => [ 'times_accessed', false ],
			'accessed'       => [ 'accessed', false ],
		];
	}

	/**
	 * Get an associative array ( option_name => option_title ) with the list
	 * of bulk actions available on this table.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'redirect' => esc_html__( 'Redirect', 'rank-math' ),
			'delete'   => esc_html__( 'Delete', 'rank-math' ),
		];

		if ( ! Helper::get_module( 'redirections' ) ) {
			unset( $actions['redirect'] );
		}

		return $actions;
	}
}
