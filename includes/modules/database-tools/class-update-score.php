<?php
/**
 * The tool to update SEO score on existing posts.
 *
 * @since      1.0.97
 * @package    RankMath
 * @subpackage RankMath\Status
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tools;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Paper\Paper;
use RankMath\Admin\Metabox\Screen;
use RankMath\Schema\DB;
use MyThemeShop\Helpers\Param;
use MyThemeShop\Helpers\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * Update_Score class.
 */
class Update_Score {

	use Hooker;

	/**
	 * Batch size.
	 *
	 * @var int
	 */
	private $batch_size;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->batch_size = absint( apply_filters( 'rank_math/recalculate_scores_batch_size', 25 ) );

		$this->action( 'admin_footer', 'footer_modal' );
		$this->filter( 'rank_math/tools/update_seo_score', 'update_seo_score' );

		$this->screen = new Screen();
		$this->screen->load_screen( 'post' );
		$this->action( 'admin_enqueue_scripts', 'enqueue' );

		$this->action( 'admin_init', 'run_in_new_window' );
	}

	/**
	 * Enqueue scripts & add JSON data needed to update the SEO score on existing posts.
	 */
	public function enqueue() {
		$screen = get_current_screen();
		if ( Param::get('page') !== 'rank-math-status' || Param::get( 'view' ) !== 'tools' ) {
			return;
		}

		$scripts = [
			'lodash'             => '',
			'wp-data'            => '',
			'wp-core-data'       => '',
			'wp-compose'         => '',
			'wp-components'      => '',
			'wp-element'         => '',
			'wp-block-editor'    => '',
			'rank-math-analyzer' => rank_math()->plugin_url() . 'assets/admin/js/analyzer.js',
		];

		foreach ( $scripts as $handle => $src ) {
			wp_enqueue_script( $handle, $src, [], rank_math()->version, true );
		}

		global $post;
		$temp_post = $post;
		if ( is_null( $post ) ) {
			$posts = get_posts(
				[
					'fields'         => 'id',
					'posts_per_page' => 1,
					'post_type'      => $this->get_post_types(),
				]
			);
			$post  = isset( $posts[0] ) ? $posts[0] : null;
		}

		$this->screen->localize();
		$post = $temp_post;

		Helper::add_json( 'totalPosts', $this->find() );
		Helper::add_json( 'batchSize', $this->batch_size );
	}

	/**
	 * Function to Update the SEO score.
	 */
	public function update_seo_score() {
		$args   = Param::post( 'args', [], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$offset = isset( $args['offset'] ) ? absint( $args['offset'] ) : 0;
		$posts  = get_posts(
			[
				'post_type'      => $this->get_post_types(),
				'posts_per_page' => $this->batch_size,
				'offset'         => $offset,
				'orderby'        => 'ID',
				'order'          => 'ASC',

				'meta_query'     => [
					[
						'key'     => 'rank_math_focus_keyword',
						'value'   => '',
						'compare' => '!=',
					],
				],
			]
		);

		if ( empty( $posts ) ) {
			return esc_html__( 'All data updated', 'rank-math' );
		}

		add_filter(
			'rank_math/replacements/non_cacheable',
			function( $non_cacheable ) {
				$non_cacheable[] = 'excerpt';
				$non_cacheable[] = 'excerpt_only';
				$non_cacheable[] = 'seo_description';
				return $non_cacheable;
			}
		);

		rank_math()->variables->setup();
		$data = [];

		global $wpdb;
		foreach ( $posts as $post ) {
			$post_id   = $post->ID;
			$post_type = $post->post_type;
			$title     = get_post_meta( $post_id, 'rank_math_title', true );
			$title     = $title ? $title : Paper::get_from_options( "pt_{$post_type}_title", $post, '%title% %sep% %sitename%' );
			$keywords  = array_map( 'trim', explode( ',', Helper::get_post_meta( 'focus_keyword', $post_id ) ) );
			$keyword   = $keywords[0];

			$values    = [
				'title'        => Helper::replace_vars( '%seo_title%', $post ),
				'description'  => Helper::replace_vars( '%seo_description%', $post ),
				'keywords'     => $keywords,
				'keyword'      => $keyword,
				'content'      => $post->post_content,
				'url'          => get_the_permalink( $post_id ),
				'thumbnail'    => get_the_post_thumbnail_url( $post_id ),
				'hasContentAi' => ! empty( Helper::get_post_meta( 'contentai_score', $post_id ) ),
			];

			if (
				( Conditional::is_woocommerce_active() && 'product' === $post_type ) ||
				( Conditional::is_edd_active() && 'download' === $post_type )
			) {
				$values['isProduct']       = true;
				$values['isReviewEnabled'] = 'yes' === get_option( 'woocommerce_enable_reviews', 'yes' );

				$schemas = DB::get_schemas( $post_id );
				if ( empty( $schemas ) && Helper::get_default_schema_type( $post_id ) ) {
					$schemas = [
						[ '@type' => Helper::get_default_schema_type( $post_id ) ],
					];
				}

				$schemas = array_filter(
					$schemas,
					function( $schema ) {
						return in_array( $schema['@type'], [ 'WooCommerceProduct', 'EDDProduct', 'Product' ], true );
					}
				);

				$values['hasProductSchema'] = ! empty( $schemas );
			}

			/**
			 * Filter the values sent to the analyzer to calculate the SEO score.
			 *
			 * @param array $values The values to be sent to the analyzer.
			 */
			$data[ $post_id ] = $this->do_filter( 'recalculate_score/data', $values, $post_id );
		}

		return $data;
	}

	/**
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Update_Score
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof Update_Score ) ) {
			$instance = new Update_Score();
		}

		return $instance;
	}

	/**
	 * Find posts with focus keyword.
	 *
	 * @return int
	 */
	public function find() {
		static $update_score_post_ids;
		if ( null !== $update_score_post_ids ) {
			return $update_score_post_ids;
		}

		global $wpdb;
		$post_types  = $this->get_post_types();
		$placeholder = implode( ', ', array_fill( 0, count( $post_types ), '%s' ) );
		$query       = "SELECT COUNT(ID) FROM {$wpdb->posts} as p
		LEFT JOIN {$wpdb->postmeta} ON ( p.ID = {$wpdb->postmeta}.post_id )
		WHERE (
			{$wpdb->postmeta}.meta_key = 'rank_math_focus_keyword' AND {$wpdb->postmeta}.meta_value != ''
		)
		AND p.post_type IN ($placeholder)
		AND p.post_status = 'publish'
		";

		$update_score_post_ids = $wpdb->get_var( $wpdb->prepare( $query, $post_types ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- It's prepared above.

		return (int) $update_score_post_ids;
	}

	/**
	 * Modal to show the Update SEO Score progress.
	 *
	 * @return array
	 */
	public function footer_modal() {
		if ( Param::get( 'page' ) !== 'rank-math-status' || Param::get( 'view' ) !== 'tools' ) {
			return;
		}
		?>
		<div class="rank-math-modal rank-math-modal-update-score">
			<div class="rank-math-modal-content">
				<div class="rank-math-modal-header">
					<h3><?php esc_html_e( 'Recalculating SEO Scores', 'rank-math' ); ?></h3>
					<p><?php esc_html_e( 'This process may take a while. Please keep this window open until the process is complete.', 'rank-math' ); ?></p>
				</div>
				<div class="rank-math-modal-body">
					<div class="count">
						<?php esc_html_e( 'Calculated:', 'rank-math' ); ?> <span>0</span> / <?php echo esc_html( $this->find() ); ?>
					</div>
					<div class="progress-bar">
						<span></span>
					</div>

					<div class="rank-math-modal-footer hidden">
						<p>
							<?php esc_html_e( 'The SEO Scores have been recalculated successfully!', 'rank-math' ); ?>
						</p>
						<button class="button button-large rank-math-modal-close"><?php esc_html_e( 'Close', 'rank-math' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get post types.
	 *
	 * @return array
	 */
	private function get_post_types() {
		$post_types = get_post_types( [ 'public' => true ] );
		if ( isset( $post_types['attachment'] ) ) {
			unset( $post_types['attachment'] );
		}

		return $this->do_filter( 'tool/post_types', array_keys( $post_types ) );
	}

	/**
	 * Run the tool in a new window, so that the user can continue to use the site.
	 */
	public function run_in_new_window() {
		// Check if we're on the right page.
		if ( Param::get( 'page' ) !== 'rank-math-status' || Param::get( 'view' ) !== 'tools' || Param::get( 'update_scores' ) !== '1' ) {
			return;
		}

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'rank-math' ) );
		}

		// Check nonce.
		check_admin_referer( 'rank-math-recalculate-scores' );

		Helper::add_json( 'startUpdateScore', true );
		$this->filter( 'admin_body_class', 'add_body_class' );
	}

	/**
	 * Add body class.
	 *
	 * @param  string $classes Body classes.
	 * @return string
	 */
	public function add_body_class( $classes ) {
		return $classes . ' rank-math-start-update-score';
	}
}
