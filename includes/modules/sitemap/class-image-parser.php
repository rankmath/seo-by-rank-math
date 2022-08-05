<?php
/**
 * Parse images from a post.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 * @author     Rank Math <support@rankmath.com>
 *
 * @copyright Copyright (C) 2008-2019, Yoast BV
 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
 */

namespace RankMath\Sitemap;

use WP_Query;
use DOMDocument;
use RankMath\Helper;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Url;
use MyThemeShop\Helpers\Arr;

defined( 'ABSPATH' ) || exit;

/**
 * Image_Parser class.
 */
class Image_Parser {

	use Hooker;

	/**
	 * Holds the `home_url()` value to speed up loops.
	 *
	 * @var string
	 */
	protected $home_url = '';

	/**
	 * Holds site URL hostname.
	 *
	 * @var string
	 */
	protected $host = '';

	/**
	 * Holds site URL protocol.
	 *
	 * @var string
	 */
	protected $scheme = 'http';

	/**
	 * Cached set of attachments for multiple posts.
	 *
	 * @var array
	 */
	protected $attachments = [];

	/**
	 * Holds blog charset value for use in DOM parsing.
	 *
	 * @var string
	 */
	protected $charset = 'UTF-8';

	/**
	 * Hold post.
	 *
	 * @var object
	 */
	private $post = null;

	/**
	 * Hold parsed images data.
	 *
	 * @var array
	 */
	private $images = [];

	/**
	 * Set up URL properties for reuse.
	 */
	public function __construct() {
		$this->home_url = home_url();
		$parsed_home    = wp_parse_url( $this->home_url );

		if ( ! empty( $parsed_home['host'] ) ) {
			$this->host = str_replace( 'www.', '', $parsed_home['host'] );
		}

		if ( ! empty( $parsed_home['scheme'] ) ) {
			$this->scheme = $parsed_home['scheme'];
		}

		$this->charset = esc_attr( get_bloginfo( 'charset' ) );
	}

	/**
	 * Get set of image data sets for the given post.
	 *
	 * @param object $post Post object to get images for.
	 *
	 * @return array
	 */
	public function get_images( $post ) {
		if ( ! Helper::get_settings( 'sitemap.include_images' ) ) {
			return false;
		}

		$this->post = $post;
		if ( ! is_object( $this->post ) ) {
			return $this->images;
		}

		$this->get_post_thumbnail();
		$this->get_post_images();
		$this->get_post_galleries();
		$this->get_is_attachment();
		$this->get_custom_field_images();

		// Reset.
		$images       = $this->images;
		$this->images = [];
		$this->post   = null;

		/**
		 * Filter images to be included for the post in XML sitemap.
		 *
		 * @param array $images  Array of image items.
		 * @param int   $post_id ID of the post.
		 */
		return $this->do_filter( 'sitemap/urlimages', $images, $post->ID );
	}

	/**
	 * Get term images.
	 *
	 * @param object $term Term to get images from description for.
	 *
	 * @return array
	 */
	public function get_term_images( $term ) {
		if ( ! Helper::get_settings( 'sitemap.include_images' ) ) {
			return false;
		}

		$images = $this->parse_html_images( $term->description );
		foreach ( $this->parse_galleries( $term->description ) as $attachment ) {
			$images[] = [
				'src' => $this->get_absolute_url( $this->image_url( $attachment->ID ) ),
			];
		}

		return $images;
	}

	/**
	 * Get post thumbnail.
	 */
	private function get_post_thumbnail() {
		$thumbnail_id = get_post_thumbnail_id( $this->post->ID );
		if (
			! Helper::get_settings( 'sitemap.include_featured_image' ) ||
			! Helper::attachment_in_sitemap( $thumbnail_id )
		) {
			return;
		}

		$this->get_image_item( $this->get_absolute_url( $this->image_url( $thumbnail_id ) ) );
	}

	/**
	 * Get images from post content.
	 */
	private function get_post_images() {
		/**
		 * Filter: 'rank_math/sitemap/content_before_parse_html_images' - Filters the post content
		 * before it is parsed for images.
		 *
		 * @param string $content The raw/unprocessed post content.
		 */
		$content = $this->do_filter( 'sitemap/content_before_parse_html_images', $this->post->post_content, $this->post->ID );
		$content = apply_filters( 'the_content', $content );

		foreach ( $this->parse_html_images( $content ) as $image ) {
			$this->get_image_item( $image['src'] );
		}
	}

	/**
	 * Get post galleries.
	 */
	private function get_post_galleries() {
		foreach ( $this->parse_galleries( $this->post->post_content, $this->post->ID ) as $attachment ) {
			$this->get_image_item( $this->get_absolute_url( $this->image_url( $attachment->ID ) ) );
		}
	}

	/**
	 * Get image if post is attachment.
	 */
	private function get_is_attachment() {
		if ( 'attachment' === $this->post->post_type && wp_attachment_is_image( $this->post ) ) {
			$this->get_image_item( $this->get_absolute_url( $this->image_url( $this->post->ID ) ) );
		}
	}

