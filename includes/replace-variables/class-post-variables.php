<?php
/**
 * Post variable replacer.
 *
 * @since      1.0.33
 * @package    RankMath
 * @subpackage RankMath\Replace_Variables
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Replace_Variables;

use RankMath\Helper;
use RankMath\Helpers\Str;
use RankMath\Post;
use RankMath\Paper\Paper;

defined( 'ABSPATH' ) || exit;

/**
 * Post_Variables class.
 */
class Post_Variables extends Advanced_Variables {

	/**
	 * Setup post variables.
	 */
	public function setup_post_variables() {
		$this->args = (object) wp_parse_args( array_filter( (array) $this->get_post() ), $this->get_defaults() );

		$this->register_replacement(
			'title',
			[
				'name'        => esc_html__( 'Post Title', 'rank-math' ),
				'description' => esc_html__( 'Title of the current post/page', 'rank-math' ),
				'variable'    => 'title',
				'example'     => $this->get_title(),
				'nocache'     => true,
			],
			[ $this, 'get_title' ]
		);

		$this->register_replacement(
			'parent_title',
			[
				'name'        => esc_html__( 'Post Title of parent page', 'rank-math' ),
				'description' => esc_html__( 'Title of the parent page of the current post/page', 'rank-math' ),
				'variable'    => 'parent_title',
				'example'     => $this->get_parent_title(),
			],
			[ $this, 'get_parent_title' ]
		);

		$this->register_replacement(
			'excerpt',
			[
				'name'        => esc_html__( 'Post Excerpt', 'rank-math' ),
				'description' => esc_html__( 'Excerpt of the current post (or auto-generated if it does not exist)', 'rank-math' ),
				'variable'    => 'excerpt',
				'example'     => $this->get_excerpt(),
				'nocache'     => true,
			],
			[ $this, 'get_excerpt' ]
		);

		$this->register_replacement(
			'excerpt_only',
			[
				'name'        => esc_html__( 'Post Excerpt', 'rank-math' ),
				'description' => esc_html__( 'Excerpt of the current post (without auto-generation)', 'rank-math' ),
				'variable'    => 'excerpt_only',
				'example'     => $this->is_post_edit && $this->args->post_excerpt ? $this->args->post_excerpt : esc_html__( 'Post Excerpt Only', 'rank-math' ),
				'nocache'     => true,
			],
			[ $this, 'get_excerpt_only' ]
		);

		$this->register_replacement(
			'seo_title',
			[
				'name'        => esc_html__( 'SEO Title', 'rank-math' ),
				'description' => esc_html__( 'Custom or Generated SEO Title of the current post/page', 'rank-math' ),
				'variable'    => 'seo_title',
				'example'     => $this->get_title(),
				'nocache'     => true,
			],
			[ $this, 'get_seo_title' ]
		);

		$this->register_replacement(
			'seo_description',
			[
				'name'        => esc_html__( 'SEO Description', 'rank-math' ),
				'description' => esc_html__( 'Custom or Generated SEO Description of the current post/page', 'rank-math' ),
				'variable'    => 'seo_description',
				'example'     => $this->get_excerpt(),
				'nocache'     => true,
			],
			[ $this, 'get_seo_description' ]
		);

		$this->register_replacement(
			'url',
			[
				'name'        => esc_html__( 'Post URL', 'rank-math' ),
				'description' => esc_html__( 'URL of the current post/page', 'rank-math' ),
				'variable'    => 'url',
				'example'     => $this->get_url(),
			],
			[ $this, 'get_url' ]
		);

		$this->register_replacement(
			'post_thumbnail',
			[
				'name'        => esc_html__( 'Post Thumbnail', 'rank-math' ),
				'description' => esc_html__( 'Current Post Thumbnail', 'rank-math' ),
				'variable'    => 'post_thumbnail',
				'example'     => $this->get_post_thumbnail(),
				'nocache'     => true,
			],
			[ $this, 'get_post_thumbnail' ]
		);

		$this->setup_post_dates_variables();
		$this->setup_post_category_variables();
		$this->setup_post_tags_variables();
	}

