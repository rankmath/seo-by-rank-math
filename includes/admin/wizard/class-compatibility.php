<?php
/**
 * The Compatibility wizard step.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Wizard
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Wizard;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Step class.
 */
class Compatibility implements Wizard_Step {

	/**
	 * Get Localized data to be used in the Compatibility step.
	 *
	 * @return array
	 */
	public static function get_localized_data() {
		$php_version = phpversion();
		return [
			'conflictingPlugins'    => self::get_conflicting_plugins(),
			'phpVersion'            => phpversion(),
			'phpVersionOk'          => version_compare( $php_version, rank_math()->php_version, '>' ),
			'phpVersionRecommended' => version_compare( $php_version, '7.4', '<' ),
			'extensions'            => [
				'dom'        => extension_loaded( 'dom' ),
				'simpleXml'  => extension_loaded( 'SimpleXML' ),
				'image'      => extension_loaded( 'gd' ) || extension_loaded( 'imagick' ),
				'mbString'   => extension_loaded( 'mbstring' ),
				'openSsl'    => extension_loaded( 'openssl' ),
				'base64Func' => function_exists( 'base64_encode' ) && function_exists( 'base64_decode' ) && (bool) base64_decode( base64_encode( '1' ) ),  // phpcs:ignore -- Verified as safe usage.
			],
		];
	}

	/**
	 * Save handler for step.
	 *
	 * @param array $values Values to save.
	 *
	 * @return bool
	 */
	public static function save( $values ) {
		$settings = wp_parse_args(
			rank_math()->settings->all_raw(),
			[ 'general' => '' ]
		);

		$settings['general']['setup_mode'] = ! empty( $values['setup_mode'] ) ? sanitize_text_field( $values['setup_mode'] ) : 'easy';

		if ( 'custom' === $settings['general']['setup_mode'] ) {
			// Don't change, use custom imported value.
			return true;
		}

		Helper::update_all_settings( $settings['general'], null, null );

		return true;
	}

	/**
	 * Get conflicting plugins.
	 *
	 * @return array
	 */
	private static function get_conflicting_plugins() {
		$plugins_found       = [];
		$active_plugins      = get_option( 'active_plugins' );
		$conflicting_plugins = self::get_conflicting_plugins_list();
		foreach ( $conflicting_plugins as $plugin_slug => $plugin_name ) {
			if ( in_array( $plugin_slug, $active_plugins, true ) !== false ) {
				$plugins_found[ $plugin_slug ] = $plugin_name;
			}
		}

		return $plugins_found;
	}

