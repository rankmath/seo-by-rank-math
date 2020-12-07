<?php
/**
 * Knowledgebase links.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath;

use MyThemeShop\Helpers\Arr;

defined( 'ABSPATH' ) || exit;

/**
 * KB class.
 */
class KB {

	/**
	 * Hold links.
	 *
	 * @var array
	 */
	private $links = [
		'pro-general-g'               => 'https://rankmath.com/pricing/?utm_source=Plugin&utm_medium=Gutenberg%20General%20Tab%20Notice&utm_campaign=WP',
		'pro-general-ce'              => 'https://rankmath.com/pricing/?utm_source=Plugin&utm_medium=CE%20General%20Tab%20Notice&utm_campaign=WP',
		'seo-suite'                   => 'https://rankmath.com/?utm_source=Plugin&utm_campaign=WP',
		'logo'                        => 'https://rankmath.com/wordpress/plugin/seo-suite/?utm_source=Plugin&utm_campaign=WP',
		'rm-privacy'                  => 'https://rankmath.com/privacy-policy/?utm_source=Plugin&utm_campaign=WP',
		'usage-policy'                => 'https://rankmath.com/usage-tracking/?utm_source=Plugin&utm_medium=Analytics%20Privacy%20Notice&utm_campaign=WP',
		'free-account'                => 'https://rankmath.com/#signup',
		'free-account-benefits'       => 'https://rankmath.com/kb/free-account-benefits/?utm_source=Plugin&utm_campaign=WP',
		'wp-error-fixes'              => 'https://mythemeshop.com/wordpress-errors-fixes/?utm_source=Plugin&utm_campaign=WP',
		'article'                     => 'https://developers.google.com/search/docs/data-types/article/?utm_campaign=Rank+Math',
		'how-to-setup'                => 'https://rankmath.com/kb/how-to-setup/?utm_source=Plugin&utm_campaign=WP',
		'seo-import'                  => 'https://rankmath.com/kb/how-to-setup/#import-data?utm_source=Plugin&utm_campaign=WP',
		'local-seo'                   => 'https://rankmath.com/kb/how-to-setup/#easy-and-advanced-mode?utm_source=Plugin&utm_campaign=WP',
		'seo-tweaks'                  => 'https://rankmath.com/kb/how-to-setup/#optimization?utm_source=Plugin&utm_campaign=WP',
		'analytics'                   => 'https://rankmath.com/kb/how-to-setup/#google-search-console?utm_source=Plugin&utm_campaign=WP',
		'remove-category-base'        => 'https://rankmath.com/kb/general-settings/#strip-category-base?utm_source=Plugin&utm_campaign=WP',
		'link-settings'               => 'https://rankmath.com/kb/general-settings/#links?utm_source=Plugin&utm_campaign=WP',
		'image-settings'              => 'https://rankmath.com/kb/general-settings/#images?utm_source=Plugin&utm_campaign=WP',
		'breadcrumbs'                 => 'https://rankmath.com/kb/general-settings/#breadcrumbs?utm_source=Plugin&utm_campaign=WP',
		'webmaster-tools'             => 'https://rankmath.com/kb/general-settings/#webmaster-tools?utm_source=Plugin&utm_campaign=WP',
		'edit-robotstxt'              => 'https://rankmath.com/kb/general-settings/#edit-robotstxt?utm_source=Plugin&utm_campaign=WP',
		'edit-htaccess'               => 'https://rankmath.com/kb/general-settings/#edit-htaccess?utm_source=Plugin&utm_campaign=WP',
		'woocommerce-settings'        => 'https://rankmath.com/kb/general-settings/#woo-commerce?utm_source=Plugin&utm_campaign=WP',
		'404-monitor-settings'        => 'https://rankmath.com/kb/general-settings/#404-monitor?utm_source=Plugin&utm_campaign=WP',
		'redirections-settings'       => 'https://rankmath.com/kb/general-settings/#redirections?utm_source=Plugin&utm_campaign=WP',
		'analytics-settings'          => 'https://rankmath.com/kb/general-settings/#search-console?utm_source=Plugin&utm_campaign=WP',
		'other-settings'              => 'https://rankmath.com/kb/general-settings/#others?utm_source=Plugin&utm_campaign=WP',
		'score-100-sw'                => 'https://rankmath.com/kb/score-100-in-tests/?utm_source=Plugin&utm_medium=Setup%20Wizard&utm_campaign=WP',
		'score-100-dh'                => 'https://rankmath.com/kb/score-100-in-tests/?utm_source=Plugin&utm_medium=Dashboard%20Help&utm_campaign=WP',
		'score-100-ce'                => 'https://rankmath.com/kb/score-100-in-tests/?utm_source=Plugin&utm_medium=Classic&utm_campaign=WP',
		'score-100-ge'                => 'https://rankmath.com/kb/score-100-in-tests/?utm_source=Plugin&utm_medium=Gutenberg&utm_campaign=WP',
		'toc'                         => 'https://rankmath.com/kb/score-100-in-tests/#table-of-contents?utm_source=Plugin&utm_campaign=WP',
		'content-length'              => 'https://rankmath.com/kb/score-100-in-tests/#content-length?utm_source=Plugin&utm_campaign=WP',
		'sentiments'                  => 'https://monkeylearn.com/sentiment-analysis/?utm_campaign=Rank+Math',
		'rm-requirements'             => 'https://rankmath.com/kb/requirements/?utm_source=Plugin&utm_campaign=WP',
		'rm-kb'                       => 'https://rankmath.com/kb/wordpress/seo-suite/?utm_source=Plugin&utm_campaign=WP',
		'fix-404'                     => 'https://rankmath.com/kb/fix-404-errors/?utm_source=Plugin&utm_campaign=WP',
		'import-export-settings'      => 'https://rankmath.com/kb/import-export-settings/?utm_source=Plugin&utm_campaign=WP',
		'social-tab'                  => 'https://rankmath.com/kb/meta-box-social-tab/?utm_source=Plugin&utm_campaign=WP',
		'404-monitor'                 => 'https://rankmath.com/kb/monitor-404-errors/?utm_source=Plugin&utm_campaign=WP',
		'redirections'                => 'https://rankmath.com/kb/setting-up-redirections/?utm_source=Plugin&utm_campaign=WP',
		'role-manager'                => 'https://rankmath.com/kb/role-manager/?utm_source=Plugin&utm_campaign=WP',
		'analytics-kb'                => 'https://rankmath.com/kb/search-console/?utm_source=Plugin&utm_campaign=WP',
		'rich-snippets'               => 'https://rankmath.com/kb/rich-snippets/?utm_source=Plugin&utm_campaign=WP',
		'seo-analysis'                => 'https://rankmath.com/kb/seo-analysis/?utm_source=Plugin&utm_campaign=WP',
		'rm-support'                  => 'https://support.rankmath.com/?utm_source=Plugin&utm_campaign=WP',
		'review-rm'                   => 'https://wordpress.org/support/plugin/seo-by-rank-math/reviews/?rate=5#new-post',
		'fb-group'                    => 'https://www.facebook.com/groups/rankmathseopluginwordpress/',
		'tw-link'                     => 'https://twitter.com/rankmathseo',
		'fb-link'                     => 'https://www.facebook.com/rankmath/',
		'configure-sitemaps'          => 'https://rankmath.com/kb/configure-sitemaps/?utm_source=Plugin&utm_campaign=WP',
		'sitemap-general'             => 'https://rankmath.com/kb/configure-sitemaps/#general?utm_source=Plugin&utm_campaign=WP',
		'sitemap-posts'               => 'https://rankmath.com/kb/configure-sitemaps/#posts?utm_source=Plugin&utm_campaign=WP',
		'sitemap-pages'               => 'https://rankmath.com/kb/configure-sitemaps/#pages?utm_source=Plugin&utm_campaign=WP',
		'sitemap-media'               => 'https://rankmath.com/kb/configure-sitemaps/#media?utm_source=Plugin&utm_campaign=WP',
		'sitemap-product'             => 'https://rankmath.com/kb/configure-sitemaps/#products?utm_source=Plugin&utm_campaign=WP',
		'sitemap-category'            => 'https://rankmath.com/kb/configure-sitemaps/#categories?utm_source=Plugin&utm_campaign=WP',
		'sitemap-tag'                 => 'https://rankmath.com/kb/configure-sitemaps/#tags?utm_source=Plugin&utm_campaign=WP',
		'sitemap-product_cat'         => 'https://rankmath.com/kb/configure-sitemaps/#product-categories?utm_source=Plugin&utm_campaign=WP',
		'sitemap-product_tag'         => 'https://rankmath.com/kb/configure-sitemaps/#product-tags?utm_source=Plugin&utm_campaign=WP',
		'titles-meta'                 => 'https://rankmath.com/kb/titles-and-meta/?utm_source=Plugin&utm_campaign=WP',
		'local-seo-settings'          => 'https://rankmath.com/kb/local-seo/?utm_source=Plugin&utm_campaign=WP',
		'social-meta-settings'        => 'https://rankmath.com/kb/titles-and-meta/#social-meta?utm_source=Plugin&utm_campaign=WP',
		'homepage-settings'           => 'https://rankmath.com/kb/titles-and-meta/#homepage?utm_source=Plugin&utm_campaign=WP',
		'author-settings'             => 'https://rankmath.com/kb/titles-and-meta/#authors?utm_source=Plugin&utm_campaign=WP',
		'misc-settings'               => 'https://rankmath.com/kb/titles-and-meta/#misc-pages?utm_source=Plugin&utm_campaign=WP',
		'post-settings'               => 'https://rankmath.com/kb/titles-and-meta/#Posts?utm_source=Plugin&utm_campaign=WP',
		'page-settings'               => 'https://rankmath.com/kb/titles-and-meta/#pages?utm_source=Plugin&utm_campaign=WP',
		'media-settings'              => 'https://rankmath.com/kb/titles-and-meta/#media?utm_source=Plugin&utm_campaign=WP',
		'product-settings'            => 'https://rankmath.com/kb/titles-and-meta/#products?utm_source=Plugin&utm_campaign=WP',
		'category-settings'           => 'https://rankmath.com/kb/titles-and-meta/#categories?utm_source=Plugin&utm_campaign=WP',
		'tag-settings'                => 'https://rankmath.com/kb/titles-and-meta/#tags?utm_source=Plugin&utm_campaign=WP',
		'product-categories-settings' => 'https://rankmath.com/kb/titles-and-meta/#product-categories?utm_source=Plugin&utm_campaign=WP',
		'product-tags-settings'       => 'https://rankmath.com/kb/titles-and-meta/#product-tags?utm_source=Plugin&utm_campaign=WP',
		'version-control'             => 'https://rankmath.com/kb/version-control/?utm_source=Plugin&utm_campaign=WP',
		'general-settings'            => 'https://rankmath.com/kb/general-settings/?utm_source=Plugin&utm_campaign=WP',
		'google-api-key'              => 'https://rankmath.com/kb/how-to-get-a-google-api-key/?utm_source=Plugin&utm_campaign=WP',
	];

	/**
	 * Echo the link.
	 *
	 * @param string $id Id of the link to get.
	 */
	public static function the( $id ) {
		echo self::get( $id );
	}

	/**
	 * Return the link.
	 *
	 * @param  string $id Id of the link to get.
	 * @return string
	 */
	public static function get( $id ) {
		static $manager = null;

		if ( null === $manager ) {
			$manager = new self;
			$manager->register();
		}

		return isset( $manager->links[ $id ] ) ? $manager->links[ $id ] : '#';
	}

	/**
	 * Register links.
	 */
	private function register() {
		$links = $this->get_links();
		foreach ( $links as $id => $link ) {
			$this->links[ $id ] = $link;
		}
	}

	/**
	 * Get links.
	 *
	 * @return array
	 */
	private function get_links() {
		return $this->links;
	}
}