	/**
	 * Setup post dates variables.
	 */
	public function setup_post_dates_variables() {
		$this->register_replacement(
			'date',
			[
				'name'        => esc_html__( 'Date Published', 'rank-math' ),
				'description' => wp_kses_post( __( 'Publication date of the current post/page <strong>OR</strong> specified date on date archives', 'rank-math' ) ),
				'variable'    => 'date',
				'example'     => $this->is_post_edit ? get_the_date() : current_time( get_option( 'date_format' ) ),
				'nocache'     => true,
			],
			[ $this, 'get_date' ]
		);

		$this->register_replacement(
			'modified',
			[
				'name'        => esc_html__( 'Date Modified', 'rank-math' ),
				'description' => esc_html__( 'Last modification date of the current post/page', 'rank-math' ),
				'variable'    => 'modified',
				'example'     => $this->is_post_edit ? get_the_modified_date() : current_time( get_option( 'date_format' ) ),
				'nocache'     => true,
			],
			[ $this, 'get_modified' ]
		);

		$this->register_replacement(
			'date_args',
			[
				'name'        => esc_html__( 'Date Published (advanced)', 'rank-math' ),
				'description' => esc_html__( 'Publish date with custom formatting pattern.', 'rank-math' ),
				'variable'    => 'date(F jS, Y)',
				'example'     => date_i18n( 'F jS, Y' ),
			],
			[ $this, 'get_date' ]
		);

		$this->register_replacement(
			'modified_args',
			[
				'name'        => esc_html__( 'Date Modified (advanced)', 'rank-math' ),
				'description' => esc_html__( 'Modified date with custom formatting pattern.', 'rank-math' ),
				'variable'    => 'modified(F jS, Y)',
				'example'     => date_i18n( 'F jS, Y' ),
			],
			[ $this, 'get_modified' ]
		);
	}

	/**
	 * Setup post category variables.
	 */
	public function setup_post_category_variables() {
		$category   = $this->get_category();
		$categories = $this->get_categories();
		$this->register_replacement(
			'category',
			[
				'name'        => esc_html__( 'Post Category', 'rank-math' ),
				'description' => wp_kses_post( __( 'First category (alphabetically) associated to the current post <strong>OR</strong> current category on category archives', 'rank-math' ) ),
				'variable'    => 'category',
				'example'     => $category ? $category : esc_html__( 'Example Category', 'rank-math' ),
			],
			[ $this, 'get_category' ]
		);

		$this->register_replacement(
			'categories',
			[
				'name'        => esc_html__( 'Post Categories', 'rank-math' ),
				'description' => esc_html__( 'Comma-separated list of categories associated to the current post', 'rank-math' ),
				'variable'    => 'categories',
				'example'     => $categories ? $categories : esc_html__( 'Example Category 1, Example Category 2', 'rank-math' ),
			],
			[ $this, 'get_categories' ]
		);

		$this->register_replacement(
			'categories_args',
			[
				'name'        => esc_html__( 'Categories (advanced)', 'rank-math' ),
				'description' => esc_html__( 'Output list of categories associated to the current post, with customization options.', 'rank-math' ),
				'variable'    => 'categories(limit=3&separator= | &exclude=12,23)',
				'example'     => $categories ? $categories : esc_html__( 'Example Category 1, Example Category 2', 'rank-math' ),
			],
			[ $this, 'get_categories' ]
		);

		$this->register_replacement(
			'primary_taxonomy_terms',
			[
				'name'        => esc_html__( 'Primary Terms', 'rank-math' ),
				'variable'    => 'primary_taxonomy_terms',
				'description' => esc_html__( 'Output list of terms from the primary taxonomy associated to the current post.', 'rank-math' ),
				'example'     => $this->get_primary_taxonomy_terms(),
			],
			[ $this, 'get_primary_taxonomy_terms' ]
		);
	}

	/**
	 * Setup post tags variables.
	 */
	public function setup_post_tags_variables() {
		$tag  = $this->get_tag();
		$tags = $this->get_tags();
		$this->register_replacement(
			'tag',
			[
				'name'        => esc_html__( 'Post Tag', 'rank-math' ),
				'description' => wp_kses_post( __( 'First tag (alphabetically) associated to the current post <strong>OR</strong> current tag on tag archives', 'rank-math' ) ),
				'variable'    => 'tag',
				'example'     => $tag ? $tag : esc_html__( 'Example Tag', 'rank-math' ),
				'nocache'     => true,
			],
			[ $this, 'get_tag' ]
		);

		$this->register_replacement(
			'tags',
			[
				'name'        => esc_html__( 'Post Tags', 'rank-math' ),
				'description' => esc_html__( 'Comma-separated list of tags associated to the current post', 'rank-math' ),
				'variable'    => 'tags',
				'example'     => $tags ? $tags : esc_html__( 'Example Tag 1, Example Tag 2', 'rank-math' ),
				'nocache'     => true,
			],
			[ $this, 'get_tags' ]
		);

		$this->register_replacement(
			'tags_args',
			[
				'name'        => esc_html__( 'Tags (advanced)', 'rank-math' ),
				'description' => esc_html__( 'Output list of tags associated to the current post, with customization options.', 'rank-math' ),
				'variable'    => 'tags(limit=3&separator= | &exclude=12,23)',
				'example'     => $tags ? $tags : esc_html__( 'Example Tag 1 | Example Tag 2', 'rank-math' ),
				'nocache'     => true,
			],
			[ $this, 'get_tags' ]
		);
	}

