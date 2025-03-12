<?php
/**
 * The admin post columns functionality.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\Helper;
use RankMath\Helpers\Str;
use RankMath\Helpers\Param;
use RankMath\Runner;
use RankMath\Traits\Hooker;
use RankMath\Admin\Database\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Post_Columns class.
 */
class Post_Columns implements Runner {

	use Hooker;

	/**
	 * SEO data.
	 *
	 * @var array
	 */
	private $data = [];

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->action( 'admin_init', 'init' );
	}

	/**
	 * Intialize.
	 */
	public function init() {
		if ( ! Helper::has_cap( 'onpage_general' ) ) {
			return;
		}

		$this->register_post_columns();
		$this->register_media_columns();

		// Column Content.
		$this->filter( 'rank_math_title', 'get_column_title', 5 );
		$this->filter( 'rank_math_description', 'get_column_description', 5 );
		$this->filter( 'rank_math_seo_details', 'get_column_seo_details', 5 );
	}

	/**
	 * Register post column hooks.
	 */
	private function register_post_columns() {
		$post_types = Helper::get_allowed_post_types();
		foreach ( $post_types as $post_type ) {
			$this->filter( 'edd_download_columns', 'add_columns', 11 );
			$this->filter( "manage_{$post_type}_posts_columns", 'add_columns', 11 );
			$this->action( "manage_{$post_type}_posts_custom_column", 'columns_contents', 11, 2 );
			$this->filter( "manage_edit-{$post_type}_sortable_columns", 'sortable_columns', 11 );

			// Also make them hidden by default.
			$user_id        = get_current_user_id();
			$columns_hidden = (array) get_user_meta( $user_id, "manageedit-{$post_type}columnshidden", true );
			$maybe_hidden   = get_user_meta( $user_id, "manageedit-{$post_type}columnshidden_default", true );

			// Continue if default is already set.
			if ( $maybe_hidden ) {
				continue;
			}

			// Set it to hidden by default.
			$columns_hidden = array_unique( array_merge( $columns_hidden, [ 'rank_math_title', 'rank_math_description' ] ) );
			update_user_meta( $user_id, "manageedit-{$post_type}columnshidden", $columns_hidden );
			update_user_meta( $user_id, "manageedit-{$post_type}columnshidden_default", '1' );
		}
	}

	/**
	 * Register media column hooks.
	 */
	private function register_media_columns() {
		if ( ! Helper::get_settings( 'titles.pt_attachment_bulk_editing' ) ) {
			return;
		}

		$this->filter( 'manage_media_columns', 'add_media_columns', 11 );
		$this->action( 'manage_media_custom_column', 'media_contents', 11, 2 );
	}

	/**
	 * Add new columns for SEO title, description and focus keywords.
	 *
	 * @param array $columns Array of column names.
	 *
	 * @return array
	 */
	public function add_columns( $columns ) {
		global $post_type;
		$current_pt = $post_type;
		if ( ! $post_type && 'inline-save' === Param::post( 'action' ) ) {
			$post_id    = Param::post( 'post_ID', 0, FILTER_VALIDATE_INT );
			$current_pt = get_post_type( $post_id );
		}
		$columns['rank_math_seo_details'] = esc_html__( 'SEO Details', 'rank-math' );

		if ( Helper::get_settings( 'titles.pt_' . $current_pt . '_bulk_editing', true ) ) {
			$columns['rank_math_title']       = esc_html__( 'SEO Title', 'rank-math' );
			$columns['rank_math_description'] = esc_html__( 'SEO Desc', 'rank-math' );
		}

		return $columns;
	}

	/**
	 * Make the SEO Score column sortable.
	 *
	 * @param array $columns Array of column names.
	 *
	 * @return array
	 */
	public function sortable_columns( $columns ) {
		$columns['rank_math_seo_details'] = 'rank_math_seo_score';

		return $columns;
	}

	/**
	 * Add new columns for Media Alt & Title.
	 *
	 * @param array $columns Array of column names.
	 *
	 * @return array
	 */
	public function add_media_columns( $columns ) {
		$columns['rank_math_image_title'] = esc_html__( 'Title', 'rank-math' );
		$columns['rank_math_image_alt']   = esc_html__( 'Alternative Text', 'rank-math' );

		return $columns;
	}

	/**
	 * Add content for custom column.
	 *
	 * @param string $column_name The name of the column to display.
	 * @param int    $post_id     The current post ID.
	 */
	public function columns_contents( $column_name, $post_id ) {
		if ( Str::starts_with( 'rank_math', $column_name ) ) {
			do_action( $column_name, $post_id );
		}
	}

	/**
	 * Add content for title column.
	 *
	 * @param int $post_id The current post ID.
	 */
	public function get_column_title( $post_id ) {
		$title = ! empty( $this->data[ $post_id ]['rank_math_title'] ) ? $this->data[ $post_id ]['rank_math_title'] : '';
		if ( ! $title ) {
			$post_type = get_post_type( $post_id );
			$title     = Helper::get_settings( "titles.pt_{$post_type}_title" );
		}
		?>
		<span class="rank-math-column-display"><?php echo esc_html( $title ); ?></span>
		<textarea class="rank-math-column-value" data-field="title" tabindex="11"><?php echo esc_attr( $title ); ?></textarea>
		<div class="rank-math-column-edit">
			<a href="#" class="rank-math-column-save"><?php esc_html_e( 'Save', 'rank-math' ); ?></a>
			<a href="#" class="button-link-delete rank-math-column-cancel"><?php esc_html_e( 'Cancel', 'rank-math' ); ?></a>
		</div>
		<?php
	}

	/**
	 * Add content for title column.
	 *
	 * @param int $post_id The current post ID.
	 */
	public function get_column_description( $post_id ) {
		$description = ! empty( $this->data[ $post_id ]['rank_math_description'] ) ? $this->data[ $post_id ]['rank_math_description'] : '';
		if ( ! $description ) {
			$post_type   = get_post_type( $post_id );
			$description = has_excerpt( $post_id ) ? '%excerpt%' : Helper::get_settings( "titles.pt_{$post_type}_description" );
		}
		?>
		<span class="rank-math-column-display"><?php echo esc_html( $description ); ?></span>
		<textarea class="rank-math-column-value" data-field="description" tabindex="11"><?php echo esc_attr( $description ); ?></textarea>
		<div class="rank-math-column-edit">
			<a href="#" class="rank-math-column-save"><?php esc_html_e( 'Save', 'rank-math' ); ?></a>
			<a href="#" class="button-link-delete rank-math-column-cancel"><?php esc_html_e( 'Cancel', 'rank-math' ); ?></a>
		</div>
		<?php
	}

	/**
	 * Add content for title column.
	 *
	 * @param int $post_id The current post ID.
	 */
	public function get_column_seo_details( $post_id ) {
		if ( empty( $this->data ) ) {
			$this->get_seo_data();
		}

		$data = isset( $this->data[ $post_id ] ) ? $this->data[ $post_id ] : [];
		if ( ! self::is_post_indexable( $post_id ) ) {
			echo '<span class="rank-math-column-display seo-score no-score "><strong>N/A</strong></span>';
			echo '<strong>' . esc_html__( 'No Index', 'rank-math' ) . '</strong>';
			$this->do_action( 'post/column/seo_details', $post_id, $data, $this->data );
			return;
		}

		$keyword   = ! empty( $data['rank_math_focus_keyword'] ) ? $data['rank_math_focus_keyword'] : '';
		$keyword   = explode( ',', $keyword )[0];
		$is_pillar = ! empty( $data['rank_math_pillar_content'] ) && 'on' === $data['rank_math_pillar_content'] ? true : false;

		$score = empty( $keyword ) ? false : $this->get_seo_score( $data );
		$class = ! $score ? 'no-score' : $this->get_seo_score_class( $score );
		$score = $score ? $score . ' / 100' : 'N/A';

		?>
		<span class="rank-math-column-display seo-score <?php echo esc_attr( $class ); ?> <?php echo ! $score ? 'disabled' : ''; ?>">
			<strong><?php echo esc_html( $score ); ?></strong>
			<?php if ( $is_pillar ) { ?>
				<img class="is-pillar" src="<?php echo esc_url( rank_math()->plugin_url() . 'assets/admin/img/pillar.svg' ); ?>" alt="<?php esc_html_e( 'Is Pillar', 'rank-math' ); ?>" title="<?php esc_html_e( 'Is Pillar', 'rank-math' ); ?>" width="25" />
			<?php } ?>
		</span>

			<label><?php esc_html_e( 'Focus Keyword', 'rank-math' ); ?>:</label>
			<span class="rank-math-column-display">
				<strong title="Focus Keyword"><?php esc_html_e( 'Keyword', 'rank-math' ); ?>:</strong>
				<span>
					<?php
						echo $keyword ? wp_kses_post( $this->do_filter( 'post/column/seo_details/focus_keyword', $keyword ) ) : esc_html__( 'Not Set', 'rank-math' );
					?>
				</span>
			</span>

			<input class="rank-math-column-value" data-field="focus_keyword" tabindex="11" value="<?php echo esc_attr( $keyword ); ?>" />

			<?php $this->do_action( 'post/column/seo_details', $post_id, $data, $this->data ); ?>

			<div class="rank-math-column-edit">
				<a href="#" class="rank-math-column-save"><?php esc_html_e( 'Save', 'rank-math' ); ?></a>
				<a href="#" class="button-link-delete rank-math-column-cancel"><?php esc_html_e( 'Cancel', 'rank-math' ); ?></a>
			</div>

		<?php
	}

	/**
	 * Add content for custom media column.
	 *
	 * @param string $column_name The name of the column to display.
	 * @param int    $post_id     The current post ID.
	 */
	public function media_contents( $column_name, $post_id ) {
		if ( 'rank_math_image_title' === $column_name ) {
			$title = get_the_title( $post_id );
			?>
			<span class="rank-math-column-display"><?php echo esc_html( $title ); ?></span>
			<input class="rank-math-column-value" data-field="image_title" tabindex="11" value="<?php echo esc_attr( $title ); ?>" />
			<div class="rank-math-column-edit">
				<a href="#" class="rank-math-column-save"><?php esc_html_e( 'Save', 'rank-math' ); ?></a>
				<a href="#" class="button-link-delete rank-math-column-cancel"><?php esc_html_e( 'Cancel', 'rank-math' ); ?></a>
			</div>
			<?php
			return;
		}

		if ( 'rank_math_image_alt' === $column_name ) {
			$alt = get_post_meta( $post_id, '_wp_attachment_image_alt', true );
			?>
			<span class="rank-math-column-display"><?php echo esc_html( $alt ); ?></span>
			<input class="rank-math-column-value" data-field="image_alt" tabindex="11" value="<?php echo esc_attr( $alt ); ?>" />
			<div class="rank-math-column-edit">
				<a href="#" class="rank-math-column-save"><?php esc_html_e( 'Save', 'rank-math' ); ?></a>
				<a href="#" class="button-link-delete rank-math-column-cancel"><?php esc_html_e( 'Cancel', 'rank-math' ); ?></a>
			</div>
			<?php
			return;
		}
	}

	/**
	 * Get SEO data.
	 */
	private function get_seo_data() {
		$post_ids = [];

		$post_ids = array_filter( $this->get_post_ids() );
		$post_id  = (int) Param::post( 'post_ID' );
		if ( $post_id ) {
			$post_ids[] = $post_id;
		}

		if ( empty( $post_ids ) ) {
			return false;
		}

		$results = Database::table( 'postmeta' )->select( [ 'post_id', 'meta_key', 'meta_value' ] )->whereIn( 'post_id', $post_ids )->whereLike( 'meta_key', 'rank_math' )->get( ARRAY_A );
		if ( empty( $results ) ) {
			return false;
		}

		foreach ( $results as $result ) {
			$this->data[ $result['post_id'] ][ $result['meta_key'] ] = $result['meta_value'];
		}
	}

	/**
	 * Get Post IDs dispalyed on the Post lists page.
	 */
	private function get_post_ids() {
		global $wp_query, $per_page;
		if ( empty( $wp_query->posts ) ) {
			return [];
		}

		$pages = $wp_query->posts;
		if (
			! is_post_type_hierarchical( Param::get( 'post_type' ) ) ||
			'menu_order title' !== $wp_query->query['orderby']
		) {
			return array_map(
				function ( $post ) {
					return isset( $post->ID ) ? $post->ID : '';
				},
				$pages
			);
		}

		$children_pages = [];
		if ( empty( Param::request( 's' ) ) ) {
			$top_level_pages = [];

			foreach ( $pages as $page ) {
				if ( $page->post_parent > 0 ) {
					$children_pages[ $page->post_parent ][] = $page;
				} else {
					$top_level_pages[] = $page;
				}
			}

			$pages = &$top_level_pages;
		}

		$pagenum = max( 1, Param::request( 'paged', 0 ) );
		$count   = 0;
		$start   = ( $pagenum - 1 ) * $per_page;
		$end     = $start + $per_page;
		$ids     = [];

		foreach ( $pages as $page ) {
			if ( $count >= $end ) {
				break;
			}

			if ( $count >= $start ) {
				$ids[] = $page->ID;
			}

			++$count;

			$this->add_child_page_ids( $children_pages, $page->ID, $ids, $count );
		}

		return $ids;
	}

	/**
	 * Add the child page IDs to the list of IDs to be processed.
	 *
	 * @param array $children_pages Child Pages.
	 * @param int   $id             Current page ID.
	 * @param array $ids            IDs to be processed.
	 * @param int   $count          Counter.
	 */
	private function add_child_page_ids( $children_pages, $id, &$ids, &$count ) {
		if ( empty( $children_pages ) || empty( $children_pages[ $id ] ) ) {
			return;
		}

		foreach ( $children_pages[ $id ] as $child_page ) {
			$id    = $child_page->ID;
			$ids[] = $child_page->ID;
			++$count;

			$this->add_child_page_ids( $children_pages, $id, $ids, $count );
		}
	}

	/**
	 * Get SEO score.
	 *
	 * @param array $data SEO data of current post.
	 *
	 * @return string
	 */
	private function get_seo_score( $data ) {
		if ( ! isset( $data['rank_math_seo_score'] ) ) {
			return false;
		}

		if ( ! Helper::is_score_enabled() ) {
			return false;
		}

		return $data['rank_math_seo_score'] ? $data['rank_math_seo_score'] : 0;
	}

	/**
	 * Get SEO score rating string: great/good/bad.
	 *
	 * @param int $score Score.
	 *
	 * @return string
	 */
	private function get_seo_score_class( $score ) {
		if ( $score > 80 ) {
			return 'great';
		}

		if ( $score > 50 && $score < 81 ) {
			return 'good';
		}

		return 'bad';
	}

	/**
	 * Check post indexable status.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function is_post_indexable( $post_id ) {
		$robots = Param::post( 'rank_math_robots', false, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		$robots = apply_filters( 'rank_math/admin/robots', $robots, $post_id );
		if ( ! empty( $robots ) ) {
			return in_array( 'index', $robots, true ) ? true : false;
		}

		return Helper::is_post_indexable( $post_id );
	}
}
