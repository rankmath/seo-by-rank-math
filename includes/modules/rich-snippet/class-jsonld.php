<?php
/**
 * Outputs schema code specific for Google's JSON LD stuff
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\RichSnippet;

use RankMath\Helper;
use RankMath\Paper\Paper;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Url;
use MyThemeShop\Helpers\Conditional;
use MyThemeShop\Helpers\WordPress;
use MyThemeShop\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * JsonLD class.
 */
class JsonLD {

	use Hooker;

	/**
	 * Hold post object.
	 *
	 * @var WP_Post
	 */
	public $post = null;

	/**
	 * Hold post ID.
	 *
	 * @var ID
	 */
	public $post_id = 0;

	/**
	 * Hold post parts.
	 *
	 * @var array
	 */
	public $parts = [];

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'rank_math/head', 'json_ld', 90 );
		$this->action( 'rank_math/json_ld', 'add_context_data' );
		new Block_Parser();
	}

	/**
	 * JSON LD output function that the functions for specific code can hook into.
	 */
	public function json_ld() {
		global $post;

		if ( is_singular() ) {
			$this->post    = $post;
			$this->post_id = $post->ID;
			$this->get_parts();
		}

		/**
		 * Collect data to output in JSON-LD.
		 *
		 * @param array  $unsigned An array of data to output in json-ld.
		 * @param JsonLD $unsigned JsonLD instance.
		 */
		$data = $this->do_filter( 'json_ld', [], $this );
		if ( is_array( $data ) && ! empty( $data ) ) {
			$json = [
				'@context' => 'https://schema.org',
				'@graph'   => array_values( array_filter( $data ) ),
			];
			echo '<script type="application/ld+json">' . wp_json_encode( $json ) . '</script>' . "\n";
		}
	}

	/**
	 * Get Default Schema Data.
	 *
	 * @param array $data Array of json-ld data.
	 *
	 * @return array
	 */
	public function add_context_data( $data ) {
		$is_product_page = $this->is_product_page();
		$snippets        = [
			'\\RankMath\\RichSnippet\\Website'         => is_front_page(),
			'\\RankMath\\RichSnippet\\Search_Results'  => is_search(),
			'\\RankMath\\RichSnippet\\Author'          => is_author(),
			'\\RankMath\\RichSnippet\\Products_Page'   => $is_product_page,
			'\\RankMath\\RichSnippet\\Collection_Page' => ! $is_product_page && ( is_category() || is_tag() || is_tax() ),
			'\\RankMath\\RichSnippet\\Blog'            => is_home(),
			'\\RankMath\\RichSnippet\\Singular'        => is_singular(),
			'\\RankMath\\RichSnippet\\Breadcrumbs'     => $this->can_add_breadcrumb(),
		];

		foreach ( $snippets as $class => $can_run ) {
			if ( $can_run ) {
				$class = new $class();
				$data  = $class->process( $data, $this );
			}
		}

		return $data;
	}

	/**
	 * Can add breadcrumb snippet.
	 *
	 * @return bool
	 */
	private function can_add_breadcrumb() {
		/**
		 * Allow developer to disable the breadcrumb json-ld output.
		 *
		 * @param bool $unsigned Default: true
		 */
		return ! is_front_page() && Helper::get_settings( 'general.breadcrumbs' ) && $this->do_filter( 'json_ld/breadcrumbs_enabled', true );
	}

	/**
	 * Is product page.
	 *
	 * @return bool
	 */
	private function is_product_page() {
		return Conditional::is_woocommerce_active() && ( ( is_tax() && in_array( get_query_var( 'taxonomy' ), get_object_taxonomies( 'product' ), true ) ) || is_shop() );
	}

	/**
	 * Add property to entity.
	 *
	 * @param string $prop   Name of the property to add into entity.
	 * @param array  $entity Array of json-ld entity.
	 */
	public function add_prop( $prop, &$entity ) {
		if ( empty( $prop ) ) {
			return;
		}

		$hash = [
			'email' => [ 'titles.email', 'email' ],
			'image' => [ 'titles.knowledgegraph_logo', 'logo' ],
			'phone' => [ 'titles.phone', 'telephone' ],
		];

		if ( isset( $hash[ $prop ] ) && $value = Helper::get_settings( $hash[ $prop ][0] ) ) { // phpcs:ignore
			$entity[ $hash[ $prop ][1] ] = $value;
			return;
		}

		$perform = "add_prop_{$prop}";
		if ( method_exists( $this, $perform ) ) {
			$this->$perform( $entity );
		}
	}

	/**
	 * Add property to entity.
	 *
	 * @param array $entity Array of json-ld entity.
	 */
	private function add_prop_url( &$entity ) {
		if ( $url = Helper::get_settings( 'titles.url' ) ) { // phpcs:ignore
			$entity['url'] = ! Url::is_relative( $url ) ? $url : 'http://' . $url;
		}
	}

	/**
	 * Add property to entity.
	 *
	 * @param array $entity Array of json-ld entity.
	 */
	private function add_prop_address( &$entity ) {
		if ( $address = Helper::get_settings( 'titles.local_address' ) ) { // phpcs:ignore
			$entity['address'] = [ '@type' => 'PostalAddress' ] + $address;
		}
	}

	/**
	 * Add property to entity.
	 *
	 * @param array $entity Array of json-ld entity.
	 */
	private function add_prop_thumbnail( &$entity ) {
		$image = Helper::get_thumbnail_with_fallback( get_the_ID(), 'full' );
		if ( ! empty( $image ) ) {
			$entity['image'] = [
				'@type'  => 'ImageObject',
				'url'    => $image[0],
				'width'  => $image[1],
				'height' => $image[2],
			];
		}
	}

	/**
	 * Add aggregateratings to entity.
	 *
	 * @param string $schema Schema to get data for.
	 * @param array  $entity Array of json-ld entity to attach data to.
	 */
	public function add_ratings( $schema, &$entity ) {
		$rating = Helper::get_post_meta( "snippet_{$schema}_rating" );

		// Early Bail!
		if ( ! $rating ) {
			return;
		}

		$entity['review'] = [
			'author'        => [
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name' ),
			],
			'datePublished' => get_post_time( 'Y-m-d\TH:i:sP', false ),
			'dateModified'  => get_post_modified_time( 'Y-m-d\TH:i:sP', false ),
			'reviewRating'  => [
				'@type'       => 'Rating',
				'ratingValue' => $rating,
				'bestRating'  => Helper::get_post_meta( "snippet_{$schema}_rating_max" ) ? Helper::get_post_meta( "snippet_{$schema}_rating_max" ) : 5,
				'worstRating' => Helper::get_post_meta( "snippet_{$schema}_rating_min" ) ? Helper::get_post_meta( "snippet_{$schema}_rating_min" ) : 1,
			],
		];
	}

	/**
	 * Get website name with a fallback to bloginfo( 'name' ).
	 *
	 * @return string
	 */
	public function get_website_name() {
		$name = Helper::get_settings( 'titles.knowledgegraph_name' );

		return $name ? $name : get_bloginfo( 'name' );
	}

	/**
	 * Get post parts
	 *
	 * @param array $data Array of json-ld data.
	 *
	 * @return array
	 */
	public function get_post_collection( $data ) {
		$collection = [];
		while ( have_posts() ) {
			the_post();
			$this->get_post_collection_item( $collection, $data );
		}

		wp_reset_query();
		return $collection;
	}

	/**
	 * Process single post
	 *
	 * @param array $collection Collection holder.
	 * @param array $data       Array of json-ld data.
	 */
	public function get_post_collection_item( &$collection, $data ) {
		$post_id   = get_the_ID();
		$post_type = get_post_type();

		$schema         = Helper::get_post_meta( 'rich_snippet', $post_id );
		$default_schema = Helper::get_settings( "titles.pt_{$post_type}_default_rich_snippet" );

		$article_type         = Helper::get_post_meta( 'snippet_article_type', $post_id );
		$default_article_type = Helper::get_settings( "titles.pt_{$post_type}_default_article_type" );

		if ( ! $schema ) {
			$schema = $default_schema;
		}
		if ( ! $schema || 'article' !== $schema ) {
			return;
		}
		if ( ! $article_type ) {
			$article_type = $default_article_type ? $default_article_type : 'Article';
		}

		$this->post = get_post( $post_id );

		$title = $this->get_post_title( $post_id );
		$url   = $this->get_post_url( $post_id );

		$part = [
			'@type'            => ! empty( $data['schema'] ) ? $data['schema'] : $article_type,
			'headline'         => $title,
			'name'             => $title,
			'url'              => $url,
			'mainEntityOfPage' => $url,
			'dateModified'     => get_post_modified_time( 'Y-m-d\TH:i:sP', false ),
			'datePublished'    => get_post_time( 'Y-m-d\TH:i:sP', false ),
			'author'           => $this->get_author(),
			'publisher'        => $this->get_publisher( $data ),
			'image'            => $this->get_post_thumbnail( $post_id ),
			'keywords'         => $this->get_post_terms( $post_id ),
			'commentCount'     => get_comments_number(),
			'comment'          => $this->get_comments( $post_id ),
		];

		if ( 'article' === $schema ) {
			$part['wordCount'] = str_word_count( get_the_content() );
		}

		$collection[] = $part;
	}

	/**
	 * Get publisher
	 *
	 * @param array $data Entity.
	 *
	 * @return array
	 */
	public function get_publisher( $data ) {
		if ( ! isset( $data['Organization'] ) && ! isset( $data['Person'] ) ) {
			return [
				'@type' => 'Organization',
				'name'  => $this->get_website_name(),
				'logo'  => [
					'@type' => 'ImageObject',
					'url'   => Helper::get_settings( 'titles.knowledgegraph_logo' ),
				],
			];
		}

		$entity = [];
		if ( isset( $data['Organization'] ) ) {
			$this->set_publisher( $entity, $data['Organization'] );
			$logo = isset( $entity['publisher']['logo']['url'] ) ? $entity['publisher']['logo']['url'] : '';
		}

		if ( isset( $data['Person'] ) ) {
			$this->set_publisher( $entity, $data['Person'] );
			$logo                        = Helper::get_settings( 'titles.knowledgegraph_logo' );
			$entity['publisher']['logo'] = [
				'@type' => 'ImageObject',
				'url'   => $logo,
			];
		}

		$entity['publisher']['@type'] = 'Organization';

		return $entity['publisher'];
	}

	/**
	 * Get post thumbnail if any
	 *
	 * @param int $post_id  Post id to get featured image  for.
	 *
	 * @return array
	 */
	public function get_post_thumbnail( $post_id = 0 ) {
		if ( ! has_post_thumbnail( $post_id ) ) {
			return false;
		}

		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'full' );
		if ( empty( $image ) ) {
			return false;
		}

		return [
			'@type'  => 'ImageObject',
			'url'    => $image[0],
			'height' => $image[2],
			'width'  => $image[1],
		];
	}

	/**
	 * Get post terms
	 *
	 * @param int    $post_id  Post id to get terms  for.
	 * @param string $taxonomy Taxonomy name.
	 *
	 * @return array
	 */
	public function get_post_terms( $post_id = 0, $taxonomy = false ) {
		if ( false === $taxonomy ) {
			$taxonomy = get_queried_object();
			if ( ! is_object( $taxonomy ) ) {
				return [];
			}
			$taxonomy = $taxonomy->taxonomy;
		}

		$terms = wp_get_post_terms( $post_id, $taxonomy, [ 'fields' => 'names' ] );
		return is_wp_error( $terms ) || empty( $terms ) ? [] : $terms;
	}

	/**
	 * Get comments data
	 *
	 * @param int $post_id Post id to get comments for.
	 *
	 * @return array
	 */
	public function get_comments( $post_id = 0 ) {
		$post_comments = get_comments(
			[
				'post_id' => $post_id,
				'number'  => 10,
				'status'  => 'approve',
				'type'    => 'comment',
			]
		);

		if ( empty( $post_comments ) ) {
			return '';
		}

		$comments = [];
		foreach ( $post_comments as $comment ) {
			$comments[] = [
				'@type'       => 'Comment',
				'dateCreated' => $comment->comment_date,
				'description' => $comment->comment_content,
				'author'      => [
					'@type' => 'Person',
					'name'  => $comment->comment_author,
					'url'   => $comment->comment_author_url,
				],
			];
		}

		return $comments;
	}

	/**
	 * Get author data
	 *
	 * @return array
	 */
	public function get_author() {
		$author = [
			'@type' => 'Person',
			'name'  => get_the_author_meta( 'display_name' ),
			'url'   => esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		];

		if ( get_the_author_meta( 'description' ) ) {
			$author['description'] = wp_strip_all_tags( get_the_author_meta( 'description' ), true );
		}

		if ( version_compare( get_bloginfo( 'version' ), '4.2', '>=' ) ) {
			$image = get_avatar_url( get_the_author_meta( 'user_email' ), 96 );
			if ( $image ) {
				$author['image'] = [
					'@type'  => 'ImageObject',
					'url'    => $image,
					'height' => 96,
					'width'  => 96,
				];
			}
		}

		return $author;
	}

	/**
	 * Set publisher/provider data for JSON-LD.
	 *
	 * @param array  $entity Array of json-ld entity.
	 * @param array  $organization Organization data.
	 * @param string $type         Type data set to. Default: 'publisher'.
	 */
	public function set_publisher( &$entity, $organization, $type = 'publisher' ) {
		$keys = [ '@context', '@type', 'url', 'name', 'logo', 'image', 'contactPoint', 'sameAs' ];
		foreach ( $keys as $key ) {
			if ( ! isset( $organization[ $key ] ) ) {
				continue;
			}

			$entity[ $type ][ $key ] = 'logo' !== $key ? $organization[ $key ] : [
				'@type' => 'ImageObject',
				'url'   => $organization[ $key ],
			];
		}
	}

	/**
	 * Set address for JSON-LD.
	 *
	 * @param string $schema Schema to get data for.
	 * @param array  $entity Array of json-ld entity to attach data to.
	 */
	public function set_address( $schema, &$entity ) {
		$address = Helper::get_post_meta( "snippet_{$schema}_address" );

		// Early Bail!
		if ( ! is_array( $address ) || empty( $address ) ) {
			return;
		}

		$entity['address'] = [ '@type' => 'PostalAddress' ];
		foreach ( $address as $key => $value ) {
			$entity['address'][ $key ] = $value;
		}
	}

	/**
	 * Set data to entity.
	 *
	 * Loop through post meta value grab data and attache it to the entity.
	 *
	 * @param array $hash   Key to get data and Value to save as.
	 * @param array $entity Array of json-ld entity to attach data to.
	 */
	public function set_data( $hash, &$entity ) {
		foreach ( $hash as $metakey => $dest ) {
			$entity[ $dest ] = Helper::get_post_meta( $metakey, $this->post_id );
		}
	}

	/**
	 * Get post parts.
	 */
	private function get_parts() {
		$parts = [
			'title'     => $this->get_post_title(),
			'url'       => $this->get_post_url(),
			'canonical' => Paper::get()->get_canonical(),
			'modified'  => mysql2date( DATE_W3C, $this->post->post_modified, false ),
			'published' => mysql2date( DATE_W3C, $this->post->post_date, false ),
			'excerpt'   => Helper::replace_vars( '%excerpt%', $this->post ),
		];

		// Description.
		$desc = Helper::get_post_meta( 'snippet_desc' );

		if ( ! $desc ) {
			$desc = Helper::replace_vars( Helper::get_settings( "titles.pt_{$this->post->post_type}_default_snippet_desc" ), $this->post );
		}
		$parts['desc'] = $desc ? $desc : ( Helper::get_post_meta( 'description' ) ? Helper::get_post_meta( 'description' ) : $parts['excerpt'] );

		// Author.
		$author          = Helper::get_post_meta( 'snippet_author' );
		$parts['author'] = $author ? $author : get_the_author_meta( 'display_name', $this->post->post_author );

		$this->parts = $parts;
	}

	/**
	 * Get post title.
	 *
	 * Retrieves the title in this order.
	 *  1. Custom post meta set in rich snippet
	 *  2. Headline template set in Titles & Meta
	 *
	 * @param int $post_id Post ID to get title for.
	 *
	 * @return string
	 */
	public function get_post_title( $post_id = 0 ) {
		$title = Helper::get_post_meta( 'snippet_name', $post_id );

		if ( ! $title && ! empty( $this->post ) ) {
			$title = Helper::replace_vars( Helper::get_settings( "titles.pt_{$this->post->post_type}_default_snippet_name", '%seo_title%' ), $this->post );
		}

		$title = $title ? $title : Paper::get()->get_title();

		return Str::truncate( $title );
	}

	/**
	 * Get post url.
	 *
	 * @param  int $post_id Post ID to get url for.
	 * @return string
	 */
	public function get_post_url( $post_id = 0 ) {
		$url = Helper::get_post_meta( 'snippet_url', $post_id );

		return $url ? $url : ( 0 === $post_id ? Paper::get()->get_canonical() : get_the_permalink( $post_id ) );
	}

	/**
	 * Get product description.
	 *
	 * @param  object $product Product Object.
	 * @return string
	 */
	public function get_product_desc( $product = [] ) {
		if ( empty( $product ) ) {
			return;
		}

		if ( $description = Helper::get_post_meta( 'description', $product->get_id() ) ) { //phpcs:ignore
			return $description;
		}

		$description = $product->get_short_description() ? $product->get_short_description() : $product->get_description();
		$description = $this->do_filter( 'product_description/appy_shortcode', false ) ? do_shortcode( $description ) : WordPress::strip_shortcodes( $description );
		return wp_strip_all_tags( $description, true );
	}

	/**
	 * Get product title.
	 *
	 * @param  object $product Product Object.
	 * @return string
	 */
	public function get_product_title( $product = [] ) {
		if ( empty( $product ) ) {
			return '';
		}

		if ( $title = Helper::get_post_meta( 'title', $product->get_id() ) ) { //phpcs:ignore
			return $title;
		}

		return $product->get_name();
	}
}