	/**
	 * Get images from custom fields.
	 */
	private function get_custom_field_images() {
		$customs = Helper::get_settings( 'sitemap.pt_' . $this->post->post_type . '_image_customfields' );
		if ( empty( $customs ) ) {
			return;
		}

		$customs = Arr::from_string( $customs, "\n" );
		foreach ( $customs as $key ) {
			$src = get_post_meta( $this->post->ID, $key, true );
			if ( Str::is_non_empty( $src ) && preg_match( '/\.(jpg|jpeg|png|gif)$/i', $src ) ) {
				$this->get_image_item( $src );
			}
		}
	}

	/**
	 * Parse `<img />` tags in content.
	 *
	 * @param string $content Content string to parse.
	 *
	 * @return array
	 */
	private function parse_html_images( $content ) {
		$images   = [];
		$document = $this->get_document( $content );
		if ( false === $document ) {
			return $images;
		}

		foreach ( $document->getElementsByTagName( 'img' ) as $img ) {
			$src = $this->get_image_src( $img );
			if ( false === $src ) {
				continue;
			}

			$images[] = [ 'src' => $src ];
		}

		return $images;
	}

	/**
	 * Get DOM document.
	 *
	 * @param string $content Content to parse.
	 *
	 * @return bool|DOMDocument
	 */
	private function get_document( $content ) {
		if ( ! class_exists( 'DOMDocument' ) || empty( $content ) ) {
			return false;
		}

		// Prevent DOMDocument from bubbling warnings about invalid HTML.
		libxml_use_internal_errors( true );

		$post_dom = new DOMDocument();
		$post_dom->loadHTML( '<?xml encoding="' . $this->charset . '">' . $content );

		// Clear the errors, so they don't get kept in memory.
		libxml_clear_errors();

		return $post_dom;
	}

	/**
	 * Get image source from node.
	 *
	 * @param DOMNode $node Node instance.
	 *
	 * @return bool|string
	 */
	private function get_image_src( $node ) {
		$src = $node->getAttribute( 'src' );
		if ( $node->hasAttribute( 'data-sitemapexclude' ) || empty( $src ) ) {
			return false;
		}

		$class = $node->getAttribute( 'class' );
		if ( // This detects WP-inserted images, which we need to upsize. R.
			! empty( $class )
			&& ! Str::contains( 'size-full', $class )
			&& preg_match( '|wp-image-(?P<id>\d+)|', $class, $matches )
			&& get_post_status( $matches['id'] )
		) {
			$src = $this->image_url( $matches['id'] );
		}

		$src     = $this->get_absolute_url( $src );
		$no_host = esc_url( $src ) !== $src;
		if ( ! $this->do_filter( 'sitemap/include_external_image', false ) ) {
			$no_host = ! Str::contains( $this->host, $src ) || esc_url( $src ) !== $src;
		}

		return $no_host ? false : $src;
	}

	/**
	 * Parse gallery shortcodes in a given content.
	 *
	 * @param string $content Content string.
	 * @param int    $post_id Optional ID of post being parsed.
	 *
	 * @return array Set of attachment objects.
	 */
	private function parse_galleries( $content, $post_id = 0 ) {
		$attachments = [];
		$galleries   = $this->get_content_galleries( $content );

		foreach ( $galleries as $gallery ) {
			$id = $post_id;
			if ( ! empty( $gallery['id'] ) ) {
				$id = intval( $gallery['id'] );
			}

			// Forked from core gallery_shortcode() to have exact same logic. R.
			if ( ! empty( $gallery['ids'] ) ) {
				$gallery['include'] = $gallery['ids'];
			}

			$attachments = array_merge( $attachments, $this->get_gallery_attachments( $id, $gallery ) );
		}

		return array_unique( $attachments, SORT_REGULAR );
	}

	/**
	 * Retrieves galleries from the passed content.
	 * Forked from core to skip executing shortcodes for performance.
	 *
	 * @param string $content Content to parse for shortcodes.
	 *
	 * @return array A list of arrays, each containing gallery data.
	 */
	private function get_content_galleries( $content ) {
		if ( ! preg_match_all( '/' . get_shortcode_regex( [ 'gallery' ] ) . '/s', $content, $matches, PREG_SET_ORDER ) ) {
			return [];
		}

		$galleries = [];
		foreach ( $matches as $shortcode ) {
			if ( 'gallery' !== $shortcode[2] ) {
				continue;
			}

			$attributes  = shortcode_parse_atts( $shortcode[3] );
			$galleries[] = '' === $attributes ? [] : $attributes;
		}

		return $galleries;
	}

	/**
	 * Set image item array with filters applied.
	 *
	 * @param string $src Image URL.
	 */
	private function get_image_item( $src ) {
		$image = [];

		/**
		 * Filter image URL to be included in XML sitemap for the post.
		 *
		 * @param string $src  Image URL.
		 * @param object $post Post object.
		 */
		$image['src'] = $this->do_filter( 'sitemap/xml_img_src', $src, $this->post );
		if ( Str::is_empty( $image['src'] ) ) {
			return;
		}

		/**
		 * Filter image data to be included in XML sitemap for the post.
		 *
		 * @param array  $image Array of image data. {
		 *     @type string $src Image URL.
		 * }
		 * @param object $post  Post object.
		 */
		$this->images[] = $this->do_filter( 'sitemap/xml_img', $image, $this->post );
	}

