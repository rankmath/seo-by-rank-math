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
	private static $links = [
		// General.
		'seo-suite'                       => 'https://rankmath.com/',
		'blog'                            => 'https://rankmath.com/blog/',
		'free-account'                    => 'https://rankmath.com/my-account/',
		'support'                         => 'https://rankmath.com/support/',
		'pro'                             => 'https://rankmath.com/pricing/',
		'changelog'                       => 'https://rankmath.com/changelog/',
		'changelog-free'                  => 'https://rankmath.com/changelog/free/',
		'help-affiliate'                  => 'https://rankmath.com/affiliates/',
		'content-ai'                      => 'https://rankmath.com/content-ai/',
		'content-ai-pricing-tables'       => 'https://rankmath.com/content-ai/?#pricing-tables',
		'content-ai-restore-credits'      => 'https://rankmath.com/kb/how-to-restore-missing-content-ai-credits/',
		'free-vs-pro'                     => 'https://rankmath.com/free-vs-pro/',
		'google-updates'                  => 'https://rankmath.com/google-updates/',
		'usage-policy'                    => 'https://rankmath.com/usage-tracking/',
		'logo'                            => 'https://rankmath.com/wordpress/plugin/seo-suite/',
		'offer'                           => 'https://rankmath.com/offer/',

		// Knowledgebase.
		'knowledgebase'                   => 'https://rankmath.com/kb/',
		'how-to-setup'                    => 'https://rankmath.com/kb/how-to-setup/',
		'how-to-setup-your-site'          => 'https://rankmath.com/kb/how-to-setup/?#your-site',
		'seo-import'                      => 'https://rankmath.com/kb/how-to-setup/?#import-data',
		'seo-tweaks'                      => 'https://rankmath.com/kb/how-to-setup/?#optimization',
		'local-seo'                       => 'https://rankmath.com/kb/how-to-setup/?#easy-and-advanced-mode',
		'general-settings'                => 'https://rankmath.com/kb/general-settings/',
		'remove-category-base'            => 'https://rankmath.com/kb/general-settings/?#strip-category-base',
		'link-settings'                   => 'https://rankmath.com/kb/general-settings/?#links',
		'image-settings'                  => 'https://rankmath.com/kb/general-settings/?#images',
		'breadcrumbs'                     => 'https://rankmath.com/kb/general-settings/?#breadcrumbs',
		'webmaster-tools'                 => 'https://rankmath.com/kb/general-settings/?#webmaster-tools',
		'edit-robotstxt'                  => 'https://rankmath.com/kb/general-settings/?#edit-robotstxt',
		'edit-htaccess'                   => 'https://rankmath.com/kb/general-settings/?#edit-htaccess',
		'woocommerce-settings'            => 'https://rankmath.com/kb/general-settings/?#woo-commerce',
		'404-monitor-settings'            => 'https://rankmath.com/kb/general-settings/?#404-monitor',
		'redirections-settings'           => 'https://rankmath.com/kb/general-settings/?#redirections',
		'analytics-settings'              => 'https://rankmath.com/kb/general-settings/?#search-console',
		'other-settings'                  => 'https://rankmath.com/kb/general-settings/?#others',
		'score-100'                       => 'https://rankmath.com/kb/score-100-in-tests/',
		'content-length'                  => 'https://rankmath.com/kb/score-100-in-tests/?#content-length',
		'toc'                             => 'https://rankmath.com/kb/score-100-in-tests/?#content-length',
		'configure-sitemaps'              => 'https://rankmath.com/kb/configure-sitemaps/',
		'sitemap-general'                 => 'https://rankmath.com/kb/configure-sitemaps/?#general',
		'sitemap-post'                    => 'https://rankmath.com/kb/configure-sitemaps/?#posts',
		'sitemap-page'                    => 'https://rankmath.com/kb/configure-sitemaps/?#pages',
		'sitemap-media'                   => 'https://rankmath.com/kb/configure-sitemaps/?#media',
		'sitemap-product'                 => 'https://rankmath.com/kb/configure-sitemaps/?#products',
		'social-meta-settings'            => 'https://rankmath.com/kb/titles-and-meta/?#social-meta',
		'homepage-settings'               => 'https://rankmath.com/kb/titles-and-meta/?#homepage',
		'author-settings'                 => 'https://rankmath.com/kb/titles-and-meta/?#authors',
		'misc-settings'                   => 'https://rankmath.com/kb/titles-and-meta/?#misc-pages',
		'post-settings'                   => 'https://rankmath.com/kb/titles-and-meta/?#Posts',
		'page-settings'                   => 'https://rankmath.com/kb/titles-and-meta/?#pages',
		'media-settings'                  => 'https://rankmath.com/kb/titles-and-meta/?#media',
		'product-settings'                => 'https://rankmath.com/kb/titles-and-meta/?#products',
		'category-settings'               => 'https://rankmath.com/kb/titles-and-meta/?#categories',
		'tag-settings'                    => 'https://rankmath.com/kb/titles-and-meta/?#tags',
		'product-categories-settings'     => 'https://rankmath.com/kb/titles-and-meta/?#product-categories',
		'product-tags-settings'           => 'https://rankmath.com/kb/titles-and-meta/?#product-tags',
		'seo-email-reporting'             => 'https://rankmath.com/kb/seo-email-reporting/',
		'email-reports-logo'              => 'https://rankmath.com/kb/seo-email-reporting/#report-logo',
		'kb-seo-suite'                    => 'https://rankmath.com/kb/wordpress/seo-suite/',
		'kb-search'                       => 'https://rankmath.com/kb/wordpress/seo-suite/?ht-kb-search=1',
		'help-analytics'                  => 'https://rankmath.com/kb/analytics/',
		'monitor-seo-performance'         => 'https://rankmath.com/kb/client-management/#num-3-1-monitor-seo-performance-business',
		'top-5-winning-and-losing'        => 'https://rankmath.com/kb/analytics/?#top-5-winning-and-losing-posts-pro',
		'using-ga4'                       => 'https://rankmath.com/kb/using-ga4/',
		'local-seo-settings'              => 'https://rankmath.com/kb/local-seo/',
		'kml-sitemap'                     => 'https://rankmath.com/kb/kml-sitemap/',
		'news-sitemap'                    => 'https://rankmath.com/kb/news-sitemap/',
		'role-manager'                    => 'https://rankmath.com/kb/role-manager/',
		'seo-analysis'                    => 'https://rankmath.com/kb/seo-analysis/',
		'requirements'                    => 'https://rankmath.com/kb/requirements/',
		'video-sitemap'                   => 'https://rankmath.com/kb/video-sitemap/',
		'rich-snippets'                   => 'https://rankmath.com/kb/rich-snippets/',
		'podcast-settings'                => 'https://rankmath.com/kb/podcast-schema/',
		'fix-404'                         => 'https://rankmath.com/kb/fix-404-errors/',
		'titles-meta'                     => 'https://rankmath.com/kb/titles-and-meta/',
		'version-control'                 => 'https://rankmath.com/kb/version-control/',
		'headless-support'                => 'https://rankmath.com/kb/headless-support/',
		'faq-schema-block'                => 'https://rankmath.com/kb/faq-schema-block/',
		'404-monitor'                     => 'https://rankmath.com/kb/monitor-404-errors/',
		'meta-box-social-tab'             => 'https://rankmath.com/kb/meta-box-social-tab/',
		'instant-indexing'                => 'https://rankmath.com/kb/how-to-use-indexnow/',
		'analytics-stats-bar'             => 'https://rankmath.com/kb/analytics-stats-bar/',
		'content-ai-settings'             => 'https://rankmath.com/kb/how-to-use-content-ai/',
		'content-ai-links'                => 'https://rankmath.com/kb/how-to-use-content-ai/?#links',
		'content-ai-keywords'             => 'https://rankmath.com/kb/how-to-use-content-ai/?#keywords',
		'content-ai-credits-usage'        => 'https://rankmath.com/kb/content-ai-plans-and-credits/',
		'free-account-benefits'           => 'https://rankmath.com/kb/free-account-benefits/',
		'import-export-settings'          => 'https://rankmath.com/kb/import-export-settings/',
		'location-data-shortcode'         => 'https://rankmath.com/kb/location-data-shortcode/',
		'redirections'                    => 'https://rankmath.com/kb/setting-up-redirections/',
		'about-and-mentions-schema'       => 'https://rankmath.com/kb/about-and-mentions-schema/',
		'url-inspection-api'              => 'https://rankmath.com/kb/url-inspection-api-integration/',
		'pillar-content-internal-linking' => 'https://rankmath.com/kb/pillar-content-internal-linking/',
		'breadcrumbs-install'             => 'https://rankmath.com/kb/breadcrumbs/?#add-breadcrumbs-theme',
		'change-seo-score-backlink'       => 'https://rankmath.com/kb/filters-hooks-api-developer/?#change-seo-score-backlink',
		'unable-to-encrypt'               => 'https://rankmath.com/kb/fix-automatic-update-unavailable-for-this-plugin/?#unable-to-encrypt',
		'google-verification-kb'          => 'https://rankmath.com/kb/google-site-verification/',
		'bing-verification-kb'            => 'https://rankmath.com/kb/verify-your-site-with-bing-webmaster-tools/',
		'baidu-verification-kb'           => 'https://rankmath.com/kb/baidu-webmaster-tools-verification/',
		'yandex-verification-kb'          => 'https://rankmath.com/kb/verifying-your-domain-with-yandex/',
		'norton-verification-kb'          => 'https://rankmath.com/kb/verify-site-with-norton-safe-web/',
		'pinterest-verification-kb'       => 'https://rankmath.com/kb/pinterest-site-verification/',

		// SEO Analysis.
		'analysis-site-tagline'           => 'https://rankmath.com/kb/seo-analysis/?#site-tagline-test',
		'analysis-blog-public'            => 'https://rankmath.com/kb/seo-analysis/?#blog-public-test',
		'analysis-permalink-structure'    => 'https://rankmath.com/kb/seo-analysis/?#permalink-structure-test',
		'analysis-focus-keywords'         => 'https://rankmath.com/kb/seo-analysis/?#focus-keywords-test',
		'analysis-post-titles'            => 'https://rankmath.com/kb/seo-analysis/?#post-titles-missing-focus-keywords-test',
		'analysis-search-console'         => 'https://rankmath.com/kb/seo-analysis/?#search-console-test',
		'analysis-sitemaps'               => 'https://rankmath.com/kb/seo-analysis/?#sitemaps-test',
		'analysis-auto-update'            => 'https://rankmath.com/kb/seo-analysis/?#priority',

		// Social Media.
		'yt-link'                         => 'http://youtube.com/rankmath',
		'fb-group'                        => 'https://www.facebook.com/groups/rankmathseopluginwordpress/',

		// Other.
		'google-article-schema'           => 'https://developers.google.com/search/docs/data-types/article/?utm_campaign=Rank+Math',
		'create-facebook-app'             => 'https://www.youtube.com/watch?utm_source=Plugin&utm_campaign=WP&v=-XME8Q25omQ&feature=youtu.be',
	];

	/**
	 * Echo the link.
	 *
	 * @param string $id Id of the link to get.
	 * @param  string $medium Medium of the link to get.
	 */
	public static function the( $id, $medium = '' ) {
		echo self::get( $id, $medium ); // phpcs:ignore
	}

	/**
	 * Return the link.
	 *
	 * @param  string $id Id of the link to get.
	 * @param  string $medium Medium of the link to get.
	 * @return string
	 */
	public static function get( $id, $medium = '' ) {
		$links = self::get_links();
		$url   = isset( $links[ $id ] ) ? $links[ $id ] : '#';

		if ( empty( $medium ) ) {
			return $url;
		}

		return add_query_arg(
			[
				'utm_source'   => 'Plugin',
				'utm_medium'   => rawurlencode( $medium ),
				'utm_campaign' => 'WP',
			],
			$url
		);
	}

	/**
	 * Get links.
	 *
	 * @return array
	 */
	public static function get_links() {
		return self::$links;
	}
}
