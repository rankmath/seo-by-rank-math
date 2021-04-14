<?php
/**
 * The WP List Table class for the Redirections module.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Redirections;

use RankMath\Helper;
use RankMath\Helpers\Sitepress;
use MyThemeShop\Helpers\Param;
use MyThemeShop\Admin\List_Table;

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
				'singular' => 'redirection',
				'plural'   => 'redirections',
				'no_items' => $this->is_trashed_page() ? esc_html__( 'No redirections found in Trash.', 'rank-math' ) : wp_kses_post( __( 'No redirections added yet. <a href="#" class="rank-math-add-new-redirection">Add New Redirection</a>', 'rank-math' ) ),
			]
		);
	}

	/**
	 * Prepares the list of items for displaying.
	 */
	public function prepare_items() {
		global $per_page;

		$per_page = $this->get_items_per_page( 'rank_math_redirections_per_page', 100 );

		$data = DB::get_redirections(
			[
				'limit'   => $per_page,
				'order'   => $this->get_order(),
				'orderby' => $this->get_orderby( 'id' ),
				'paged'   => $this->get_pagenum(),
				'search'  => $this->get_search(),
				'status'  => Param::request( 'status', 'any' ),
			]
		);

		$this->items = $data['redirections'];

		$this->set_pagination_args(
			[
				'total_items' => $data['count'],
				'per_page'    => $per_page,
			]
		);
	}

	/**
	 * Handles the checkbox column output.
	 *
	 * @param object $item The current item.
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="redirection[]" value="%s" />', $item['id'] );
	}

	/**
	 * Handle the sources column.
	 *
	 * @param object $item The current item.
	 */
	protected function column_sources( $item ) {
		return $this->get_sources_html( maybe_unserialize( $item['sources'] ) ) . $this->column_actions( $item );
	}

	/**
	 * Handle the last accessed column.
	 *
	 * @param object $item The current item.
	 */
	protected function column_last_accessed( $item ) {
		$no_last_accessed = ( empty( $item['last_accessed'] ) || '0000-00-00 00:00:00' === $item['last_accessed'] );

		return $no_last_accessed ? '' : mysql2date( 'F j, Y, G:i', $item['last_accessed'] );
	}

	/**
	 * Handles the default column output.
	 *
	 * @param object $item        The current item.
	 * @param string $column_name The current column name.
	 */
	public function column_default( $item, $column_name ) {
		$default = apply_filters( "rank_math/redirection/admin_column_{$column_name}", false, $item );
		if ( ! empty( $default ) ) {
			return $default;
		}

		if ( in_array( $column_name, [ 'hits', 'header_code', 'url_to' ], true ) ) {
			return esc_html( $item[ $column_name ] );
		}

		return esc_html( wp_json_encode( $item, true ) );
	}

	/**
	 * Get html for sources column
	 *
	 * @param  array $sources Array of sources.
	 * @return string
	 */
	private function get_sources_html( $sources ) {
		if ( empty( $sources ) ) {
			return '';
		}

		$comparison_hash = Helper::choices_comparison_types();

		// First one.
		$html = $this->get_source_html( $sources[0], $comparison_hash );
		unset( $sources[0] );

		if ( empty( $sources ) ) {
			return $html;
		}

		// Show more button.
		$html .= ' <a href="#" class="rank-math-showmore" title="' . esc_html__( 'Show more', 'rank-math' ) . '">[&hellip;]</a>';
		$html .= '<div class="rank-math-more">';

		// Loop remaining.
		$parts = [];
		foreach ( $sources as $source ) {
			$parts[] = $this->get_source_html( $source, $comparison_hash );
		}

		$html .= join( '<br>', $parts );
		$html .= '<br><a href="#" class="rank-math-hidemore" title="' . esc_html__( 'Hide details', 'rank-math' ) . '">[' . esc_html__( 'Hide', 'rank-math' ) . ']</a>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Get html of a source.
	 *
	 * @param  array $source          Source for which render html.
	 * @param  array $comparison_hash Comparison array hash.
	 * @return string
	 */
	private function get_source_html( $source, $comparison_hash ) {
		Sitepress::get()->remove_home_url_filter();
		$html = '<span class="value-url_from"><strong><a href="' . esc_url( home_url( $source['pattern'] ) ) . '" target="_blank">' . esc_html( stripslashes( $source['pattern'] ) ) . '</a></strong></span>';
		if ( 'exact' !== $source['comparison'] ) {
			$html .= ' <span class="value-source-comparison">(' . esc_html( $comparison_hash[ $source['comparison'] ] ) . ')</span>';
		}
		Sitepress::get()->restore_home_url_filter();
		return $html;
	}

	/**
	 * Generate row actions div.
	 *
	 * @param object $item The current item.
	 */
	public function column_actions( $item ) {
		$url = esc_url(
			Helper::get_admin_url(
				'redirections',
				[
					'redirection' => $item['id'],
					'security'    => wp_create_nonce( 'redirection_list_action' ),
				]
			)
		);

		if ( $this->is_trashed_page() ) {
			return $this->row_actions(
				[
					'restore' => '<a href="' . $url . '" data-action="restore" class="rank-math-redirection-action">' . esc_html__( 'Restore', 'rank-math' ) . '</a>',
					'delete'  => '<a href="' . $url . '" data-action="delete" class="rank-math-redirection-action">' . esc_html__( 'Delete Permanently', 'rank-math' ) . '</a>',
				]
			);
		}

		return $this->row_actions(
			[
				'edit'       => '<a href="' . $url . '&action=edit" class="rank-math-redirection-edit">' . esc_html__( 'Edit', 'rank-math' ) . '</a>',
				'deactivate' => '<a href="' . $url . '" data-action="deactivate" class="rank-math-redirection-action">' . esc_html__( 'Deactivate', 'rank-math' ) . '</a>',
				'activate'   => '<a href="' . $url . '" data-action="activate" class="rank-math-redirection-action">' . esc_html__( 'Activate', 'rank-math' ) . '</a>',
				'trash'      => '<a href="' . $url . '" data-action="trash" class="rank-math-redirection-action">' . esc_html__( 'Trash', 'rank-math' ) . '</a>',
			]
		);
	}

	/**
	 * Get a list of columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return apply_filters(
			'rank_math/redirection/admin_columns',
			[
				'cb'            => '<input type="checkbox" />',
				'sources'       => esc_html__( 'From', 'rank-math' ),
				'url_to'        => esc_html__( 'To', 'rank-math' ),
				'header_code'   => esc_html__( 'Type', 'rank-math' ),
				'hits'          => esc_html__( 'Hits', 'rank-math' ),
				'last_accessed' => esc_html__( 'Last Accessed', 'rank-math' ),
			]
		);
	}

	/**
	 * Get a list of sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return [
			'url_to'        => [ 'url_to', false ],
			'header_code'   => [ 'header_code', false ],
			'hits'          => [ 'hits', false ],
			'last_accessed' => [ 'last_accessed', false ],
		];
	}

	/**
	 * Get an associative array ( option_name => option_title ) with the list
	 * of bulk actions available on this table.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		if ( $this->is_trashed_page() ) {
			$actions = [
				'restore' => esc_html__( 'Restore', 'rank-math' ),
				'delete'  => esc_html__( 'Delete Permanently', 'rank-math' ),
			];
		} else {
			$actions = [
				'activate'   => esc_html__( 'Activate', 'rank-math' ),
				'deactivate' => esc_html__( 'Deactivate', 'rank-math' ),
				'trash'      => esc_html__( 'Move to Trash', 'rank-math' ),
			];
		}

		return apply_filters( 'rank_math/redirection/bulk_actions', $actions );
	}

	/**
	 * Get an associative array ( id => link ) with the list of views available on this table.
	 *
	 * @return array
	 */
	public function get_views() {

		$url     = Helper::get_admin_url( 'redirections' );
		$current = Param::get( 'status', 'all' );
		$counts  = DB::get_counts();
		$labels  = [
			'all'      => esc_html__( 'All', 'rank-math' ),
			'active'   => esc_html__( 'Active', 'rank-math' ),
			'inactive' => esc_html__( 'Inactive', 'rank-math' ),
			'trashed'  => esc_html__( 'Trash', 'rank-math' ),
		];

		$links = [];
		foreach ( $labels as $key => $label ) {
			$links[ $key ] = sprintf(
				'<a href="%1$s"%2$s>%3$s <span class="count">(%4$s)</span></a>',
				$url . '&status=' . $key,
				$key === $current ? ' class="current"' : '',
				$label,
				number_format_i18n( $counts[ $key ] )
			);
		}

		return $links;
	}

	/**
	 * Generates content for a single row of the table.
	 *
	 * @param object $item The current item.
	 */
	public function single_row( $item ) {
		echo '<tr class="rank-math-redirection-' . ( 'inactive' === $item['status'] ? 'deactivated' : 'activated' ) . '">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Prints extra table nav.
	 *
	 * @param string $which The position. Accepts `top` or `bottom`.
	 */
	public function extra_tablenav( $which ) {
		parent::extra_tablenav( $which );

		do_action( 'rank_math/redirection/extra_tablenav', $which );

		if ( ! $this->is_trashed_page() ) {
			return;
		}

		$counts = DB::get_counts();
		if ( empty( $counts['trashed'] ) || ! intval( $counts['trashed'] ) ) {
			return;
		}

		echo '<div class="alignleft actions">';
		submit_button( esc_html__( 'Empty Trash', 'rank-math' ), '', 'delete_all', false );
		echo '</div>';
	}

	/**
	 * Checks if page status is set to trashed.
	 *
	 * @return bool
	 */
	protected function is_trashed_page() {
		return 'trashed' === Param::get( 'status' );
	}
}
