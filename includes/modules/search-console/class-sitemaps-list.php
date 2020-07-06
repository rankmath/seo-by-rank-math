<?php
/**
 * Sitemaps List
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Search_Console;

use RankMath\Helper;
use MyThemeShop\Admin\List_Table;

defined( 'ABSPATH' ) || exit;

/**
 * Sitemaps_List class.
 */
class Sitemaps_List extends List_Table {

	/**
	 * The Constructor.
	 */
	public function __construct() {

		parent::__construct(
			[
				'singular' => 'sitemap',
				'plural'   => 'sitemaps',
				'no_items' => esc_html__( 'No sitemaps submitted.', 'rank-math' ),
			]
		);
	}

	/**
	 * Prepares the list of items for displaying.
	 */
	public function prepare_items() {

		$this->set_column_headers();
		$with_index  = ! Helper::search_console()->sitemaps->selected_site_is_domain_property();
		$this->items = Helper::search_console()->sitemaps->get_sitemaps( $with_index );

		$this->set_pagination_args(
			[
				'total_items' => is_array( $this->items ) ? count( $this->items ) : 0,
				'per_page'    => 100,
			]
		);
	}

	/**
	 * Handle column path.
	 *
	 * @param object $item The current item.
	 *
	 * @return string
	 */
	protected function column_path( $item ) {
		return ( empty( $item['isSitemapsIndex'] ) ? '' : '<span class="dashicons dashicons-category"></span>' ) . '<a href="' . esc_url( $item['path'] ) . '" target="_blank">' . esc_url( $item['path'] ) . '</a>';
	}

	/**
	 * Handle column lastDownloaded.
	 *
	 * @param object $item The current item.
	 *
	 * @return string
	 */
	protected function column_lastDownloaded( $item ) {
		if ( ! empty( $item['lastDownloaded'] ) ) {
			$date = date_parse( $item['lastDownloaded'] );
			$date = date_i18n( 'Y-m-d H:i:s', mktime( $date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year'] ) );
			return esc_html( $date );
		}
	}

	/**
	 * Handle column items.
	 *
	 * @param object $item The current item.
	 *
	 * @return string
	 */
	protected function column_items( $item ) {
		if ( empty( $item['contents'] ) || ! is_array( $item['contents'] ) ) {
			return;
		}

		$hash = [
			'web'   => [
				'icon'  => 'media-default',
				'title' => __( 'Pages', 'rank-math' ),
			],
			'image' => [
				'icon'  => 'format-image',
				'title' => __( 'Images', 'rank-math' ),
			],
			'news'  => [
				'icon'  => 'media-document',
				'title' => __( 'News', 'rank-math' ),
			],
		];

		$items = '';
		foreach ( $item['contents'] as $contents ) {

			$items .= ! isset( $hash[ $contents['type'] ] ) ? '<span class="rank-math-items-misc">' :
				sprintf(
					'<span title="%1$s"><span class="dashicons dashicons-%2$s"></span> ',
					esc_attr( $hash[ $contents['type'] ]['title'] ),
					esc_attr( $hash[ $contents['type'] ]['icon'] )
				);

			/* translators: content: submitted and indexed */
			$items .= sprintf( wp_kses_post( __( '%1$d <span class="indexed">(%2$d indexed)</span><br>', 'rank-math' ) ), absint( $contents['submitted'] ), absint( $contents['indexed'] ) );
			$items .= '</span>';
		}

		return $items;
	}

	/**
	 * Handles the default column output.
	 *
	 * @param object $item        The current item.
	 * @param string $column_name The current column name.
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		if ( 'warnings' === $column_name ) {
			return '<span title="' . esc_html__( 'Warnings', 'rank-math' ) . '">' . esc_html( $item['warnings'] ) . '</span>';
		}

		if ( 'errors' === $column_name ) {
			return '<span title="' . esc_html__( 'Errors', 'rank-math' ) . '">' . esc_html( $item['errors'] ) . '</span>';
		}

		return esc_html( print_r( $item, true ) );
	}

	/**
	 * Get a list of columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return [
			'path'           => esc_html__( 'Path', 'rank-math' ),
			'lastDownloaded' => esc_html__( 'Last Downloaded', 'rank-math' ),
			'items'          => esc_html__( 'Items', 'rank-math' ),
			'warnings'       => esc_html__( 'Warnings', 'rank-math' ) . ' <span class="dashicons dashicons-warning"></span>',
			'errors'         => esc_html__( 'Errors', 'rank-math' ) . ' <span class="dashicons dashicons-dismiss"></span>',
		];
	}

	/**
	 * Generates content for a single row of the table.
	 *
	 * @param object $item The current item.
	 */
	public function single_row( $item ) {
		$classes = [];

		$classes[] = ! empty( $item['isSitemapsIndex'] ) ? 'is-sitemap-index' : 'is-sitemap';

		if ( ! empty( $item['isPending'] ) ) {
			$classes[] = 'is-pending';
		}

		if ( ! empty( $item['errors'] ) ) {
			$classes[] = 'has-errors';
		}

		if ( ! empty( $item['warnings'] ) ) {
			$classes[] = 'has-warnings';
		}

		echo '<tr class="' . join( ' ', $classes ) . '">';
			$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Get refresh button
	 */
	public function get_refresh_button() {
		$url = Helper::get_admin_url(
			'search-console',
			[
				'view'             => 'sitemaps',
				'refresh_sitemaps' => '1',
				'security'         => wp_create_nonce( 'rank_math_refresh_sitemaps' ),
			]
		);
		?>
		<div class="alignleft actions">
			<a href="<?php echo esc_url( $url ); ?>" class="button button-secondary"><?php esc_html_e( 'Refresh Sitemaps', 'rank-math' ); ?></a>
		</div>
		<?php
	}
}
