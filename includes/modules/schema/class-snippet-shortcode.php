<?php
/**
 * The Schema Shortcode
 *
 * @since      1.0.24
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Traits\Shortcode;
use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Snippet_Shortcode class.
 */
class Snippet_Shortcode {

	use Hooker, Shortcode;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->add_shortcode( 'rank_math_rich_snippet', 'rich_snippet' );
		$this->add_shortcode( 'rank_math_review_snippet', 'rich_snippet' );

		if ( ! is_admin() ) {
			$this->filter( 'the_content', 'add_review_to_content', 11 );
		}

		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			'rank-math/rich-snippet',
			[
				'render_callback' => [ $this, 'rich_snippet' ],
				'attributes'      => [
					'id' => [
						'default' => '',
						'type'    => 'integer',
					],
				],
			]
		);
	}

	/**
	 * Schema shortcode.
	 *
	 * @param  array $atts Optional. Shortcode arguments - currently only 'show'
	 *                     parameter, which is a comma-separated list of elements to show.
	 *
	 * @return string Shortcode output.
	 */
	public function rich_snippet( $atts ) {
		$atts = shortcode_atts(
			[ 'id' => false ],
			$atts,
			'rank_math_rich_snippet'
		);

		if ( 'edit' === Param::get( 'context' ) ) {
			rank_math()->variables->setup();
		}

		$data = $this->get_data( $atts['id'] );
		if ( empty( $data ) ) {
			return esc_html__( 'No schema found.', 'rank-math' );
		}

		$post   = get_post( $data['post_id'] );
		$schema = $this->replace_variables( $data['schema'], $post );
		$schema = $this->do_filter( 'schema/shortcode/filter_attributes', $schema, $atts );

		return $this->do_filter( 'snippet/html', $this->get_snippet_content( $schema, $post ), $schema, $post, $this );
	}

	/**
	 * Get schema data.
	 *
	 * @param  string $id Shortcode id.
	 * @return array
	 */
	private function get_data( $id ) {
		if ( ! empty( $id ) && is_string( $id ) ) {
			return DB::get_schema_by_shortcode_id( $id );
		}

		$post_id = Param::get( 'post_id' ) ? Param::get( 'post_id' ) : get_the_ID();
		$data    = DB::get_schemas( $post_id );
		return empty( $data ) ? false : [
			'post_id' => $post_id,
			'schema'  => current( $data ),
		];
	}

	/**
	 * Replace variable.
	 *
	 * @param  array   $schemas Schema to replace.
	 * @param  WP_Post $post    Post schema attached to.
	 * @return array
	 */
	private function replace_variables( $schemas, $post ) {
		$new_schemas = [];

		foreach ( $schemas as $key => $schema ) {
			if ( 'metadata' === $key ) {
				continue;
			}

			if ( is_array( $schema ) ) {
				$new_schemas[ $key ] = $this->replace_variables( $schema, $post );
				continue;
			}

			$new_schemas[ $key ] = Str::contains( '%', $schema ) ? Helper::replace_vars( $schema, $post ) : $schema;
		}

		return $new_schemas;
	}

	/**
	 * Get Snippet content.
	 *
	 * @param  array   $schema Schema to replace.
	 * @param  WP_Post $post   Post schema attached to.
	 *
	 * @return string Shortcode output.
	 */
	public function get_snippet_content( $schema, $post ) {
		wp_enqueue_style( 'rank-math-review-snippet', rank_math()->assets() . 'css/rank-math-snippet.css', null, rank_math()->version );

		$type         = \strtolower( $schema['@type'] );
		$this->post   = $post;
		$this->schema = $schema;

		if ( in_array( $type, [ 'article' ], true ) ) {
			return;
		}

		if ( Str::ends_with( 'event', $type ) ) {
			$type = 'event';
		}

		ob_start();
		?>
			<div id="rank-math-rich-snippet-wrapper">

				<?php
				$file = rank_math()->plugin_dir() . "includes/modules/schema/shortcode/$type.php";
				if ( file_exists( $file ) ) {
					include $file;
				}
				?>

			</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Get field value.
	 *
	 * @param  string $field_id Field id.
	 * @param  mixed  $default  Default value.
	 * @return mixed
	 */
	public function get_field_value( $field_id, $default = null ) {
		$array = $this->schema;
		if ( isset( $array[ $field_id ] ) ) {
			return $array[ $field_id ];
		}

		foreach ( explode( '.', $field_id ) as $segment ) {
			if ( ! is_array( $array ) || ! array_key_exists( $segment, $array ) ) {
				return $default;
			}

			$array = $array[ $segment ];
		}

		return $array;
	}

	/**
	 * Get field.
	 *
	 * @param  string $title        Field title.
	 * @param  string $field_id     Field id to get value.
	 * @param  string $convert_date Convert date value to proper format.
	 * @param  mixed  $default      Default value.
	 */
	public function get_field( $title, $field_id, $convert_date = false, $default = null ) {
		$value = $this->get_field_value( $field_id, $default );
		if ( empty( $value ) ) {
			return;
		}

		if ( $convert_date ) {
			$value = Helper::convert_date( $value );
		}

		$this->output_field( $title, $value );
	}

	/**
	 * Get field.
	 *
	 * @param string $title Field title.
	 * @param mixed  $value Field value.
	 */
	public function output_field( $title, $value ) {
		?>
		<p>
			<strong><?php echo $title; // phpcs:ignore ?>: </strong>
			<?php echo is_array( $value ) ? implode( ', ', $value ) : wp_kses_post( $value ); // phpcs:ignore ?>
		</p>
		<?php
	}

	/**
	 * Get title.
	 */
	public function get_title() {
		if ( ! isset( $this->schema['name'] ) && ! isset( $this->schema['title'] ) ) {
			return;
		}

		$title = isset( $this->schema['title'] ) ? $this->schema['title'] : $this->schema['name'];
		$title = $title && '' !== $title ? $title : Helper::replace_vars( '%title%', $this->post );
		?>
		<h5 class="rank-math-title"><?php echo $title; // phpcs:ignore ?></h5>
		<?php
	}

	/**
	 * Get description.
	 *
	 * @param  string $description Schema description field.
	 */
	public function get_description( $description = 'description' ) {
		$excerpt = Helper::replace_vars( '%excerpt%', $this->post );
		if ( $description && '' !== $description ) {
			$description = $this->get_field_value( $description );
		}
		$description = $description && '' !== $description ? $description : ( $excerpt ? $excerpt : Helper::get_post_meta( 'description', $this->post->ID ) );
		?>
		<p><?php echo do_shortcode( $description ); ?></p>
		<?php
	}

	/**
	 * Get image.
	 */
	public function get_image() {
		if ( ! isset( $this->schema['image'] ) ) {
			return;
		}

		$image = Helper::get_thumbnail_with_fallback( $this->post->ID, 'medium' );
		if ( empty( $image ) ) {
			return;
		}
		?>
		<div class="rank-math-review-image">
			<img src="<?php echo esc_url( $image[0] ); ?>" />
		</div>
		<?php
	}

	/**
	 * Display nicely formatted reviews.
	 *
	 * @param  string $field_id     Field id to get value.
	 */
	public function show_ratings( $field_id = 'review.reviewRating.ratingValue' ) {
		$rating = (float) $this->get_field_value( $field_id );
		if ( empty( $rating ) ) {
			return;
		}
		?>
		<div class="rank-math-total-wrapper">

			<strong><?php echo $this->do_filter( 'review/text', esc_html__( 'Editor\'s Rating:', 'rank-math' ) ); // phpcs:ignore ?></strong><br />

			<span class="rank-math-total"><?php echo $rating; // phpcs:ignore ?></span>

			<div class="rank-math-review-star">

				<div class="rank-math-review-result-wrapper">

					<?php echo \str_repeat( '<i class="rank-math-star"></i>', 5 ); // phpcs:ignore ?>

					<div class="rank-math-review-result" style="width:<?php echo ( $rating * 20 ); // phpcs:ignore ?>%;">
						<?php echo \str_repeat( '<i class="rank-math-star"></i>', 5 ); // phpcs:ignore ?>
					</div>

				</div>

			</div>

		</div>
		<?php
	}

	/**
	 * Injects reviews to content.
	 *
	 * @param  string $content Post content.
	 * @return string
	 *
	 * @since 1.0.12
	 */
	public function add_review_to_content( $content ) {
		$location = $this->get_content_location();
		if ( false === $location ) {
			return $content;
		}

		$review = do_shortcode( '[rank_math_review_snippet]' );

		if ( in_array( $location, [ 'top', 'both' ], true ) ) {
			$content = $review . $content;
		}

		if ( in_array( $location, [ 'bottom', 'both' ], true ) && $this->can_add_multi_page() ) {
			$content .= $review;
		}

		return $content;
	}

	/**
	 * Check if we can inject the review in the content.
	 *
	 * @return boolean|string
	 */
	private function get_content_location() {
		/**
		 * Filter: Allow disabling the review display.
		 *
		 * @param bool $return True to disable.
		 */
		if ( ! is_main_query() || ! in_the_loop() || $this->do_filter( 'snippet/review/hide_data', false ) ) {
			return false;
		}

		$data = $this->get_data( false );
		if ( empty( $data ) ) {
			return false;
		}

		$schema = $data['schema'];
		$type   = \strtolower( $schema['@type'] );
		if ( ! in_array( $type, [ 'book', 'review', 'course', 'event', 'product', 'recipe', 'softwareapplication' ], true ) ) {
			return false;
		}

		$location = isset( $schema['metadata']['reviewLocation'] ) ? $schema['metadata']['reviewLocation'] : false;
		return $this->do_filter( 'snippet/review/location', $location );
	}

	/**
	 * Check if we can add content if multipage.
	 *
	 * @return bool
	 */
	private function can_add_multi_page() {
		global $multipage, $numpages, $page;

		return ( ! $multipage || $page === $numpages );
	}
}
