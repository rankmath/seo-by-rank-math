<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Frontend
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Frontend;

use RankMath\Post;
use RankMath\Helper;
use RankMath\Paper\Paper;
use RankMath\Traits\Hooker;
use RankMath\OpenGraph\Twitter;
use RankMath\OpenGraph\Facebook;
use RankMath\OpenGraph\Slack;
use RankMath\Frontend\Shortcodes;

defined( 'ABSPATH' ) || exit;

/**
 * Frontend class.
 *
 * @copyright Copyright (C) 2008-2019, Yoast BV
 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
 */
class Frontend {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		if ( \MyThemeShop\Helpers\Param::get( 'et_fb' ) ) {
			return;
		}

		$this->includes();
		$this->hooks();

		/**
		 * Fires when frontend is included/loaded.
		 */
		$this->do_action( 'frontend/loaded' );
	}

	/**
	 * Include required files.
	 */
	private function includes() {

		rank_math()->shortcodes = new Shortcodes();

		if ( Helper::is_breadcrumbs_enabled() ) {
			/**
			 * If RM's breadcrumbs are enabled then we can remove the bbPress breadcrumbs.
			 */
			add_filter( 'bbp_get_breadcrumb', '__return_false' );
		}

		rank_math()->link_attributes = new Link_Attributes();
		rank_math()->comments        = new Comments();
	}

	/**
	 * Hook into actions and filters.
	 */
	private function hooks() {

		$this->action( 'wp_enqueue_scripts', 'enqueue' );
		$this->action( 'wp', 'integrations' );
		$this->filter( 'the_content_feed', 'embed_rssfooter' );
		$this->filter( 'the_excerpt_rss', 'embed_rssfooter_excerpt' );

		// Redirect attachment page to parent post.
		if ( Helper::get_settings( 'general.attachment_redirect_urls', true ) ) {
			$this->action( 'wp', 'attachment_redirect_urls' );
		}

		// Redirect archives.
		if ( Helper::get_settings( 'titles.disable_author_archives' ) || Helper::get_settings( 'titles.disable_date_archives' ) ) {
			$this->action( 'wp', 'archive_redirect' );
		}

		// Add support for shortcode in the Category/Term description.
		add_filter( 'category_description', 'do_shortcode' );
		add_filter( 'term_description', 'do_shortcode' );
	}

	/**
	 * Initialize integrations.
	 */
	public function integrations() {
		$type = get_query_var( 'sitemap' );
		if ( $this->do_filter( 'frontend/disable_integration', ! empty( $type ) || is_customize_preview() ) ) {
			return;
		}

		Paper::get();
		new Facebook();
		new Twitter();
		new Slack();

		// Leave this for backwards compatibility as AMP plugin uses head function. We can remove this in the future update.
		rank_math()->head = new Head();

		if ( function_exists( 'amp_is_dev_mode' ) && amp_is_dev_mode() ) {
			$this->filter( 'script_loader_tag', 'add_amp_dev_mode_attributes', 10, 2 );
			$this->filter( 'amp_dev_mode_element_xpaths', 'add_amp_dev_mode_xpaths' );
		}
	}

	/**
	 * Enqueue Styles and Scripts
	 */
	public function enqueue() {
		if ( ! is_admin_bar_showing() || ! Helper::has_cap( 'admin_bar' ) ) {
			return;
		}

		wp_enqueue_style( 'rank-math', rank_math()->assets() . 'css/rank-math.css', null, rank_math()->version );
		wp_enqueue_script( 'rank-math', rank_math()->assets() . 'js/rank-math.js', [ 'jquery' ], rank_math()->version, true );

		if ( is_singular() ) {
			Helper::add_json( 'objectID', Post::get_page_id() );
			Helper::add_json( 'objectType', 'post' );
		} elseif ( is_category() || is_tag() || is_tax() ) {
			Helper::add_json( 'objectID', get_queried_object_id() );
			Helper::add_json( 'objectType', 'term' );
		} elseif ( is_author() ) {
			Helper::add_json( 'objectID', get_queried_object_id() );
			Helper::add_json( 'objectType', 'user' );
		}
	}

	/**
	 * Redirects attachment to its parent post if it has one.
	 */
	public function attachment_redirect_urls() {
		global $post;

		// Early bail.
		if ( ! is_attachment() ) {
			return;
		}

		$redirect = ! empty( $post->post_parent ) ? get_permalink( $post->post_parent ) : Helper::get_settings( 'general.attachment_redirect_default' );

		/**
		 * Redirect atachment to its parent post.
		 *
		 * @param string  $redirect URL as calculated for redirection.
		 * @param WP_Post $post     Current post instance.
		 */
		Helper::redirect( $this->do_filter( 'frontend/attachment/redirect_url', $redirect, $post ), 301 );
		exit;
	}

	/**
	 * Redirect date & author archives if the setting is enabled.
	 */
	public function archive_redirect() {
		global $wp_query;

		if (
			( Helper::get_settings( 'titles.disable_date_archives' ) && $wp_query->is_date ) ||
			( true === Helper::get_settings( 'titles.disable_author_archives' ) && $wp_query->is_author )
		) {
			Helper::redirect( get_bloginfo( 'url' ), 301 );
			exit;
		}
	}

	/**
	 * Adds the RSS header and footer messages to the RSS feed item content.
	 *
	 * @param string $content Feed item content.
	 *
	 * @return string
	 */
	public function embed_rssfooter( $content ) {
		return $this->embed_rss( $content, 'full' );
	}

	/**
	 * Adds the RSS header and footer messages to the RSS feed item excerpt.
	 *
	 * @param string $content Feed item excerpt.
	 *
	 * @return string
	 */
	public function embed_rssfooter_excerpt( $content ) {
		return $this->embed_rss( $content, 'excerpt' );
	}

	/**
	 * Add data-ampdevmode attribute to enqueued scripts.
	 *
	 * @since 1.0.45
	 *
	 * @param string $tag    The script tag.
	 * @param string $handle The script handle.
	 *
	 * @return string Modified script tag.
	 */
	public function add_amp_dev_mode_attributes( $tag, $handle ) {
		if ( ! in_array( $handle, [ 'rank-math', 'jquery-core', 'jquery-migrate' ], true ) ) {
			return $tag;
		}

		return preg_replace( '/(?<=<script)(?=\s|>)/i', ' data-ampdevmode', $tag );
	}

	/**
	 * Add data-ampdevmode attributes to the elements that need it.
	 *
	 * @since 1.0.45
	 *
	 * @param string[] $xpaths XPath queries for elements that should get the data-ampdevmode attribute.
	 *
	 * @return string[] XPath queries.
	 */
	public function add_amp_dev_mode_xpaths( $xpaths ) {
		$xpaths[] = '//script[ contains( text(), "var rankMath" ) ]';
		$xpaths[] = '//*[ @id = "rank-math-css" ]';
		$xpaths[] = '//a[starts-with(@href, "tel://")]';
		return $xpaths;
	}

	/**
	 * Inserts the RSS header and footer messages in the RSS feed item.
	 *
	 * @param string $content Feed item content.
	 * @param string $context Feed item context, 'excerpt' or 'full'.
	 *
	 * @return string
	 */
	private function embed_rss( $content, $context = 'full' ) {
		if ( false === $this->can_embed_footer( $content, $context ) ) {
			return $content;
		}

		$before = $this->get_rss_content( 'before' );
		$after  = $this->get_rss_content( 'after' );

		if ( '' === $before && '' === $after ) {
			return $content;
		}

		if ( 'excerpt' === $context && '' !== trim( $content ) ) {
			$content = wpautop( $content );
		}

		return $before . $content . $after;
	}

	/**
	 * Check if we can add the RSS footer and/or header to the RSS feed item.
	 *
	 * @copyright Copyright (C) 2008-2019, Yoast BV
	 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
	 *
	 * @param string $content Feed item content.
	 * @param string $context Feed item context, either 'excerpt' or 'full'.
	 *
	 * @return bool
	 */
	private function can_embed_footer( $content, $context ) {
		/**
		 * Allow the RSS footer to be dynamically shown/hidden.
		 *
		 * @param bool   $show_embed Indicates if the RSS footer should be shown or not.
		 * @param string $context    The context of the RSS content - 'full' or 'excerpt'.
		 */
		if ( false === $this->do_filter( 'frontend/rss/include_footer', true, $context ) ) {
			return false;
		}

		return is_feed();
	}

	/**
	 * Get RSS content for specified location.
	 *
	 * @param string $which Location ID.
	 *
	 * @return string
	 */
	private function get_rss_content( $which ) {
		$content = $this->do_filter( 'frontend/rss/' . $which . '_content', Helper::get_settings( 'general.rss_' . $which . '_content' ) );

		return '' !== $content ? wpautop( $this->rss_replace_vars( $content ) ) : $content;
	}

	/**
	 * Replace variables with the actual values in RSS header and footer messages.
	 *
	 * @param string $content The RSS content.
	 *
	 * @return string
	 */
	private function rss_replace_vars( $content ) {
		global $post;

		/**
		 * Add nofollow for the links in the RSS header and footer messages. Default: true.
		 *
		 * @param bool $unsigned Whether or not to follow the links in RSS feed, defaults to true.
		 */
		$no_follow = $this->do_filter( 'frontend/rss/nofollow_links', true );
		$no_follow = true === $no_follow ? 'rel="nofollow" ' : '';

		$author_link = '';
		if ( is_object( $post ) ) {
			$author_link = '<a ' . $no_follow . 'href="' . esc_url( get_author_posts_url( $post->post_author ) ) . '">' . esc_html( get_the_author() ) . '</a>';
		}
		$post_link      = '<a ' . $no_follow . 'href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a>';
		$blog_link      = '<a ' . $no_follow . 'href="' . esc_url( get_bloginfo( 'url' ) ) . '">' . esc_html( get_bloginfo( 'name' ) ) . '</a>';
		$blog_desc_link = '<a ' . $no_follow . 'href="' . esc_url( get_bloginfo( 'url' ) ) . '">' . esc_html( get_bloginfo( 'name' ) ) . ' - ' . esc_html( get_bloginfo( 'description' ) ) . '</a>';

		// Featured image.
		$image = Helper::get_thumbnail_with_fallback( $post->ID, 'full' );
		$image = isset( $image[0] ) ? '<img src="' . $image[0] . '" style="display: block; margin: 1em auto">' : '';

		$content = stripslashes( trim( $content ) );
		$content = str_replace( '%AUTHORLINK%', $author_link, $content );
		$content = str_replace( '%POSTLINK%', $post_link, $content );
		$content = str_replace( '%BLOGLINK%', $blog_link, $content );
		$content = str_replace( '%BLOGDESCLINK%', $blog_desc_link, $content );
		$content = str_replace( '%FEATUREDIMAGE%', $image, $content );

		return $content;
	}
}
