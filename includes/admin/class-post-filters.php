<?php
/**
 * The admin post filters functionality.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use WP_Meta_Query;
use RankMath\Helper;
use RankMath\Runner;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Security;
use RankMath\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Post_Filters class.
 */
class Post_Filters implements Runner {

	use Hooker;

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
		if ( ! Helper::has_cap( 'general' ) ) {
			return;
		}

		$this->filter( 'pre_get_posts', 'posts_by_seo_filters' );
		$this->filter( 'parse_query', 'posts_by_focus_keywords' );
		$this->filter( 'restrict_manage_posts', 'add_seo_filters', 11 );

		foreach ( Helper::get_allowed_post_types() as $post_type ) {
			$this->filter( "views_edit-$post_type", 'add_pillar_content_filter_link' );
		}
	}

	/**
	 * Filter posts in admin by Rank Math's Filter value.
	 *
	 * @param \WP_Query $query The wp_query instance.
	 */
	public function posts_by_seo_filters( $query ) {
		if ( ! $this->can_seo_filters() ) {
			return;
		}

		if ( 'rank_math_seo_score' === $query->get( 'orderby' ) ) {
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_key', 'rank_math_seo_score' );
			$query->set( 'meta_type', 'numeric' );
		}

		if ( empty( $_GET['pillar_content'] ) && empty( $_GET['seo-filter'] ) ) {
			return;
		}

		$meta_query = [];

		// Check for Pillar Content filter.
		if ( ! empty( $_GET['pillar_content'] ) ) {
			$meta_query[] = [
				'key'   => 'rank_math_pillar_content',
				'value' => 'on',
			];
		}

		$this->set_seo_filters( $meta_query );
		$query->set( 'meta_query', $meta_query );
	}

	/**
	 * Filter post in admin by Pillar Content.
	 *
	 * @param \WP_Query $query The wp_query instance.
	 */
	public function posts_by_focus_keywords( $query ) {
		if ( ! $this->can_fk_filter() ) {
			return;
		}

		if ( $ids = $this->posts_had_reviews() ) { // phpcs:ignore
			$query->set( 'post_type', 'any' );
			$query->set( 'post__in', $ids );
			return;
		}

		$query->set( 'post_status', 'publish' );
		if ( $ids = $this->has_fk_in_title() ) { // phpcs:ignore
			$query->set( 'post__in', $ids );
			return;
		}

		$focus_keyword = Param::get( 'focus_keyword', '', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_BACKTICK );
		if ( 1 === absint( $focus_keyword ) ) {
			$query->set(
				'meta_query',
				[
					'relation' => 'AND',
					[
						'key'     => 'rank_math_focus_keyword',
						'compare' => 'NOT EXISTS',
					],
					[
						'relation' => 'OR',
						[
							'key'     => 'rank_math_robots',
							'value'   => 'noindex',
							'compare' => 'NOT LIKE',
						],
						[
							'key'     => 'rank_math_robots',
							'compare' => 'NOT EXISTS',
						],
					],
				]
			);
			return;
		}

		$query->set( 'post_type', 'any' );
		$query->set(
			'meta_query',
			[
				[
					'relation' => 'OR',
					[
						'key'     => 'rank_math_focus_keyword',
						'value'   => $focus_keyword . ',',
						'compare' => 'LIKE',
					],
					[
						'key'     => 'rank_math_focus_keyword',
						'value'   => $focus_keyword,
						'compare' => 'LIKE',
					],
				],
			]
		);
	}

	/**
	 * Add SEO filters.
	 */
	public function add_seo_filters() {
		global $post_type;

		if ( 'attachment' === $post_type || ! in_array( $post_type, Helper::get_allowed_post_types(), true ) ) {
			return;
		}

		$options = [
			''          => esc_html__( 'Rank Math', 'rank-math' ),
			'great-seo' => esc_html__( 'SEO Score: Good', 'rank-math' ),
			'good-seo'  => esc_html__( 'SEO Score: Ok', 'rank-math' ),
			'bad-seo'   => esc_html__( 'SEO Score: Bad', 'rank-math' ),
			'empty-fk'  => esc_html__( 'Focus Keyword Not Set', 'rank-math' ),
			'noindexed' => esc_html__( 'Articles noindexed', 'rank-math' ),
		];

		$options  = $this->do_filter( 'manage_posts/seo_filter_options', $options, $post_type );
		$selected = Param::get( 'seo-filter' );
		?>
		<select name="seo-filter" id="rank-math-seo-filter">
			<?php foreach ( $options as $val => $option ) : ?>
				<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $selected, $val, true ); ?>><?php echo esc_html( $option ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Add view to filter list for Pillar Content.
	 *
	 * @param array $views An array of available list table views.
	 */
	public function add_pillar_content_filter_link( $views ) {
		global $typenow;

		$current = empty( $_GET['pillar_content'] ) ? '' : ' class="current" aria-current="page"';
		$pillars = get_posts(
			[
				'post_type'      => $typenow,
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'meta_key'       => 'rank_math_pillar_content',
				'meta_value'     => 'on',
			]
		);

		$views['pillar_content'] = sprintf(
			'<a href="%1$s"%2$s>%3$s <span class="count">(%4$s)</span></a>',
			Security::add_query_arg(
				[
					'post_type'      => $typenow,
					'pillar_content' => 1,
				]
			),
			$current,
			esc_html__( 'Pillar Content', 'rank-math' ),
			number_format_i18n( count( $pillars ) )
		);

		return $views;
	}

	/**
	 * Can apply SEO filters.
	 *
	 * @return bool
	 */
	private function can_seo_filters() {
		$screen = get_current_screen();
		if ( is_null( $screen ) || ! in_array( $screen->post_type, Helper::get_allowed_post_types(), true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Set SEO filters meta query.
	 *
	 * @param array $query Meta query.
	 */
	private function set_seo_filters( &$query ) {
		$filter = Param::get( 'seo-filter' );
		if ( false === $filter ) {
			return;
		}

		$hash = [
			'empty-fk'  => [
				'key'     => 'rank_math_focus_keyword',
				'compare' => 'NOT EXISTS',
			],
			'bad-seo'   => [
				'key'     => 'rank_math_seo_score',
				'value'   => 50,
				'compare' => '<=',
				'type'    => 'numeric',
			],
			'good-seo'  => [
				'key'     => 'rank_math_seo_score',
				'value'   => [ 51, 80 ],
				'compare' => 'BETWEEN',
				'type'    => 'numeric',
			],
			'great-seo' => [
				'key'     => 'rank_math_seo_score',
				'value'   => 80,
				'compare' => '>',
				'type'    => 'numeric',
			],
			'noindexed' => [
				'key'     => 'rank_math_robots',
				'value'   => 'noindex',
				'compare' => 'LIKE',
			],
		];

		// Extra conditions for "SEO Score" filters.
		$seo_score_filters = [ 'bad-seo', 'good-seo', 'great-seo' ];
		if ( in_array( $filter, $seo_score_filters, true ) ) {
			$query['relation'] = 'AND';
			$query[]           = [
				'relation' => 'OR',
				[
					'key'     => 'rank_math_robots',
					'value'   => 'noindex',
					'compare' => 'NOT LIKE',
				],
				[
					'key'     => 'rank_math_robots',
					'compare' => 'NOT EXISTS',
				],
			];
			$query[]           = [
				'key'     => 'rank_math_focus_keyword',
				'compare' => 'EXISTS',
			];
			$query[]           = [
				'key'     => 'rank_math_focus_keyword',
				'value'   => '',
				'compare' => '!=',
			];
		}

		if ( isset( $hash[ $filter ] ) ) {
			$query[] = $hash[ $filter ];
		}
	}

	/**
	 * Can apply Focus Keyword filter.
	 *
	 * @return bool
	 */
	private function can_fk_filter() {
		$screen = get_current_screen();
		if (
			is_null( $screen ) ||
			'edit' !== $screen->base ||
			(
				! isset( $_GET['focus_keyword'] ) &&
				! isset( $_GET['fk_in_title'] ) &&
				! isset( $_GET['review_posts'] )
			)
		) {
			return false;
		}

		return true;
	}

	/**
	 * Check if Focus Keyword appears in the title.
	 *
	 * @return bool|array
	 */
	private function has_fk_in_title() {
		global $wpdb;

		if ( ! Param::get( 'fk_in_title' ) ) {
			return false;
		}

		$screen     = get_current_screen();
		$meta_query = new WP_Meta_Query(
			[
				[
					'key'     => 'rank_math_focus_keyword',
					'compare' => 'EXISTS',
				],
				[
					'relation' => 'OR',
					[
						'key'     => 'rank_math_robots',
						'value'   => 'noindex',
						'compare' => 'NOT LIKE',
					],
					[
						'key'     => 'rank_math_robots',
						'compare' => 'NOT EXISTS',
					],
				],
			]
		);

		$meta_query = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		return $wpdb->get_col( "SELECT {$wpdb->posts}.ID FROM $wpdb->posts {$meta_query['join']} WHERE 1=1 {$meta_query['where']} AND {$wpdb->posts}.post_type = '$screen->post_type' AND ({$wpdb->posts}.post_status = 'publish') AND {$wpdb->posts}.post_title NOT LIKE CONCAT( '%', SUBSTRING_INDEX( {$wpdb->postmeta}.meta_value, ',', 1 ), '%' )" ); // phpcs:ignore
	}

	/**
	 * Check if any posts had Review schema.
	 *
	 * @return bool|array
	 */
	private function posts_had_reviews() {
		global $wpdb;

		$review_posts = Param::get( 'review_posts' );
		if ( ! $review_posts ) {
			return false;
		}

		return ! get_option( 'rank_math_review_posts_converted', false );
	}
}
