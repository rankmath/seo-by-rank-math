<?php
/**
 * Analytics List
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Search_Console;

use RankMath\Helper;
use MyThemeShop\Admin\List_Table;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Analytics_List class.
 */
class Analytics_List extends List_Table {

	/**
	 * Hold current filters applied.
	 *
	 * @var array
	 */
	private $filters;

	/**
	 * Hold old items.
	 *
	 * @var array
	 */
	private $old_items;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		parent::__construct(
			[
				'singular' => 'analytic',
				'plural'   => 'analytics',
				'no_items' => esc_html__( 'No data.', 'rank-math' ),
			]
		);

		$this->filters = Helper::search_console()->get_filters();
	}

	/**
	 * Prepares the list of items for displaying.
	 */
	public function prepare_items() {
		global $per_page;

		$per_page = $this->get_items_per_page( 'rank_math_sc_analytics_per_page', 30 );
		$this->set_column_headers();
		$data = DB::get_data(
			array_merge(
				$this->filters,
				[
					'orderby' => $this->get_orderby( 'clicks' ),
					'order'   => $this->get_order(),
					'limit'   => $per_page,
					'paged'   => $this->get_pagenum(),
					'search'  => $this->get_search() ? $this->get_search() : '',
				]
			)
		);

		$this->items     = $data['rows'];
		$this->old_items = $data['old_rows'];

		$this->set_pagination_args(
			[
				'total_items' => $data['count'],
				'per_page'    => $per_page,
			]
		);

		unset( $data );
	}

	/**
	 * Handle the property column.
	 *
	 * @param object $item The current item.
	 */
	protected function column_property( $item ) {
		return 'query' === $this->filters['dimension'] ? $item['property'] :
			'<a href="' . esc_url( $item['property'] ) . '">' . $item['property'] . '</a>';
	}

	/**
	 * Handles the default column output.
	 *
	 * @param object $item        The current item.
	 * @param string $column_name The current column name.
	 */
	public function column_default( $item, $column_name ) {
		if ( in_array( $column_name, [ 'clicks', 'impressions', 'ctr', 'position' ], true ) ) {

			$current    = in_array( $column_name, [ 'ctr', 'position' ], true ) ? round( $item[ $column_name ], 2 ) : $item[ $column_name ];
			$inverted   = 'position' === $column_name ? true : false;
			$percentage = 'ctr' === $column_name ? true : false;
			return isset( $this->old_items[ $item['property'] ] ) ? self::diff_label( $current, $this->old_items[ $item['property'] ][ $column_name ], $inverted, $percentage ) : $current;
		}

		return print_r( $item, true );
	}

	/**
	 * Get a list of columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return [
			'property'    => 'query' === $this->filters['dimension'] ? esc_html__( 'Keywords', 'rank-math' ) : esc_html__( 'Pages', 'rank-math' ),
			'clicks'      => esc_html__( 'Clicks', 'rank-math' ),
			'impressions' => esc_html__( 'Impressions', 'rank-math' ),
			'ctr'         => esc_html__( 'CTR', 'rank-math' ),
			'position'    => esc_html__( 'Position', 'rank-math' ),
		];
	}

	/**
	 * Get a list of sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return [
			'property'    => [ 'property', false ],
			'clicks'      => [ 'clicks', false ],
			'impressions' => [ 'impressions', false ],
			'ctr'         => [ 'ctr', false ],
			'position'    => [ 'position', false ],
		];
	}

	/**
	 * Create difference label for display.
	 *
	 * @param integer $current    Current value.
	 * @param integer $previous   Previous value to compare with.
	 * @param boolean $inverted   Invert the result.
	 * @param boolean $percentage Show as percentage.
	 *
	 * @return string
	 */
	public static function diff_label( $current, $previous = 0, $inverted = false, $percentage = false ) {
		$diff = Admin_Helper::compare_values( $previous, $current );
		if ( 0 === $diff ) {
			return '<span class="compare-value">' . $current . '</span>';
		}

		$downward = $inverted ? 'up' : 'down';
		$upward   = $inverted ? 'down' : 'up';
		$class    = $diff < 0 ? $downward : $upward;

		return sprintf(
			'<span class="compare-value value-%1$s" title="%2$s"><i class="dashicons dashicons-arrow-%1$s-alt"></i> %3$s <small>%4$s%5$s</small></span>',
			$class,
			/* translators: previous value */
			esc_attr( sprintf( esc_html__( 'Previously: %s', 'rank-math' ), $previous ) ),
			$current,
			$inverted ? abs( $diff ) : ( $diff < 0 ? $diff : '+' . $diff ),
			( $percentage ? '%' : '' )
		);
	}
}
