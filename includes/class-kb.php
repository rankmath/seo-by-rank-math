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
		'pro-help-tab'                => 'https://rankmath.com/pricing/?utm_source=Plugin&utm_medium=Help%20Tab%20PRO%20Link&utm_campaign=WP',
		'pro-ready-step'              => 'https://rankmath.com/pricing/?utm_source=Plugin&utm_medium=SW%20Ready%20Step%20Upgrade&utm_campaign=WP',
		'help-affiliate'              => 'https://rankmath.com/affiliates/?utm_source=Plugin&utm_medium=Help%20Tab%20Aff%20Link&utm_campaign=WP',
		'seo-suite'                   => 'https://rankmath.com/?utm_source=Plugin&utm_campaign=WP',
		'logo'                        => 'https://rankmath.com/wordpress/plugin/seo-suite/?utm_source=Plugin&utm_medium=SW%20Logo&utm_campaign=WP',
		'rm-privacy'                  => 'https://rankmath.com/privacy-policy/?utm_source=Plugin&utm_campaign=WP',
		'usage-policy'                => 'https://rankmath.com/usage-tracking/?utm_source=Plugin&utm_medium=Analytics%20Privacy%20Notice&utm_campaign=WP',
		'free-account'                => 'https://rankmath.com/#signup',
		'free-account-benefits'       => 'https://rankmath.com/kb/free-account-benefits/?utm_source=Plugin&utm_campaign=WP',
		'yt-link'                     => 'http://youtube.com/rankmath',
		'article'                     => 'https://developers.google.com/search/docs/data-types/article/?utm_campaign=Rank+Math',
		'how-to-setup'                => 'https://rankmath.com/kb/how-to-setup/?utm_source=Plugin&utm_medium=Help%20Tab%20Setup%20KB&utm_campaign=WP',
		'how-to-setup-your-site'      => 'https://rankmath.com/kb/how-to-setup/?utm_source=Plugin&utm_medium=SW%20Your%20Site%20Setup%20KB&utm_campaign=WP',
		'seo-import'                  => 'https://rankmath.com/kb/how-to-setup/?utm_source=Plugin&utm_medium=Help%20Tab%20Import%20KB&utm_campaign=WP#import-data',
		'seo-import-sw'               => 'https://rankmath.com/kb/how-to-setup/?utm_source=Plugin&utm_medium=SW%20Import%20KB&utm_campaign=WP#import-data',
		'local-seo'                   => 'https://rankmath.com/kb/how-to-setup/?utm_source=Plugin&utm_campaign=WP#easy-and-advanced-mode',
		'seo-tweaks'                  => 'https://rankmath.com/kb/how-to-setup/?utm_source=Plugin&utm_medium=SW%20Optimization%20Step&utm_campaign=WP#optimization',
		'sw-analytics-kb'             => 'https://rankmath.com/kb/analytics/?utm_source=Plugin&utm_medium=SW%20Analytics%20Step%20Description&utm_campaign=WP',
		'benefits-analytics-kb'       => 'https://rankmath.com/kb/analytics/?utm_source=Plugin&utm_medium=SW%20Analytics%20Step%20Benefits&utm_campaign=WP',
		'settings-gdpr-analytics'     => 'https://rankmath.com/blog/google-analytics-gdpr/?utm_source=Plugin&utm_medium=SW%20Analytics%20GDPR%20Option&utm_campaign=WP',
		'url-inspection-api'          => 'https://rankmath.com/kb/url-inspection-api-integration/?utm_source=Plugin&utm_medium=SW%20Analytics%20Index%20Status%20Option&utm_campaign=WP',
		'seo-analysis-gsc-test'       => 'https://rankmath.com/kb/analytics/?utm_source=Plugin&utm_medium=SEO%20Analysis%20GSC%20Test&utm_campaign=WP',
		'remove-category-base'        => 'https://rankmath.com/kb/general-settings/?utm_source=Plugin&utm_campaign=WP#strip-category-base',
		'link-settings'               => 'https://rankmath.com/kb/general-settings/?utm_source=Plugin&utm_medium=Options%20Panel%20Links%20Tab&utm_campaign=WP#links',
		'image-settings'              => 'https://rankmath.com/kb/general-settings/?utm_source=Plugin&utm_campaign=WP#images',
		'breadcrumbs'                 => 'https://rankmath.com/kb/general-settings/?utm_source=Plugin&utm_medium=Options%20Panel%20Breadcrumbs%20Tab&utm_campaign=WP#breadcrumbs',
		'breadcrumbs-install'         => 'https://rankmath.com/kb/breadcrumbs/?utm_source=Plugin&utm_medium=Options%20Panel%20Breadcrumbs%20Tab&utm_campaign=WP#add-breadcrumbs-theme',
		'webmaster-tools'             => 'https://rankmath.com/kb/general-settings/?utm_source=Plugin&utm_medium=Options%20Panel%20Webmaster%20Tools%20Tab&utm_campaign=WP#webmaster-tools',
		'edit-robotstxt'              => 'https://rankmath.com/kb/general-settings/?utm_source=Plugin&utm_medium=Options%20Panel%20Robots%20Tab&utm_campaign=WP#edit-robotstxt',
		'edit-htaccess'               => 'https://rankmath.com/kb/general-settings/?utm_source=Plugin&utm_medium=Options%20Panel%20htaccess%20Tab&utm_campaign=WP#edit-htaccess',
		'woocommerce-settings'        => 'https://rankmath.com/kb/general-settings/?utm_source=Plugin&utm_campaign=WP#woo-commerce',
		'404-monitor'                 => 'https://rankmath.com/kb/monitor-404-errors/?utm_source=Plugin&utm_campaign=WP',
		'404-monitor-settings'        => 'https://rankmath.com/kb/general-settings/?utm_source=Plugin&utm_medium=Options%20Panel%20404%20Monitor%20Tab&utm_campaign=WP#404-monitor',
		'404-monitor-help'            => 'https://rankmath.com/kb/general-settings/?utm_source=Plugin&utm_medium=404%20Monitor%20Help%20Toggle&utm_campaign=WP#404-monitor',
		'redirections-settings'       => 'https://rankmath.com/kb/general-settings/?utm_source=Plugin&utm_medium=Options%20Panel%20Redirections%20Tab&utm_campaign=WP#redirections',
		'analytics-settings'          => 'https://rankmath.com/kb/general-settings/?utm_source=Plugin&utm_medium=Options%20Panel%20Analytics%20Tab&utm_campaign=WP#search-console',
		'content-ai-settings'         => 'https://rankmath.com/kb/how-to-use-content-ai/?utm_source=Plugin&utm_medium=Options%20Panel%20Content%20AI%20Tab&utm_campaign=WP',
		'podcast-settings'            => 'https://rankmath.com/kb/podcast-schema/?utm_source=Plugin&utm_medium=Options%20Panel%20Podcast%20Tab&utm_campaign=WP',
		'other-settings'              => 'https://rankmath.com/kb/general-settings/?utm_source=Plugin&utm_medium=Options%20Panel%20Others%20Tab&utm_campaign=WP#others',
		'score-100-sw'                => 'https://rankmath.com/kb/score-100-in-tests/?utm_source=Plugin&utm_medium=SW%20Ready%20Score%20Image&utm_campaign=WP',
		'score-100-dh'                => 'https://rankmath.com/kb/score-100-in-tests/?utm_source=Plugin&utm_medium=Help%20Tab%20Score%20KB&utm_campaign=WP',
		'score-100-ce'                => 'https://rankmath.com/kb/score-100-in-tests/?utm_source=Plugin&utm_medium=CE%20General%20Tab%20Score%20Notice&utm_campaign=WP',
		'score-100-ge'                => 'https://rankmath.com/kb/score-100-in-tests/?utm_source=Plugin&utm_medium=Gutenberg&utm_campaign=WP',
		'toc'                         => 'https://rankmath.com/kb/score-100-in-tests/?utm_source=Plugin&utm_campaign=WP#table-of-contents',
		'content-length'              => 'https://rankmath.com/kb/score-100-in-tests/?utm_source=Plugin&utm_campaign=WP#content-length',
		'sentiments'                  => 'https://monkeylearn.com/sentiment-analysis/?utm_campaign=Rank+Math',
		'rm-requirements'             => 'https://rankmath.com/kb/requirements/?utm_source=Plugin&utm_campaign=WP',
		'rm-kb'                       => 'https://rankmath.com/kb/wordpress/seo-suite/?utm_source=Plugin&utm_medium=Help%20Tab%20KB%20Link&utm_campaign=WP',
		'rm-kb-ready'                 => 'https://rankmath.com/kb/wordpress/seo-suite/?utm_source=Plugin&utm_medium=SW%20Ready%20Step%20KB&utm_campaign=WP',
		'fix-404'                     => 'https://rankmath.com/kb/fix-404-errors/?utm_source=Plugin&utm_campaign=WP',
		'import-export-settings'      => 'https://rankmath.com/kb/import-export-settings/?utm_source=Plugin&utm_campaign=WP',
		'social-tab'                  => 'https://rankmath.com/kb/meta-box-social-tab/?utm_source=Plugin&utm_medium=CE%20Social%20Tab&utm_campaign=WP',
		'redirections'                => 'https://rankmath.com/kb/setting-up-redirections/?utm_source=Plugin&utm_medium=SW%20Redirection%20Step&utm_campaign=WP',
		'role-manager'                => 'https://rankmath.com/kb/role-manager/?utm_source=Plugin&utm_campaign=WP',
		'analytics-kb'                => 'https://rankmath.com/kb/search-console/?utm_source=Plugin&utm_campaign=WP',
		'rich-snippets'               => 'https://rankmath.com/kb/rich-snippets/?utm_source=Plugin&utm_campaign=WP',
		'seo-analysis'                => 'https://rankmath.com/kb/seo-analysis/?utm_source=Plugin&utm_campaign=WP',
		'rm-support'                  => 'https://rankmath.com/support/?utm_source=Plugin&utm_medium=Help%20Tab%20Ticket&utm_campaign=WP',
		'rm-support-ready'            => 'https://rankmath.com/support/?utm_source=Plugin&utm_medium=SW%20Ready%20Step%20Support&utm_campaign=WP',
		'review-rm'                   => 'https://wordpress.org/support/plugin/seo-by-rank-math/reviews/?rate=5#new-post',
		'fb-group'                    => 'https://www.facebook.com/groups/rankmathseopluginwordpress/',
		'tw-link'                     => 'https://twitter.com/rankmathseo',
		'fb-link'                     => 'https://www.facebook.com/rankmath/',
		'configure-sitemaps'          => 'https://rankmath.com/kb/configure-sitemaps/?utm_source=Plugin&utm_medium=SW%20Sitemap%20Step&utm_campaign=WP',
		'sitemap-general'             => 'https://rankmath.com/kb/configure-sitemaps/?utm_source=Plugin&utm_campaign=WP#general',
		'sitemap-posts'               => 'https://rankmath.com/kb/configure-sitemaps/?utm_source=Plugin&utm_campaign=WP#posts',
		'sitemap-pages'               => 'https://rankmath.com/kb/configure-sitemaps/?utm_source=Plugin&utm_campaign=WP#pages',
		'sitemap-media'               => 'https://rankmath.com/kb/configure-sitemaps/?utm_source=Plugin&utm_campaign=WP#media',
		'sitemap-product'             => 'https://rankmath.com/kb/configure-sitemaps/?utm_source=Plugin&utm_campaign=WP#products',
		'sitemap-category'            => 'https://rankmath.com/kb/configure-sitemaps/?utm_source=Plugin&utm_campaign=WP#categories',
		'sitemap-tag'                 => 'https://rankmath.com/kb/configure-sitemaps/?utm_source=Plugin&utm_campaign=WP#tags',
		'sitemap-product_cat'         => 'https://rankmath.com/kb/configure-sitemaps/?utm_source=Plugin&utm_campaign=WP#product-categories',
		'sitemap-product_tag'         => 'https://rankmath.com/kb/configure-sitemaps/?utm_source=Plugin&utm_campaign=WP#product-tags',
		'titles-meta'                 => 'https://rankmath.com/kb/titles-and-meta/?utm_source=Plugin&utm_campaign=WP',
		'local-seo-settings'          => 'https://rankmath.com/kb/local-seo/?utm_source=Plugin&utm_campaign=WP',
		'social-meta-settings'        => 'https://rankmath.com/kb/titles-and-meta/?utm_source=Plugin&utm_campaign=WP#social-meta',
		'homepage-settings'           => 'https://rankmath.com/kb/titles-and-meta/?utm_source=Plugin&utm_campaign=WP#homepage',
		'author-settings'             => 'https://rankmath.com/kb/titles-and-meta/?utm_source=Plugin&utm_campaign=WP#authors',
		'misc-settings'               => 'https://rankmath.com/kb/titles-and-meta/?utm_source=Plugin&utm_campaign=WP#misc-pages',
		'post-settings'               => 'https://rankmath.com/kb/titles-and-meta/?utm_source=Plugin&utm_campaign=WP#Posts',
		'page-settings'               => 'https://rankmath.com/kb/titles-and-meta/?utm_source=Plugin&utm_campaign=WP#pages',
		'media-settings'              => 'https://rankmath.com/kb/titles-and-meta/?utm_source=Plugin&utm_campaign=WP#media',
		'product-settings'            => 'https://rankmath.com/kb/titles-and-meta/?utm_source=Plugin&utm_campaign=WP#products',
		'category-settings'           => 'https://rankmath.com/kb/titles-and-meta/?utm_source=Plugin&utm_campaign=WP#categories',
		'tag-settings'                => 'https://rankmath.com/kb/titles-and-meta/?utm_source=Plugin&utm_campaign=WP#tags',
		'product-categories-settings' => 'https://rankmath.com/kb/titles-and-meta/?utm_source=Plugin&utm_campaign=WP#product-categories',
		'product-tags-settings'       => 'https://rankmath.com/kb/titles-and-meta/?utm_source=Plugin&utm_campaign=WP#product-tags',
		'version-control'             => 'https://rankmath.com/kb/version-control/?utm_source=Plugin&utm_campaign=WP',
		'general-settings'            => 'https://rankmath.com/kb/general-settings/?utm_source=Plugin&utm_campaign=WP',
		'google-api-key'              => 'https://rankmath.com/kb/how-to-get-a-google-api-key/?utm_source=Plugin&utm_campaign=WP',
		'email-reports-logo'          => 'https://rankmath.com/kb/seo-email-reporting/?utm_source=Plugin&utm_medium=Email%20Report%20Logo&utm_campaign=WP',
		'headless-support'            => 'https://rankmath.com/kb/headless-support/?utm_source=Plugin&utm_medium=Others%20Tab%20KB%20Link&utm_campaign=WP',
		'instant-indexing'            => 'https://rankmath.com/kb/how-to-use-indexnow/?utm_source=Plugin&utm_campaign=WP',
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
			$manager = new self();
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
