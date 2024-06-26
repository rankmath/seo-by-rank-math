<?php
/**
 * This code adds the OpenGraph Image parser.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\OpenGraph
 * @author     Rank Math <support@rankmath.com>
 *
 * @copyright Copyright (C) 2008-2019, Yoast BV
 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
 */

namespace RankMath\OpenGraph;

use RankMath\Helper;
use RankMath\Post;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Str;
use RankMath\Helpers\Url;
use RankMath\Helpers\Attachment;

defined( 'ABSPATH' ) || exit;

/**
 * Image class.
 */
class Image {

	use Hooker;

	/**
	 * Holds network slug.
	 *
	 * @var array
	 */
	private $network;

	/**
	 * Holds the images that have been put out as OG image.
	 *
	 * @var array
	 */
	private $images = [];

	/**
	 * Holds the OpenGraph instance.
	 *
	 * @var OpenGraph
	 */
	private $opengraph;

	/**
	 * The parameters we have for Facebook images.
	 *
	 * @var array
	 */
	private $usable_dimensions = [
		'min_width'  => 200,
		'max_width'  => 2000,
		'min_height' => 200,
		'max_height' => 2000,
	];

	/**
	 * The Constructor.
	 *
	 * @param string    $image     (Optional) The image URL.
	 * @param OpenGraph $opengraph (Optional) The OpenGraph object..
	 */
	public function __construct( $image = false, $opengraph = null ) {
		$this->opengraph = $opengraph;
		$this->network   = $opengraph->network;

		// If an image was not supplied or could not be added.
		if ( Str::is_non_empty( $image ) ) {
			$this->add_image_by_url( $image );
		}

		if ( ! post_password_required() ) {
			$this->set_images();
		}
	}

	/**
	 * Outputs the images.
	 */
	public function show() {
		foreach ( $this->get_images() as $image => $image_meta ) {
			$this->image_tag( $image_meta );
			$this->image_meta( $image_meta );
		}
	}

	/**
	 * Return the images array.
	 *
	 * @return array
	 */
	public function get_images() {
		return $this->images;
	}

	/**
	 * Check whether we have images or not.
	 *
	 * @return bool
	 */
	public function has_images() {
		return ! empty( $this->images );
	}

	/**
	 * Generate secret key for safer image URLs.
	 *
	 * @param int    $id   The attachment ID.
	 * @param string $type Overlay type.
	 */
	public function generate_secret( $id, $type ) {
		return md5( $id . $type . wp_salt( 'nonce' ) );
	}

	/**
	 * Outputs an image tag based on whether it's https or not.
	 *
	 * @param array $image_meta Image metadata.
	 */
	private function image_tag( $image_meta ) {
		$overlay  = $this->opengraph->get_overlay_image();
		$og_image = $image_meta['url'];
		if ( $overlay && ! empty( $image_meta['id'] ) ) {
			$secret   = $this->generate_secret( $image_meta['id'], $overlay );
			$og_image = admin_url( "admin-ajax.php?action=rank_math_overlay_thumb&id={$image_meta['id']}&type={$overlay}&hash={$secret}" );
		}
		$this->opengraph->tag( 'og:image', esc_url_raw( $og_image ) );

		// Add secure URL if detected. Not all services implement this, so the regular one also needs to be rendered.
		if ( Str::starts_with( 'https://', $og_image ) ) {
			$this->opengraph->tag( 'og:image:secure_url', esc_url_raw( $og_image ) );
		}
	}

	/**
	 * Output the image metadata.
	 *
	 * @param array $image_meta Image meta data to output.
	 */
	private function image_meta( $image_meta ) {
		$image_tags = [ 'width', 'height', 'alt', 'type' ];
		foreach ( $image_tags as $key ) {
			if ( ! empty( $image_meta[ $key ] ) ) {
				$this->opengraph->tag( 'og:image:' . $key, $image_meta[ $key ] );
			}
		}
	}