	/**
	 * Get the title of the post to use as a replacement.
	 *
	 * @return string|null
	 */
	public function get_title() {
		// Get post type name as Title.
		if ( is_post_type_archive() && ! Post::is_shop_page() ) {
			$post_type = $this->get_queried_post_type();
			return get_post_type_object( $post_type )->labels->name;
		}

		return Str::is_non_empty( $this->args->post_title ) ? stripslashes( $this->args->post_title ) : null;
	}

	/**
	 * Custom or Generated SEO Title
	 *
	 * @return string
	 */
	public function get_seo_title() {
		if ( is_singular() || is_category() || is_tag() || is_tax() ) {
			return Paper::get()->get_title();
		}

		$object = $this->args;

		// Early Bail!
		if ( empty( $object ) || empty( $object->ID ) ) {
			return '';
		}

		$title = Post::get_meta( 'title', $object->ID );
		if ( '' !== $title ) {
			return $title;
		}

		return Paper::get_from_options( "pt_{$object->post_type}_title", $object, '%title% %sep% %sitename%' );
	}

	/**
	 * Custom or Generated SEO Description
	 *
	 * @return string
	 */
	public function get_seo_description() {
		if ( is_singular() || is_category() || is_tag() || is_tax() ) {
			return Paper::get()->get_description();
		}

		$object = $this->args;

		// Early Bail!
		if ( empty( $object ) || empty( $object->ID ) ) {
			return '';
		}

		$description = Post::get_meta( 'description', $object->ID );
		if ( '' !== $description ) {
			return $description;
		}

		return Paper::get_from_options( "pt_{$object->post_type}_description", $object, '%excerpt%' );
	}

	/**
	 * Get the parent page title of the current page/CPT to use as a replacement.
	 *
	 * @return string|null
	 */
	public function get_parent_title() {
		$on_screen  = is_singular() || is_admin();
		$has_parent = isset( $this->args->post_parent ) && 0 !== $this->args->post_parent;

		return $on_screen && $has_parent ? get_the_title( $this->args->post_parent ) : null;
	}

	/**
	 * Get the post excerpt to use as a replacement. It will be auto-generated if it does not exist.
	 *
	 * @return string|null
	 */
	public function get_excerpt() {
		$object = $this->args;

		// Early Bail!
		if ( empty( $object ) ) {
			return '';
		}

		return ! empty( $object->post_excerpt ) ? wp_strip_all_tags( $object->post_excerpt ) : $this->get_post_content( $object );
	}

	/**
	 * Get the post excerpt to use as a replacement (without auto-generating).
	 *
	 * @return string|null
	 */
	public function get_excerpt_only() {
		$has = '' !== $this->args->post_excerpt && ! empty( $this->args->ID ) && ! post_password_required( $this->args->ID );

		return $has ? wp_strip_all_tags( $this->args->post_excerpt ) : null;
	}

	/**
	 * Get the date of the post to use as a replacement.
	 *
	 * @param string $format (Optional) PHP date format.
	 * @return string|null
	 */
	public function get_date( $format = '' ) {
		if ( is_array( $format ) && empty( $format ) ) {
			$format = '';
		}

		if ( '' !== $this->args->post_date ) {
			$format = $format ? $format : get_option( 'date_format' );
			return mysql2date( $format, $this->args->post_date, true );
		}

		if ( ! empty( get_query_var( 'day' ) ) ) {
			return get_the_date( $format );
		}

		$replacement = single_month_title( ' ', false );
		if ( Str::is_non_empty( $replacement ) ) {
			return $replacement;
		}

		return ! empty( get_query_var( 'year' ) ) ? get_query_var( 'year' ) : null;
	}

	/**
	 * Get the post modified time to use as a replacement.
	 *
	 * @param string $format (Optional) PHP date format.
	 * @return string|null
	 */
	public function get_modified( $format = '' ) {
		if ( ! empty( $this->args->post_modified ) && ! empty( $this->args->post_date ) ) {
			$modified = strtotime( $this->args->post_date ) > strtotime( $this->args->post_modified ) ? $this->args->post_date : $this->args->post_modified;
			$format   = $format ? $format : get_option( 'date_format' );
			return mysql2date( $format, $modified, true );
		}

		return null;
	}

	/**
	 * Get the post category to use as a replacement.
	 *
	 * @return string|null
	 */
	public function get_category() {
		if ( ! empty( $this->args->ID ) ) {
			$cat = $this->get_terms( $this->args->ID, 'category', true );
			if ( '' !== $cat ) {
				return $cat;
			}
		}

		return ! empty( $this->args->cat_name ) ? $this->args->cat_name : null;
	}

