<?php
/**
 * This code adds the Facebook metadata.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\OpenGraph
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\OpenGraph;

use DateInterval;
use RankMath\Helper;
use RankMath\Paper\Paper;
use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * Facebook class.
 */
class Facebook extends OpenGraph {

	/**
	 * Network slug.
	 *
	 * @var string
	 */
	public $network = 'facebook';

	/**
	 * Metakey prefix.
	 *
	 * @var string
	 */
	public $prefix = 'facebook';

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->hooks();
		add_filter( 'jetpack_enable_open_graph', '__return_false' );
		parent::__construct();
	}

	/**
	 * Hooks
	 */
	private function hooks() {
		if ( isset( $GLOBALS['fb_ver'] ) || class_exists( 'Facebook_Loader', false ) ) {
			$this->filter( 'fb_meta_tags', 'facebook_filter', 10, 1 );
			return;
		}

		$this->filter( 'language_attributes', 'add_namespace', 15 );
		$this->action( 'rank_math/opengraph/facebook', 'locale', 1 );
		$this->action( 'rank_math/opengraph/facebook', 'type', 5 );
		$this->action( 'rank_math/opengraph/facebook', 'title', 10 );
		$this->action( 'rank_math/opengraph/facebook', 'description', 11 );
		$this->action( 'rank_math/opengraph/facebook', 'url', 12 );
		$this->action( 'rank_math/opengraph/facebook', 'site_name', 13 );
		$this->action( 'rank_math/opengraph/facebook', 'website', 14 );
		$this->action( 'rank_math/opengraph/facebook', 'site_owner', 20 );
		$this->action( 'rank_math/opengraph/facebook', 'image', 30 );

	}

	/**
	 * Filter the Facebook plugins metadata.
	 *
	 * @param  array $meta_tags The array to fix.
	 * @return array
	 */
	public function facebook_filter( $meta_tags ) {
		$meta_tags['http://ogp.me/ns#type']  = $this->type( false );
		$meta_tags['http://ogp.me/ns#title'] = $this->title( false );

		// Filter the locale too because the Facebook plugin locale code is not as good as ours.
		$meta_tags['http://ogp.me/ns#locale'] = $this->locale( false );

		$desc = $this->description( false );
		if ( ! empty( $desc ) ) {
			$meta_tags['http://ogp.me/ns#description'] = $desc;
		}

		return $meta_tags;
	}

	/**
	 * Adds prefix attributes to the <html> tag.
	 *
	 * @param  string $input The input namespace string.
	 * @return string
	 */
	public function add_namespace( $input ) {
		return $input . ' prefix="og: http://ogp.me/ns#"';
	}

	/**
	 * Output the locale, doing some conversions to make sure the proper Facebook locale is outputted.
	 *
	 * @see  http://www.facebook.com/translations/FacebookLocales.xml for the list of supported locales
	 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/article/
	 *
	 * @param bool $echo Whether to echo or return the locale.
	 * @return string
	 */
	public function locale( $echo = true ) {
		$locale = get_locale();
		$locale = Facebook_Locale::sanitize( $locale );
		$locale = Facebook_Locale::validate( $locale );

		if ( $echo ) {
			$this->tag( 'og:locale', $locale );
		}

		return $locale;
	}

	/**
	 * Output the OpenGraph type.
	 *
	 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/object/
	 *
	 * @param bool $echo Whether to echo or return the type.
	 * @return string
	 */
	public function type( $echo = true ) {
		$type = $this->get_type();

		if ( is_singular() ) {
			if ( 'article' === $type && ! is_front_page() ) {
				$this->action( 'rank_math/opengraph/facebook', 'article_author', 15 );
				$this->action( 'rank_math/opengraph/facebook', 'tags', 16 );
				$this->action( 'rank_math/opengraph/facebook', 'category', 17 );
			}
			$this->action( 'rank_math/opengraph/facebook', 'publish_date', 19 );
		}

		/**
		 * Allow changing the OpenGraph type of the page.
		 *
		 * @param string $type The OpenGraph type string.
		 */
		$type = $this->do_filter( 'opengraph/type', $type );

		if ( Str::is_non_empty( $type ) && $echo ) {
			$this->tag( 'og:type', $type );
		}

		return $type;
	}

	/**
	 * Get type.
	 *
	 * @return string
	 */
	private function get_type() {
		if ( is_front_page() || is_home() ) {
			return 'website';
		}

		// We use "object" for archives etc. as article doesn't apply there.
		if ( ! is_singular() ) {
			return 'object';
		}

		if ( in_array( $this->schema, [ 'video', 'product', 'local' ], true ) ) {
			if ( ! is_front_page() ) {
				$this->action( 'rank_math/opengraph/facebook', $this->schema, 30 );
			}
			return $this->schema;
		}

		return $this->is_product() ? 'product' : 'article';
	}

	/**
	 * Outputs the SEO title as OpenGraph title.
	 *
	 * @copyright Copyright (C) 2008-2019, Yoast BV
	 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
	 *
	 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/article/
	 *
	 * @param bool $echo Whether or not to echo the output.
	 *
	 * @return string
	 */
	public function title( $echo = true ) {
		$title = trim( $this->get_title() );
		if ( $echo ) {
			$this->tag( 'og:title', $title );
		}

		return $title;
	}

	/**
	 * Output the OpenGraph description, specific OG description first, if not, grab the meta description.
	 *
	 * @param bool $echo Whether to echo or return the description.
	 * @return string
	 */
	public function description( $echo = true ) {
		$desc = trim( $this->get_description() );
		if ( $echo ) {
			$this->tag( 'og:description', $desc );
		}

		return $desc;
	}

	/**
	 * Outputs the canonical URL as OpenGraph URL, which consolidates likes and shares.
	 *
	 * @copyright Copyright (C) 2008-2019, Yoast BV
	 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
	 *
	 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/article/
	 */
	public function url() {
		$url = $this->do_filter( 'opengraph/url', esc_url( Paper::get()->get_canonical() ) );
		$this->tag( 'og:url', $url );
	}

	/**
	 * Output the site name straight from the blog info.
	 */
	public function site_name() {
		$this->tag( 'og:site_name', get_bloginfo( 'name' ) );
	}

	/**
	 * Outputs the websites FB page.
	 *
	 * @link https://developers.facebook.com/blog/post/2013/06/19/platform-updates--new-open-graph-tags-for-media-publishers-and-more/
	 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/article/
	 */
	public function website() {
		$site = Helper::get_settings( 'titles.social_url_facebook' );
		if ( 'article' === $this->type( false ) && '' !== $site ) {
			$this->tag( 'article:publisher', $site );
		}
	}

	/**
	 * Outputs the site owner.
	 *
	 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/article/
	 */
	public function site_owner() {
		$app_id = Helper::get_settings( 'titles.facebook_app_id' );
		if ( 0 !== absint( $app_id ) ) {
			$this->tag( 'fb:app_id', $app_id );
			return;
		}

		$admins = Helper::get_settings( 'titles.facebook_admin_id' );
		if ( '' !== trim( $admins ) ) {
			$this->tag( 'fb:admins', $admins );
			return;
		}
	}

	/**
	 * Create new Image class and get the images to set the `og:image`.
	 *
	 * @param string|bool $image Optional. Image URL.
	 */
	public function image( $image = false ) {
		$images = new Image( $image, $this );
		$images->show();
	}

	/**
	 * Outputs the authors FB page.
	 *
	 * @link https://developers.facebook.com/blog/post/2013/06/19/platform-updates--new-open-graph-tags-for-media-publishers-and-more/
	 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/article/
	 */
	public function article_author() {
		$this->tag( 'article:author', $this->get_author() );
	}

	/**
	 * Output the article tags as `article:tag` tags.
	 */
	public function tags() {
		$tags = get_the_tags();
		if ( is_wp_error( $tags ) || empty( $tags ) ) {
			return;
		}

		foreach ( $tags as $tag ) {
			$this->tag( 'article:tag', $tag->name );
		}
	}

	/**
	 * Output the article category as an `article:section` tag.
	 */
	public function category() {
		$terms = get_the_category();
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return;
		}

		// We can only show one section here, so we take the first one.
		$this->tag( 'article:section', $terms[0]->name );
	}

	/**
	 * Output the article publish and last modification date.
	 */
	public function publish_date() {
		$post = get_post();
		$pub  = mysql2date( DATE_W3C, $post->post_date, false );
		$mod  = mysql2date( DATE_W3C, $post->post_modified, false );

		if ( 'article' === $this->schema ) {
			$this->tag( 'article:published_time', $pub );
			if ( $mod !== $pub ) {
				$this->tag( 'article:modified_time', $mod );
			}
		}
		if ( $mod !== $pub ) {
			$this->tag( 'og:updated_time', $mod );
		}
	}

	/**
	 * Output product tags.
	 */
	public function product() {
		if ( $this->is_product() ) {
			return;
		}

		$this->tag( 'product:brand', Helper::get_post_meta( 'snippet_product_brand' ) );
		$this->tag( 'product:price:amount', Helper::get_post_meta( 'snippet_product_price' ) );
		$this->tag( 'product:price:currency', Helper::get_post_meta( 'snippet_product_currency' ) );
		if ( Helper::get_post_meta( 'snippet_product_instock' ) ) {
			$this->tag( 'product:availability', 'instock' );
		}
	}

	/**
	 * Output local info.
	 */
	public function local() {
		$this->tag( 'og:url', Helper::get_post_meta( 'snippet_local_url' ) );

		if ( $geo = Helper::get_post_meta( 'snippet_local_geo' ) ) { // phpcs:ignore
			$parts = explode( ' ', $geo );
			if ( count( $parts ) > 1 ) {
				$this->tag( 'place:location:latitude', $parts[0] );
				$this->tag( 'place:location:longitude', $parts[1] );
			}
		}
	}

	/**
	 * Output video tags.
	 */
	public function video() {
		$this->tag( 'og:video', Helper::get_post_meta( 'snippet_video_url' ) );
		if ( $duration = Helper::get_formatted_duration( Helper::get_post_meta( 'snippet_video_duration' ) ) ) { // phpcs:ignore
			$this->tag( 'video:duration', $this->duration_to_seconds( $duration ) );
		}
	}

	/**
	 * Helper function to convert ISO 8601 duration to seconds.
	 * For example "1H12M24S" becomes 5064.
	 *
	 * @param string $iso8601 Duration which need to be converted to seconds.
	 * @return int
	 */
	private function duration_to_seconds( $iso8601 ) {
		$interval = new DateInterval( $iso8601 );

		return array_sum(
			[
				$interval->d * DAY_IN_SECONDS,
				$interval->h * HOUR_IN_SECONDS,
				$interval->i * MINUTE_IN_SECONDS,
				$interval->s,
			]
		);
	}

	/**
	 * Get author.
	 *
	 * @return string
	 */
	private function get_author() {
		$author = Helper::get_user_meta( 'facebook_author', $GLOBALS['post']->post_author );
		if ( $author ) {
			return $author;
		}

		$author = get_user_meta( $GLOBALS['post']->post_author, 'facebook', true );
		if ( $author ) {
			return $author;
		}

		return Helper::get_settings( 'titles.facebook_author_urls' );
	}

	/**
	 * Is WooCommerce product
	 *
	 * @return bool
	 */
	private function is_product() {
		return function_exists( 'is_woocommerce' ) && is_product();
	}
}