	/**
	 * Get attached image URL with filters applied. Adapted from core for speed.
	 *
	 * @param int $post_id ID of the post.
	 *
	 * @return string
	 */
	private function image_url( $post_id ) {
		$src = $this->normalize_image_url( $post_id );

		return false === $src ? '' : apply_filters( 'wp_get_attachment_url', $src, $post_id ); // phpcs:ignore
	}

	/**
	 * Get attached image URL.
	 *
	 * @param int $post_id ID of the post.
	 *
	 * @return bool|string
	 */
	private function normalize_image_url( $post_id ) {
		$uploads    = $this->get_upload_dir();
		$attachment = get_post_meta( $post_id, '_wp_attached_file', true );

		if ( false !== $uploads['error'] || empty( $attachment ) ) {
			return false;
		}

		// Check that the upload base exists in the file location.
		if ( 0 === strpos( $attachment, $uploads['basedir'] ) ) {
			return str_replace( $uploads['basedir'], $uploads['baseurl'], $attachment );
		}

		if ( false !== strpos( $attachment, 'wp-content/uploads' ) ) {
			return $uploads['baseurl'] . substr( $attachment, ( strpos( $attachment, 'wp-content/uploads' ) + 18 ) );
		}

		// It's a newly uploaded file, therefore $attachment is relative to the baseurl.
		return $uploads['baseurl'] . '/' . $attachment;
	}

	/**
	 * Get WordPress upload directory.
	 *
	 * @return bool|array
	 */
	private function get_upload_dir() {
		static $rank_math_wp_uploads;

		if ( empty( $rank_math_wp_uploads ) ) {
			$rank_math_wp_uploads = wp_upload_dir();
		}

		return $rank_math_wp_uploads;
	}

	/**
	 * Make absolute URL for domain or protocol-relative one.
	 *
	 * @param string $src URL to process.
	 *
	 * @return string
	 */
	private function get_absolute_url( $src ) {
		if ( Str::is_empty( $src ) ) {
			return $src;
		}

		if ( true === Url::is_relative( $src ) ) {
			return '/' !== $src[0] ? $src :
				$this->home_url . $src; // The URL is relative, we'll have to make it absolute.
		}

		// If not starting with protocol, we add the scheme as the standard requires a protocol.
		return ! Str::starts_with( 'http', $src ) ? $this->scheme . ':' . $src : $src;
	}

	/**
	 * Returns the attachments for a gallery.
	 *
	 * @param int   $id      The post ID.
	 * @param array $gallery The gallery config.
	 *
	 * @return array The selected attachments.
	 */
	private function get_gallery_attachments( $id, $gallery ) {
		// When there are attachments to include.
		if ( ! empty( $gallery['include'] ) ) {
			return $this->get_gallery_attachments_for_included( $gallery['include'] );
		}

		return empty( $id ) ? [] : $this->get_gallery_attachments_for_parent( $id, $gallery );
	}

	/**
	 * Returns the attachments for the given ID.
	 *
	 * @param int   $id      The post ID.
	 * @param array $gallery The gallery config.
	 *
	 * @return array The selected attachments.
	 */
	private function get_gallery_attachments_for_parent( $id, $gallery ) {
		$query = [
			'posts_per_page' => -1,
			'post_parent'    => $id,
		];

		// When there are posts that should be excluded from result set.
		if ( ! empty( $gallery['exclude'] ) ) {
			$query['post__not_in'] = wp_parse_id_list( $gallery['exclude'] );
		}

		return $this->get_attachments( $query );
	}

	/**
	 * Returns an array with attachments for the post IDs that will be included.
	 *
	 * @param array $include Array with ids to include.
	 *
	 * @return array The found attachments.
	 */
	private function get_gallery_attachments_for_included( $include ) {
		$ids_to_include = wp_parse_id_list( $include );
		$attachments    = $this->get_attachments(
			[
				'posts_per_page' => count( $ids_to_include ),
				'post__in'       => $ids_to_include,
			]
		);

		$gallery_attachments = [];
		foreach ( $attachments as $val ) {
			$gallery_attachments[ $val->ID ] = $val;
		}

		return $gallery_attachments;
	}

	/**
	 * Returns the attachments.
	 *
	 * @param array $args Array with query args.
	 *
	 * @return array The found attachments.
	 */
	protected function get_attachments( $args ) {
		$default_args = [
			'post_status'         => 'inherit',
			'post_type'           => 'attachment',
			'post_mime_type'      => 'image',

			// Defaults taken from function get_posts.
			'orderby'             => 'date',
			'order'               => 'DESC',
			'meta_key'            => '',
			'meta_value'          => '',
			'suppress_filters'    => true,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		];

		$args = wp_parse_args( $args, $default_args );

		$get_attachments = new WP_Query();
		return $get_attachments->query( $args );
	}
}
