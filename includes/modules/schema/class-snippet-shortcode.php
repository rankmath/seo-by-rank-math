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
use RankMath\Helpers\Str;
use RankMath\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Snippet_Shortcode class.
 */
class Snippet_Shortcode {

	use Hooker;
	use Shortcode;

	/**
	 * Post object.
	 *
	 * @var object
	 */
	private $post;

	/**
	 * Schema data.
	 *
	 * @var array
	 */
	private $schema;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->add_shortcode( 'rank_math_rich_snippet', 'rich_snippet' );
		$this->add_shortcode( 'rank_math_review_snippet', 'rich_snippet' );

		if ( ! is_admin() ) {
			$this->filter( 'the_content', 'output_schema_in_content', 11 );
		}
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
			[
				'id'        => false,
				'post_id'   => Param::get( 'post_id' ) ? Param::get( 'post_id' ) : get_the_ID(),
				'classname' => '',
				'is_block'  => false,
			],
			$atts,
			'rank_math_rich_snippet'
		);

		if ( 'edit' === Param::get( 'context' ) ) {
			rank_math()->variables->setup();
		}

		$data = $this->get_schema_data( $atts['id'], $atts['post_id'] );
		if ( empty( $data ) || empty( $data['schema'] ) ) {
			return esc_html__( 'No schema found.', 'rank-math' );
		}

		$post    = get_post( $data['post_id'] );
		$schemas = ! empty( $atts['id'] ) ? [ $data['schema'] ] : $data['schema'];

		$html = '';

		foreach ( $schemas as $schema ) {
			$schema = $this->replace_variables( $schema, $post );
			$schema = $this->do_filter( 'schema/shortcode/filter_attributes', $schema, $atts );

			if ( empty( $schema ) ) {
				continue;
			}

			/**
			 * Change the Schema HTML output.
			 *
			 * @param string            $unsigned HTML output.
			 * @param array             $schema   Schema data.
			 * @param WP_Post           $post     The post instance.
			 * @param Snippet_Shortcode $this     Snippet_Shortcode instance.
			 */
			$html .= $this->do_filter( 'snippet/html', $this->get_snippet_content( $schema, $post, $atts ), $schema, $post, $this );
		}

		return $html;
	}

	/**
	 * Get Snippet content.
	 *
	 * @param  array   $schema Schema to replace.
	 * @param  WP_Post $post   Post schema attached to.
	 * @param  array   $atts   Optional. Shortcode arguments - currently only 'show'
	 *                     parameter, which is a comma-separated list of elements to show.
	 *
	 * @return string Shortcode output.
	 */
	public function get_snippet_content( $schema, $post, $atts ) {
		wp_enqueue_style( 'rank-math-review-snippet', untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/blocks/schema/assets/css/schema.css', null, rank_math()->version );
		$type         = \strtolower( $schema['@type'] );
		$type         = preg_replace( '/[^a-z0-9_-]+/i', '', $type );
		$this->post   = $post;
		$this->schema = $schema;

		if ( in_array( $type, [ 'article', 'blogposting', 'newsarticle' ], true ) ) {
			return;
		}

		if ( Str::ends_with( 'event', $type ) ) {
			$type = 'event';
		}

		if ( 'resturant' === $type ) {
			$type = 'restaurant';
		}

		$class = ! empty( $atts['classname'] ) ? $atts['classname'] : '';

		ob_start();
		?>
			<div id="rank-math-rich-snippet-wrapper" class="<?php echo esc_attr( $class ); ?>">

				<?php
				$type = sanitize_file_name( $type );
				$file = rank_math()->plugin_dir() . "includes/modules/schema/shortcode/$type.php";
				if ( file_exists( $file ) ) {
					include $file;
				}

				$this->do_action( 'snippet/after_schema_content', $this );
				?>

			</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Get field value.
	 *
	 * @param  string $field_id      Field id.
	 * @param  mixed  $default_value Default value.
	 * @return mixed
	 */
	public function get_field_value( $field_id, $default_value = null ) {
		$array = $this->schema;
		if ( isset( $array[ $field_id ] ) ) {
			if ( isset( $array[ $field_id ]['@type'] ) ) {
				unset( $array[ $field_id ]['@type'] );
			}

			return $array[ $field_id ];
		}

		foreach ( explode( '.', $field_id ) as $segment ) {
			if ( ! is_array( $array ) || ! array_key_exists( $segment, $array ) ) {
				return $default_value;
			}

			$array = $array[ $segment ];
		}

		return $array;
	}

	/**
	 * Get field.
	 *
	 * @param  string $title         Field title.
	 * @param  string $field_id      Field id to get value.
	 * @param  string $convert_date  Convert date value to proper format.
	 * @param  mixed  $default_value Default value.
	 */
	public function get_field( $title, $field_id, $convert_date = false, $default_value = null ) {
		$value = $this->get_field_value( $field_id, $default_value );
		if ( empty( $value ) ) {
			return;
		}

		if ( $convert_date ) {
			$value = Helper::convert_date( $value );
		}

		$this->output_field( $title, $value );
	}

	/**
	 * Get Opening hours data.
	 *
	 * @param string $field_id Field id to get value.
	 */
	public function get_opening_hours( $field_id ) {
		$opening_hours = $this->get_field_value( $field_id );
		if ( empty( $opening_hours ) ) {
			return;
		}

		if ( count( array_filter( array_keys( $opening_hours ), 'is_string' ) ) > 0 ) {
			$this->get_opening_hour( $opening_hours );
			return;
		}

		echo '<div>';
		echo '<strong>' . esc_html__( 'Opening Hours', 'rank-math' ) . '</strong><br />';
		foreach ( $opening_hours as $opening_hour ) {
			$this->get_opening_hour( $opening_hour );
		}
		echo '</div>';
	}

	/**
	 * Get Opening hours.
	 *
	 * @param array $opening_hour Opening hours data.
	 */
	public function get_opening_hour( $opening_hour ) {
		$labels = [
			'dayOfWeek' => esc_html__( 'Days', 'rank-math' ),
			'opens'     => esc_html__( 'Opening Time', 'rank-math' ),
			'closes'    => esc_html__( 'Closing Time', 'rank-math' ),
		];
		foreach ( $labels as $key => $label ) {
			if ( empty( $opening_hour[ $key ] ) ) {
				continue;
			}

			$this->output_field( $label, $opening_hour[ $key ] );
		}
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
			<strong><?php echo esc_html( $title ); // phpcs:ignore ?>: </strong>
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
		<h5 class="rank-math-title"><?php echo esc_html( $title ); // phpcs:ignore ?></h5>
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
		<p><?php echo wp_kses_post( do_shortcode( $description ) ); ?></p>
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

		$best_rating = (int) $this->get_field_value( 'review.reviewRating.bestRating', 5 );
		?>
		<div class="rank-math-total-wrapper">

			<strong><?php echo $this->do_filter( 'review/text', esc_html__( 'Editor\'s Rating:', 'rank-math' ) ); // phpcs:ignore ?></strong><br />

			<span class="rank-math-total"><?php echo $rating; // phpcs:ignore ?></span>

			<div class="rank-math-review-star">

				<div class="rank-math-review-result-wrapper">

					<?php echo \str_repeat( '<i class="rank-math-star"></i>', $best_rating ); // phpcs:ignore ?>

					<div class="rank-math-review-result" style="width:<?php echo ( $rating * ( 100 / $best_rating ) ); // phpcs:ignore ?>%;">
						<?php echo \str_repeat( '<i class="rank-math-star"></i>', $best_rating ); // phpcs:ignore ?>
					</div>

				</div>

			</div>

		</div>
		<?php
	}

	/**
	 * Add schema data in the content.
	 *
	 * @param  string $content Post content.
	 * @return string
	 *
	 * @since 1.0.12
	 */
	public function output_schema_in_content( $content ) {
		if ( ! is_singular() ) {
			return $content;
		}

		$schemas = $this->get_schemas();
		if ( empty( $schemas ) ) {
			return $content;
		}

		foreach ( $schemas as $schema ) {
			$location = $this->get_content_location( $schema );
			if ( false === $location || 'custom' === $location ) {
				continue;
			}

			$review = do_shortcode( '[rank_math_rich_snippet id="' . $schema['metadata']['shortcode'] . '"]' );
			if ( in_array( $location, [ 'top', 'both' ], true ) ) {
				$content = $review . $content;
			}

			if ( in_array( $location, [ 'bottom', 'both' ], true ) && $this->can_add_multi_page() ) {
				$content .= $review;
			}
		}

		return $content;
	}

	/**
	 * Get schema data by shortcode/post ID.
	 *
	 * @param  string $shortcode_id Schema shortcode ID.
	 * @param  string $post_id      Post ID.
	 * @return array
	 */
	private function get_schema_data( $shortcode_id, $post_id = false ) {
		if ( ! empty( $shortcode_id ) && is_string( $shortcode_id ) ) {
			return DB::get_schema_by_shortcode_id( $shortcode_id );
		}

		if ( ! $post_id ) {
			$post_id = Param::get( 'post_id' ) ? Param::get( 'post_id' ) : get_the_ID();
		}

		$data = DB::get_schemas( $post_id );
		return empty( $data ) ? false : [
			'post_id' => $post_id,
			'schema'  => $data,
		];
	}

	/**
	 * Function to replace variables used in Schema fields.
	 *
	 * @param  array   $schemas Schema to replace.
	 * @param  WP_Post $post    Post schema attached to.
	 * @return array
	 */
	private function replace_variables( $schemas, $post ) {
		if ( ! is_array( $schemas ) && ! is_object( $schemas ) ) {
			return [];
		}

		$new_schemas = [];
		foreach ( $schemas as $key => $schema ) {
			if ( 'metadata' === $key ) {
				continue;
			}

			if ( is_array( $schema ) ) {
				$new_schemas[ $key ] = $this->replace_variables( $schema, $post );
				continue;
			}

			// Need this conditions to convert date to valid ISO 8601 format.
			if ( in_array( $key, [ 'datePublished', 'uploadDate' ], true ) && '%date(Y-m-dTH:i:sP)%' === $schema ) {
				$schema = '%date(Y-m-d\TH:i:sP)%';
			}
			if ( 'dateModified' === $key && '%modified(Y-m-dTH:i:sP)%' === $schema ) {
				$schema = '%modified(Y-m-d\TH:i:sP)%';
			}

			$new_schemas[ $key ] = Str::contains( '%', $schema ) ? Helper::replace_seo_fields( $schema, $post ) : $schema;
		}

		return $new_schemas;
	}

	/**
	 * Check if we can inject the review in the content.
	 *
	 * @param array $schema Schema Data.
	 *
	 * @return boolean|string
	 */
	private function get_content_location( $schema ) {
		$location = ! empty( $schema['metadata']['shortcode'] ) && isset( $schema['metadata']['reviewLocation'] ) ? $schema['metadata']['reviewLocation'] : false;
		return $this->do_filter( 'snippet/review/location', $location );
	}

	/**
	 * Get schema data to show in the content.
	 *
	 * @return boolean|array
	 *
	 * @since 1.0.59
	 */
	private function get_schemas() {
		/**
		 * Filter: Allow disabling the review display.
		 *
		 * @param bool $return True to disable.
		 */
		if ( ! is_main_query() || ! in_the_loop() || $this->do_filter( 'snippet/review/hide_data', false ) ) {
			return false;
		}

		$schemas = $this->get_schema_data( false );
		if ( empty( $schemas ) ) {
			return false;
		}

		return array_filter(
			$schemas['schema'],
			function ( $schema ) {
				return ! empty( $schema['metadata']['reviewLocation'] );
			}
		);
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
