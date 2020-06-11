<?php
/**
 * The List Table Base CLass.
 *
 * @since      1.0.0
 * @package    MyThemeShop
 * @subpackage MyThemeShop\Admin
 * @author     MyThemeShop <admin@mythemeshop.com>
 */

namespace MyThemeShop\Admin;

use WP_List_Table;
use MyThemeShop\Helpers\Param;

/**
 * List_Table class.
 */
class List_Table extends WP_List_Table {

	/**
	 * The Constructor.
	 *
	 * @param array $args Array of arguments.
	 */
	public function __construct( $args = [] ) {
		parent::__construct( $args );
	}

	/**
	 * Message to be displayed when there are no items.
	 */
	public function no_items() {
		echo isset( $this->_args['no_items'] ) ? $this->_args['no_items'] : esc_html__( 'No items found.' );
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
	 * @param string $default (Optional) Extract order by from request.
	 *
	 * @return string
	 */
	protected function get_orderby( $default = 'create_date' ) {
		return Param::get( 'orderby', $default, FILTER_SANITIZE_STRING );
	}

	/**
	 * Get search query variable.
	 *
	 * @return bool|string
	 */
	protected function get_search() {
		return Param::request( 's', false, FILTER_SANITIZE_STRING );
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