	/**
	 * Get the comma-separate post categories to use as a replacement.
	 *
	 * @param array $args Array of arguments.
	 * @return string|null
	 */
	public function get_categories( $args = [] ) {
		if ( ! empty( $this->args->ID ) ) {
			$cat = $this->get_terms( $this->args->ID, 'category', false, $args );
			if ( '' !== $cat ) {
				return $cat;
			}
		}

		return null;
	}

	/**
	 * Get the current tag to use as a replacement.
	 *
	 * @return string|null
	 */
	public function get_tag() {
		if ( ! empty( $this->args->ID ) ) {
			$tags = $this->get_terms( $this->args->ID, 'post_tag', true );
			if ( '' !== $tags ) {
				return $tags;
			}
		}

		return null;
	}

	/**
	 * Get the current tags to use as a replacement.
	 *
	 * @param array $args Arguments for get_terms().
	 * @return string|null
	 */
	public function get_tags( $args = [] ) {
		if ( ! empty( $this->args->ID ) ) {
			$tags = $this->get_terms( $this->args->ID, 'post_tag', false, $args );
			if ( '' !== $tags ) {
				return $tags;
			}
		}

		return null;
	}

	/**
	 * Get the comma separated post terms.
	 *
	 * @return string|null
	 */
	public function get_primary_taxonomy_terms() {
		if ( empty( $this->args->ID ) ) {
			return;
		}

		$post_type = get_post_type( $this->args->ID );
		$main_tax  = Helper::get_settings( "titles.pt_{$post_type}_primary_taxonomy" );
		if ( ! $main_tax ) {
			return;
		}

		$terms = wp_get_object_terms(
			$this->args->ID,
			$main_tax,
			[ 'fields' => 'names' ]
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return;
		}

		return implode( ', ', $terms );
	}

	/**
	 * Get the auto generated post content.
	 *
	 * @param array $post Post Object.
	 * @return string|null
	 */
	private function get_post_content( $post ) {
		if ( empty( $post->post_content ) ) {
			return '';
		}

		$keywords     = Post::get_meta( 'focus_keyword', $post->ID );
		$post_content = Paper::should_apply_shortcode() ? do_shortcode( $post->post_content ) : $post->post_content;
		$post_content = \preg_replace( '/<!--[\s\S]*?-->/iu', '', $post_content );
		$post_content = wpautop( Helper::strip_shortcodes( $post_content ) );
		$post_content = wp_kses( $post_content, [ 'p' => [] ] );

		// Remove empty paragraph tags.
		$post_content = preg_replace( '/<p[^>]*>(\s|&nbsp;)*<\/p>/', '', $post_content );

		// 4. Paragraph with the focus keyword.
		if ( ! empty( $keywords ) ) {
			$primary_keyword = explode( ',', $keywords );
			$primary_keyword = trim( $primary_keyword[0] );
			$regex           = '/<p>(.*' . str_replace( [ ',', ' ', '/', '(', ')', '[', ']', '{', '}', '?', '*', '+', '^', '$' ], [ '|', '.', '\/', '\(', '\)', '\[', '\]', '\{', '\}', '\?', '\*', '\+', '\^', '\$' ], $primary_keyword ) . '.*)<\/p>/iu';
			\preg_match_all( $regex, $post_content, $matches );
			if ( isset( $matches[1], $matches[1][0] ) ) {
				return $matches[1][0];
			}
		}

		// 5. The First paragraph of the content.
		\preg_match_all( '/<p>(.*)<\/p>/iu', $post_content, $matches );
		return isset( $matches[1], $matches[1][0] ) ? $matches[1][0] : $post_content;
	}

	/**
	 * Default post data.
	 *
	 * @return array
	 */
	private function get_defaults() {
		$defaults = Replacer::$defaults;

		if ( $this->is_post_edit ) {
			$defaults['post_author']  = 'Author Name';
			$defaults['post_content'] = 'Post content';
			$defaults['post_title']   = 'Post Title';
		}

		return $defaults;
	}

	/**
	 * Get the canonical URL to use as a replacement.
	 *
	 * @return string|null
	 */
	public function get_url() {
		return Paper::get()->get_canonical() ? Paper::get()->get_canonical() : get_the_permalink( $this->args->ID );
	}

	/**
	 * Get the the post thumbnail to use as a replacement.
	 *
	 * @return string|null
	 */
	public function get_post_thumbnail() {
		if ( ! has_post_thumbnail( $this->args->ID ) ) {
			return '';
		}

		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $this->args->ID ), 'full' );
		return ! empty( $image ) ? $image[0] : '';
	}
}