	/**
	 * Adds an image based on a given URL, and attempts to be smart about it.
	 *
	 * @param string $url The given URL.
	 */
	public function add_image_by_url( $url ) {
		if ( empty( $url ) ) {
			return;
		}

		$attachment_id = Attachment::get_by_url( $url );

		if ( $attachment_id > 0 ) {
			$this->add_image_by_id( $attachment_id );
			return;
		}

		$this->add_image( [ 'url' => $url ] );
	}

	/**
	 * Adds an image to the list by attachment ID.
	 *
	 * @param int $attachment_id The attachment ID to add.
	 */
	public function add_image_by_id( $attachment_id ) {
		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			return;
		}

		$variations = $this->get_variations( $attachment_id );

		// If we are left without variations, there is no valid variation for this attachment.
		if ( empty( $variations ) ) {
			return;
		}

		// The variations are ordered so the first variations is by definition the best one.
		$attachment = $variations[0];

		if ( $attachment ) {
			// In the past `add_image` accepted an image url, so leave this for backwards compatibility.
			if ( Str::is_non_empty( $attachment ) ) {
				$attachment = [ 'url' => $attachment ];
			}
			$attachment['alt'] = Attachment::get_alt_tag( $attachment_id );

			$this->add_image( $attachment );
		}
	}

	/**
	 * Display an OpenGraph image tag.
	 *
	 * @param string $attachment Source URL to the image.
	 */
	public function add_image( $attachment = '' ) {
		// In the past `add_image` accepted an image url, so leave this for backwards compatibility.
		if ( Str::is_non_empty( $attachment ) ) {
			$attachment = [ 'url' => $attachment ];
		}

		$validate_image = true;
		/**
		 * Allow changing the OpenGraph image.
		 * The dynamic part of the hook name, $this->network, is the network slug (facebook, twitter).
		 *
		 * @param string $img The image we are about to add.
		 */
		$filter_image_url = trim( $this->do_filter( "opengraph/{$this->network}/image", isset( $attachment['url'] ) ? $attachment['url'] : '' ) );
		if ( ! empty( $filter_image_url ) && ( empty( $attachment['url'] ) || $filter_image_url !== $attachment['url'] ) ) {
			$attachment     = [ 'url' => $filter_image_url ];
			$validate_image = false;
		}

		/**
		 * Secondary filter to allow changing the whole array.
		 * The dynamic part of the hook name, $this->network, is the network slug (facebook, twitter).
		 * This makes it possible to change the image ID too, to allow for image overlays.
		 *
		 * @param array $attachment The image we are about to add.
		 */
		$attachment = $this->do_filter( "opengraph/{$this->network}/image_array", $attachment );

		if ( ! is_array( $attachment ) || empty( $attachment['url'] ) ) {
			return;
		}

		// Validate image only when it is not added using the opengraph filter.
		if ( $validate_image ) {
			$attachment_url = explode( '?', $attachment['url'] );
			if ( ! empty( $attachment_url ) ) {
				$attachment['url'] = $attachment_url[0];
			}

			// If the URL ends in `.svg`, we need to return.
			if ( ! $this->is_valid_image_url( $attachment['url'] ) ) {
				return;
			}
		}

		$image_url = $attachment['url'];
		if ( empty( $image_url ) ) {
			return;
		}

		if ( Url::is_relative( $image_url ) ) {
			$image_url = Attachment::get_relative_path( $image_url );
		}

		if ( array_key_exists( $image_url, $this->images ) ) {
			return;
		}

		$attachment['url'] = $image_url;

		if ( empty( $attachment['alt'] ) && is_singular() ) {
			$attachment['alt'] = $this->get_attachment_alt();
		}

		$this->images[ $image_url ] = $attachment;
	}

	/**
	 * Get attachment alt with fallback
	 *
	 * @return string
	 */
	private function get_attachment_alt() {
		global $post;

		$focus_keywords = Helper::get_post_meta( 'focus_keyword', $post->ID );
		if ( ! empty( $focus_keywords ) ) {
			$focus_keywords = explode( ',', $focus_keywords );
			return $focus_keywords[0];
		}

		return get_the_title();
	}

	/**
	 * Check if page is front page or singular and call the corresponding functions.
	 */
	private function set_images() {
		/**
		 * Allow developers to add images to the OpenGraph tags.
		 *
		 * The dynamic part of the hook name. $this->network, is the network slug.
		 *
		 * @param Image The current object.
		 */
		$this->do_action( "opengraph/{$this->network}/add_images", $this );

		switch ( true ) {
			case is_front_page():
				$this->set_front_page_image();
				break;
			case is_home():
				$this->set_posts_page_image();
				break;
			case is_attachment():
				$this->set_attachment_page_image();
				break;
			case is_singular() || Post::is_shop_page():
				$this->set_singular_image();
				break;
			case is_post_type_archive():
				$this->set_archive_image();
				break;
			case is_category():
			case is_tag():
			case is_tax():
				$this->set_taxonomy_image();
				break;
			case is_author():
				$this->set_author_image();
				break;
		}

		/**
		 * Allow developers to add images to the OpenGraph tags.
		 *
		 * The dynamic part of the hook name. $this->network, is the network slug.
		 *
		 * @param Image The current object.
		 */
		$this->do_action( "opengraph/{$this->network}/add_additional_images", $this );

		/**
		 * Passing a truthy value to the filter will effectively short-circuit the
		 * set default image process.
		 *
		 * @param bool $return Short-circuit return value. Either false or true.
		 */
		if ( false !== $this->do_filter( 'opengraph/pre_set_default_image', false ) ) {
			return;
		}

		// If not, get default image.
		$image_id = Helper::get_settings( 'titles.open_graph_image_id' );
		if ( ! $this->has_images() ) {
			if ( $image_id > 0 ) {
				$this->add_image_by_id( $image_id );
				return;
			}

			$this->add_image(); // This allows "opengraph/{$this->network}/image" filter to be used even if no image is set.
		}
	}

	/**
	 * If the frontpage image exists, call `add_image`.
	 *
	 * @return void
	 */
	private function set_front_page_image() {
		$this->set_user_defined_image();

		if ( $this->has_images() ) {
			return;
		}

		// If no frontpage image is found, don't add anything.
		if ( $image_id = Helper::get_settings( 'titles.homepage_facebook_image_id' ) ) { // phpcs:ignore
			$this->add_image_by_id( $image_id );
		}
	}

	/**
	 * Gets the user-defined image of the post.
	 *
	 * @param null|int $post_id The post ID to get the images for.
	 */
	private function set_user_defined_image( $post_id = null ) {
		if ( null === $post_id ) {
			$post_id = get_queried_object_id();
		}

		$this->set_image_post_meta( $post_id );

		if ( $this->has_images() ) {
			return;
		}

		$this->set_featured_image( $post_id );
	}

	/**
	 * If opengraph-image is set, call `add_image` and return true.
	 *
	 * @param int $post_id Optional post ID to use.
	 */
	private function set_image_post_meta( $post_id = 0 ) {
		if ( empty( $post_id ) ) {
			return;
		}
		$image_id = Helper::get_post_meta( "{$this->opengraph->prefix}_image_id", $post_id );
		$this->add_image_by_id( $image_id );
	}

	/**
	 * Retrieve the featured image.
	 *
	 * @param int $post_id The post ID.
	 */
	private function set_featured_image( $post_id = null ) {
		/**
		 * Passing a truthy value to the filter will effectively short-circuit the
		 * set featured image process.
		 *
		 * @param bool $return  Short-circuit return value. Either false or true.
		 * @param int  $post_id Post ID for the current post.
		 */
		if ( false !== $this->do_filter( 'opengraph/pre_set_featured_image', false, $post_id ) ) {
			return;
		}

		if ( $post_id && has_post_thumbnail( $post_id ) ) {
			$attachment_id = get_post_thumbnail_id( $post_id );
			$this->add_image_by_id( $attachment_id );
		}
	}

	/**
	 * Get the images of the posts page.
	 */
	private function set_posts_page_image() {
		$post_id = get_option( 'page_for_posts' );

		$this->set_image_post_meta( $post_id );

		if ( $this->has_images() ) {
			return;
		}

		$this->set_featured_image( $post_id );
	}

	/**
	 * If this is an attachment page, call `add_image` with the attachment.
	 */
	private function set_attachment_page_image() {
		$post_id = get_queried_object_id();
		if ( wp_attachment_is_image( $post_id ) ) {
			$this->add_image_by_id( $post_id );
		}
	}

	/**
	 * Get the images of the singular post.
	 *
	 * @param null|int $post_id The post ID to get the images for.
	 */
	private function set_singular_image( $post_id = null ) {
		$is_shop_page = Post::is_shop_page();
		if ( $is_shop_page ) {
			$post_id = Post::get_shop_page_id();
		}

		$post_id = is_null( $post_id ) ? get_queried_object_id() : $post_id;
		$this->set_user_defined_image( $post_id );

		if ( $this->has_images() ) {
			return;
		}

		/**
		 * Passing a truthy value to the filter will effectively short-circuit the
		 * set content image process.
		 *
		 * @param bool $return  Short-circuit return value. Either false or true.
		 * @param int  $post_id Post ID for the current post.
		 */
		if ( false !== $this->do_filter( 'opengraph/pre_set_content_image', false, $post_id ) ) {
			if ( $is_shop_page ) {
				$this->set_archive_image();
			}

			return;
		}
		$this->set_content_image( get_post( $post_id ) );

		if ( ! $this->has_images() && $is_shop_page ) {
			$this->set_archive_image();
		}
	}

	/**
	 * Adds the first usable attachment image from the post content.
	 *
	 * @param object $post The post object.
	 */
	private function set_content_image( $post ) {
		$content = sanitize_post_field( 'post_content', $post->post_content, $post->ID );

		// Early bail!
		if ( '' === $content || false === Str::contains( '<img', $content ) ) {
			return;
		}

		$do_og_content_image_cache = $this->do_filter( 'opengraph/content_image_cache', true );
		if ( $do_og_content_image_cache ) {
			$cache_key = 'rank_math_og_content_image';
			$cache     = get_post_meta( $post->ID, $cache_key, true );
			$check     = md5( $post->post_content );
			if ( ! empty( $cache ) && isset( $cache['check'] ) && $check === $cache['check'] ) {
				foreach ( $cache['images'] as $image ) {
					if ( is_int( $image ) ) {
						$this->add_image_by_id( $image );
					} else {
						$this->add_image( $image );
					}
				}
				return;
			}

			$cache = [
				'check'  => $check,
				'images' => [],
			];
		}

		$images = [];
		if ( preg_match_all( '`<img [^>]+>`', $content, $matches ) ) {
			foreach ( $matches[0] as $img ) {
				if ( preg_match( '`src=(["\'])(.*?)\1`', $img, $match ) ) {
					if ( isset( $match[2] ) ) {
						$images[] = $match[2];
					}
				}
			}
		}

		$images = array_unique( $images );
		if ( empty( $images ) ) {
			return;
		}

		foreach ( $images as $image ) {

			// If an image has been added, we're done.
			if ( $this->has_images() ) {
				break;
			}

			if ( Url::is_external( $image ) ) {
				$this->add_image( $image );
			} else {
				$attachment_id = Attachment::get_by_url( $image );
				if ( 0 === $attachment_id ) {
					$this->add_image( $image );
					if ( $do_og_content_image_cache ) {
						$cache['images'][] = $image;
					}
				} else {
					$this->add_image_by_id( $attachment_id );
					if ( $do_og_content_image_cache ) {
						$cache['images'][] = $attachment_id;
					}
				}
			}
		}

		if ( $do_og_content_image_cache ) {
			update_post_meta( $post->ID, $cache_key, $cache );
		}
	}

	/**
	 * Check if Author has an image and add this image.
	 */
	private function set_author_image() {
		$image_id = Helper::get_user_meta( "{$this->opengraph->prefix}_image_id" );
		$this->add_image_by_id( $image_id );
	}

	/**
	 * Check if taxonomy has an image and add this image.
	 */
	private function set_taxonomy_image() {
		$image_id = Helper::get_term_meta( "{$this->opengraph->prefix}_image_id" );
		$this->add_image_by_id( $image_id );
	}

	/**
	 * Check if archive has an image and add this image.
	 */
	private function set_archive_image() {
		$post_type = get_query_var( 'post_type' );
		$image_id  = Helper::get_settings( "titles.pt_{$post_type}_facebook_image_id" );
		$this->add_image_by_id( $image_id );
	}

	/**
	 * Determines whether the passed URL is considered valid.
	 *
	 * @param string $url The URL to check.
	 *
	 * @return bool Whether or not the URL is a valid image.
	 */
	protected function is_valid_image_url( $url ) {
		if ( ! is_string( $url ) ) {
			return false;
		}

		$check = wp_check_filetype( $url );
		if ( empty( $check['ext'] ) ) {
			return false;
		}

		$extensions = [ 'jpeg', 'jpg', 'gif', 'png', 'webp', 'avif' ];

		return in_array( $check['ext'], $extensions, true );
	}

	/**
	 * Returns the different image variations for consideration.
	 *
	 * @param int $attachment_id The attachment to return the variations for.
	 *
	 * @return array The different variations possible for this attachment ID.
	 */
	public function get_variations( $attachment_id ) {
		$variations = [];

		/**
		 * Determines which image sizes we'll loop through to get an appropriate image.
		 *
		 * @param unsigned array - The array of image sizes to loop through.
		 */
		$sizes = $this->do_filter( 'opengraph/image_sizes', [ 'full', 'large', 'medium_large' ] );

		foreach ( $sizes as $size ) {
			if ( $variation = $this->get_attachment_image( $attachment_id, $size ) ) { // phpcs:ignore
				if ( $this->has_usable_dimensions( $variation ) ) {
					$variations[] = $variation;
				}
			}
		}

		return $variations;
	}

	/**
	 * Retrieve an image to represent an attachment.
	 *
	 * @param int          $attachment_id Image attachment ID.
	 * @param string|array $size          Optional. Image size. Accepts any valid image size, or an array of width
	 *                                    and height values in pixels (in that order). Default 'thumbnail'.
	 * @return false|array
	 */
	private function get_attachment_image( $attachment_id, $size = 'thumbnail' ) {
		$image = wp_get_attachment_image_src( $attachment_id, $size );

		// Early Bail!
		if ( ! $image ) {
			return false;
		}

		list( $src, $width, $height ) = $image;

		return [
			'id'     => $attachment_id,
			'url'    => $src,
			'width'  => $width,
			'height' => $height,
			'type'   => get_post_mime_type( $attachment_id ),
			'alt'    => Attachment::get_alt_tag( $attachment_id ),
		];
	}

	/**
	 * Checks whether an img sizes up to the parameters.
	 *
	 * @param  array $dimensions The image values.
	 * @return bool True if the image has usable measurements, false if not.
	 */
	private function has_usable_dimensions( $dimensions ) {
		foreach ( [ 'width', 'height' ] as $param ) {
			$minimum = $this->usable_dimensions[ 'min_' . $param ];
			$maximum = $this->usable_dimensions[ 'max_' . $param ];

			$current = $dimensions[ $param ];
			if ( ( $current < $minimum ) || ( $current > $maximum ) ) {
				return false;
			}
		}

		return true;
	}
}
