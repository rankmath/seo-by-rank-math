<?php
/**
 * The List Table Base CLass.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     RankMath <support@rankmath.com>
 */

namespace RankMath\Admin;

use WP_List_Table;
use RankMath\Helpers\Param;

/**
 * List_Table class.
 */
class List_Table extends WP_List_Table {
	/**
	 * Message to be displayed when there are no items.
	 */
	public function no_items() {
		echo isset( $this->_args['no_items'] ) ? wp_kses_post( $this->_args['no_items'] ) : esc_html__( 'No items found.', 'rank-math' );
	}

	/**
	 * Get order setting.
	 *
	 * @return string
	 */
	protected function get_order() {
		$order = Param::request( 'order', 'desc' );
		return in_array( $order, [ 'desc', 'asc' ], true ) ? strtoupper( $order ) : 'DESC';
	}

	/**
	 * Get orderby setting.
	 *
	 * @param string $default_value (Optional) Extract order by from request.
	 *
	 * @return string
	 */
	protected function get_orderby( $default_value = 'create_date' ) {
		return Param::get( 'orderby', $default_value, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK );
	}

	/**
	 * Get search query variable.
	 *
	 * @return bool|string
	 */
	protected function get_search() {
		return Param::request( 's', false, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_BACKTICK );
	}

	/**
	 * Set column headers.
	 *
	 * @codeCoverageIgnore
	 */
	protected function set_column_headers() {
		$this->_column_headers = [
			$this->get_columns(),
			[],
			$this->get_sortable_columns(),
		];
	}
}