	/**
	 * Return list of conflicting plugins.
	 *
	 * @return array List of plugins in path => name format.
	 */
	private static function get_conflicting_plugins_list() {

		$plugins = [
			'2-click-socialmedia-buttons/2-click-socialmedia-buttons.php' => '2 Click Social Media Buttons.',
			'add-link-to-facebook/add-link-to-facebook.php' => 'Add Link to Facebook.',
			'extended-wp-reset/extended-wp-reset.php'      => 'Extended WP Reset.',
			'add-meta-tags/add-meta-tags.php'              => 'Add Meta Tags.',
			'all-in-one-seo-pack/all_in_one_seo_pack.php'  => 'All In One SEO Pack',
			'easy-facebook-share-thumbnails/esft.php'      => 'Easy Facebook Share Thumbnail.',
			'facebook/facebook.php'                        => 'Facebook (official plugin).',
			'facebook-awd/AWD_facebook.php'                => 'Facebook AWD All in one.',
			'facebook-featured-image-and-open-graph-meta-tags/fb-featured-image.php' => 'Facebook Featured Image & OG Meta Tags.',
			'facebook-meta-tags/facebook-metatags.php'     => 'Facebook Meta Tags.',
			'wonderm00ns-simple-facebook-open-graph-tags/wonderm00n-open-graph.php' => 'Facebook Open Graph Meta Tags for WordPress.',
			'facebook-revised-open-graph-meta-tag/index.php' => 'Facebook Revised Open Graph Meta Tag.',
			'facebook-thumb-fixer/_facebook-thumb-fixer.php' => 'Facebook Thumb Fixer.',
			'facebook-and-digg-thumbnail-generator/facebook-and-digg-thumbnail-generator.php' => 'Fedmich\'s Facebook Open Graph Meta.',
			'network-publisher/networkpub.php'             => 'Network Publisher.',
			'nextgen-facebook/nextgen-facebook.php'        => 'NextGEN Facebook OG.',
			'opengraph/opengraph.php'                      => 'Open Graph.',
			'open-graph-protocol-framework/open-graph-protocol-framework.php' => 'Open Graph Protocol Framework.',
			'seo-facebook-comments/seofacebook.php'        => 'SEO Facebook Comments.',
			'seo-ultimate/seo-ultimate.php'                => 'SEO Ultimate.',
			'sexybookmarks/sexy-bookmarks.php'             => 'Shareaholic.',
			'shareaholic/sexy-bookmarks.php'               => 'Shareaholic.',
			'sharepress/sharepress.php'                    => 'SharePress.',
			'simple-facebook-connect/sfc.php'              => 'Simple Facebook Connect.',
			'social-discussions/social-discussions.php'    => 'Social Discussions.',
			'social-sharing-toolkit/social_sharing_toolkit.php' => 'Social Sharing Toolkit.',
			'socialize/socialize.php'                      => 'Socialize.',
			'only-tweet-like-share-and-google-1/tweet-like-plusone.php' => 'Tweet, Like, Google +1 and Share.',
			'wordbooker/wordbooker.php'                    => 'Wordbooker.',
			'wordpress-seo/wp-seo.php'                     => 'Yoast SEO',
			'wordpress-seo-premium/wp-seo-premium.php'     => 'Yoast SEO Premium',
			'wp-seopress/seopress.php'                     => 'SEOPress',
			'wp-seopress-pro/seopress-pro.php'             => 'SEOPress Pro',
			'wpsso/wpsso.php'                              => 'WordPress Social Sharing Optimization.',
			'wp-caregiver/wp-caregiver.php'                => 'WP Caregiver.',
			'wp-facebook-like-send-open-graph-meta/wp-facebook-like-send-open-graph-meta.php' => 'WP Facebook Like Send & Open Graph Meta.',
			'wp-facebook-open-graph-protocol/wp-facebook-ogp.php' => 'WP Facebook Open Graph protocol.',
			'wp-ogp/wp-ogp.php'                            => 'WP-OGP.',
			'zoltonorg-social-plugin/zosp.php'             => 'Zolton.org Social Plugin.',
			'all-in-one-schemaorg-rich-snippets/index.php' => 'All In One Schema Rich Snippets.',
			'wp-schema-pro/wp-schema-pro.php'              => 'Schema Pro',
			'no-category-base-wpml/no-category-base-wpml.php' => 'No Category Base (WPML)',
			'all-404-redirect-to-homepage/all-404-redirect-to-homepage.php' => 'All 404 Redirect to Homepage',
			'remove-category-url/remove-category-url.php'  => 'Remove Category URL',
		];

		$plugins = Helper::is_module_active( 'redirections' ) ? array_merge( $plugins, self::get_redirection_conflicting_plugins() ) : $plugins;
		$plugins = Helper::is_module_active( 'sitemap' ) ? array_merge( $plugins, self::get_sitemap_conflicting_plugins() ) : $plugins;

		return $plugins;
	}

	/**
	 * Redirection: conflicting plugins.
	 *
	 * @return array
	 */
	private static function get_redirection_conflicting_plugins() {
		return [
			'redirection/redirection.php' => 'Redirection',
		];
	}

	/**
	 * Sitemap: conflicting plugins.
	 *
	 * @return array
	 */
	private static function get_sitemap_conflicting_plugins() {
		return [
			'google-sitemap-plugin/google-sitemap-plugin.php' => 'Google Sitemap (BestWebSoft).',
			'xml-sitemaps/xml-sitemaps.php'                => 'XML Sitemaps (Denis de Bernardy and Mike Koepke).',
			'bwp-google-xml-sitemaps/bwp-simple-gxs.php'   => 'Better WordPress Google XML Sitemaps (Khang Minh).',
			'google-sitemap-generator/sitemap.php'         => 'Google XML Sitemaps (Arne Brachhold).',
			'xml-sitemap-feed/xml-sitemap.php'             => 'XML Sitemap & Google News feeds (RavanH).',
			'google-monthly-xml-sitemap/monthly-xml-sitemap.php' => 'Google Monthly XML Sitemap (Andrea Pernici).',
			'simple-google-sitemap-xml/simple-google-sitemap-xml.php' => 'Simple Google Sitemap XML (iTx Technologies).',
			'another-simple-xml-sitemap/another-simple-xml-sitemap.php' => 'Another Simple XML Sitemap.',
			'xml-maps/google-sitemap.php'                  => 'Xml Sitemap (Jason Martens).',
			'google-xml-sitemap-generator-by-anton-dachauer/adachauer-google-xml-sitemap.php' => 'Google XML Sitemap Generator by Anton Dachauer (Anton Dachauer).',
			'wp-xml-sitemap/wp-xml-sitemap.php'            => 'WP XML Sitemap (Team Vivacity).',
			'sitemap-generator-for-webmasters/sitemap.php' => 'Sitemap Generator for Webmasters (iwebslogtech).',
			'xml-sitemap-xml-sitemapcouk/xmls.php'         => 'XML Sitemap - XML-Sitemap.co.uk (Simon Hancox).',
			'sewn-in-xml-sitemap/sewn-xml-sitemap.php'     => 'Sewn In XML Sitemap (jcow).',
			'rps-sitemap-generator/rps-sitemap-generator.php' => 'RPS Sitemap Generator (redpixelstudios).',
		];
	}
}
