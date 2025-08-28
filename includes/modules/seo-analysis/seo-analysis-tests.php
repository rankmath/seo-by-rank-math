<?php
/**
 * The local tests for the SEO Analyzer.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\SEO_Analysis
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\KB;
use RankMath\Helper;
use RankMath\Helpers\DB as DB_Helper;
use RankMath\Google\Console;
use RankMath\Google\Authentication;
use RankMath\Helpers\DB;

defined( 'ABSPATH' ) || exit;

add_filter( 'rank_math/seo_analysis/tests', 'rank_math_register_seo_analysis_basic_tests' );

/**
 * Register basic local tests for the SEO Analyzer.
 *
 * @param array $tests Array of tests.
 *
 * @return array
 */
function rank_math_register_seo_analysis_basic_tests( $tests ) {
	$new_tests['site_description'] = [
		'title'       => esc_html__( 'Site Tagline', 'rank-math' ),
		/* translators: link to general setting screen */
		'description' => sprintf( esc_html__( 'Your theme may display the Site Tagline, and it can also be used in SEO titles &amp; descriptions. Set it to something unique. You can change it by navigating to <a href="%s">Settings &gt; General</a>.', 'rank-math' ), admin_url( 'options-general.php' ) ),
		'how_to_fix'  => '<p>' . esc_html__( 'Most WordPress themes place your site\'s tagline in a prominent position (inside header tags near the top of the page).  Using the right tagline can give your site an SEO boost.', 'rank-math' ) . '</p>' .
			'<p>' . esc_html__( 'Unfortunately, the standard WordPress tagline is "Just Another WordPress site."  That\'s pretty sloppy looking, and it does nothing for your SEO.  In fact, it\'s actually a security risk - it makes it easy for hackers with a WordPress exploit to locate your site with an automated search.', 'rank-math' ) . '</p>' .
			/* translators: link to general setting screen */
			'<p>' . sprintf( wp_kses_post( __( 'Changing your tagline is very easy.  Just head on over to <a target="_blank" href="%1$s">Settings - General</a> in WordPress\'s admin menu (on the left), or click on the link in this sentence.', 'rank-math' ) ), esc_url( admin_url( 'options-general.php' ) ) ) . '</p>' .
			'<p>' . esc_html__( 'The tagline is the second option.  Choose a tagline that summarizes your site in a few words.  The tagline is also a good place to use your main keyword.', 'rank-math' ) . '</p>',
		'kb_link'     => KB::get( 'analysis-site-tagline' ),
		'tooltip'     => esc_html__( 'Confirm custom tagline is set for your site', 'rank-math' ),
	];

	$new_tests['blog_public'] = [
		'title'       => esc_html__( 'Blog Public', 'rank-math' ),
		/* translators: link to general setting screen */
		'description' => esc_html__( 'Your site may not be visible to search engine.', 'rank-math' ),
		'how_to_fix'  => '<p>' .
			sprintf(
				/* translators: %1$s link to the reading settings, %2$s closing tag for the link */
				esc_html__( 'You must %1$sgo to your Reading Settings%2$s and uncheck the box for Search Engine Visibility.', 'rank-math' ),
				'<a href="' . esc_url( admin_url( 'options-reading.php' ) ) . '">',
				'</a>'
			) .
		'</p>',
		'kb_link'     => KB::get( 'analysis-blog-public' ),
		'tooltip'     => esc_html__( "Check your site's visibility to search engines", 'rank-math' ),
	];

	$new_tests['permalink_structure'] = [
		'title'       => esc_html__( 'Permalink Structure', 'rank-math' ),
		/* translators: link to permalink setting screen */
		'description' => sprintf( __( 'For the best SEO results, use a custom permalink structure, preferably one that includes the post title (<code>%%postname%%</code>). You can change it by navigating to <a href="%s">Settings &gt; Permalinks</a>', 'rank-math' ), admin_url( 'options-permalink.php' ) ),
		'how_to_fix'  => '<p>' . esc_html__( 'The standard permalink structure is pretty ugly - WordPress generates offputting URLs like: http://www.yoursite.com/?p=99', 'rank-math' ) . '</p>' .
			'<p>' . esc_html__( 'It\'s not very kind on the eyes, and it does nothing for your site\'s SEO.  In fact, it can hurt it - Google\'s bot is quite cautious about crawling pages that look auto-generated.', 'rank-math' ) . '</p>' .
			/* translators: link to permalink setting screen */
			'<p>' . sprintf( wp_kses_post( __( 'Fortunately, it\'s very easy to fix.  Just hop on over to <a target="_blank" href="%1$s">Settings - Permalinks</a>.  Then chose the "Post Name" option.', 'rank-math' ) ), esc_url( admin_url( 'options-permalink.php' ) ) ) . '</p>' .
			'<p>' . esc_html__( 'This option will replace the "?p=99" part of the URL with the post\'s title, like this: http://www.yoursite.com/my-amazing-post-title/', 'rank-math' ) . '</p>' .
			'<p>' . esc_html__( 'This looks nice for readers - and it gets your keywords into the URL (keywords in the URL is a ranking factor).', 'rank-math' ) . '</p>',
		'kb_link'     => KB::get( 'analysis-permalink-structure' ),
		'tooltip'     => esc_html__( 'Check your site for SEO-friendly permalink structure', 'rank-math' ),
	];

	$new_tests['focus_keywords'] = [
		'title'       => esc_html__( 'Focus Keywords', 'rank-math' ),
		'description' => esc_html__( 'Setting focus keywords for your posts allows Rank Math to analyse the content.', 'rank-math' ),
		'how_to_fix'  => '<p>' . esc_html__( 'Rank Math allows you to set a focus keyword for every post and page you write - the option is in the "Meta Box", which appears under the text editor in the screen where you write and edit content.', 'rank-math' ) . '</p>' .
			'<p>' . esc_html__( 'Rank Math uses these focus keywords to analyze your on-page content.  It can tell if you\'ve done a good job of optimizing your text to rank for these keywords.', 'rank-math' ) . '</p>' .
			'<p>' . esc_html__( 'Of course, if you don\'t give Rank Math a focus keyword to work with, it can\'t give you any useful feedback.', 'rank-math' ) . '</p>' .
			'<p>' . esc_html__( 'Fixing this issue is easy - just edit the post, and set a Focus Keyword.  Then follow Rank Math\'s analysis to improve your rankings.', 'rank-math' ) . '</p>',
		'kb_link'     => KB::get( 'analysis-focus-keywords' ),
		'tooltip'     => esc_html__( 'Confirm focus keywords are set for all your posts', 'rank-math' ),
	];

	$new_tests['post_titles'] = [
		'title'       => esc_html__( 'Post Titles Missing Focus Keywords', 'rank-math' ),
		'description' => esc_html__( 'Make sure the focus keywords you set for the posts appear in their titles.', 'rank-math' ),
		'how_to_fix'  => '<p>' . esc_html__( 'HTML Page Titles play a large role in Google\'s ranking algorithm.  When you add a Focus Keyword to a post or page, Rank Math will check to see that you used the keyword in the title.  If it finds any posts or pages that are missing the keyword in the title, it will tell you here.', 'rank-math' ) . '</p>' .
			'<p>' . esc_html__( 'Fixing the issue is simple - just edit the post/page and add the focus keyword(s) to the title.', 'rank-math' ) . '</p>',
		'kb_link'     => KB::get( 'analysis-post-titles' ),
		'tooltip'     => esc_html__( 'Verify the presence of focus keywords in your post titles', 'rank-math' ),
	];

	foreach ( $new_tests as $key => $test ) {
		$test['category'] = 'basic';
		$test['callback'] = 'rank_math_analyze_' . $key;
		$tests[ $key ]    = $test;
	}

	return $tests;
}

add_filter( 'rank_math/seo_analysis/tests', 'rank_math_register_seo_analysis_advanced_tests' );

/**
 * Register advanced local tests for the SEO Analyzer.
 *
 * @param array $tests Array of tests.
 *
 * @return array
 */
function rank_math_register_seo_analysis_advanced_tests( $tests ) {
	$tests['search_console'] = [
		'category'    => 'advanced',
		'title'       => esc_html__( 'Search Console', 'rank-math' ),
		'description' => sprintf(
			/* translators: link to plugin setting screen */
			esc_html__( 'Register at Google Search Console and verificate your site by adding the code to <a href="%1$s">Settings &gt; Verificate Tools</a>, then navigate to <a href="%2$s">Settings &gt; Search Console</a> to authenticate and link your site.', 'rank-math' ),
			Helper::get_settings_url( 'general', 'webmaster' ),
			Helper::get_settings_url( 'general', 'analytics' )
		),
		'how_to_fix'  => '<p>' . esc_html__( 'Google\'s Search Console is a vital source of information concerning your rankings and click-through rates.  Rank Math can import this data, so you don\'t have to log into your Google account to get the data you need.', 'rank-math' ) . '</p>' .
			/* translators: link to plugin search console setting screen */
			'<p>' . sprintf( wp_kses_post( __( 'You can integrate the Google Search Console with Rank math in the <a href="%1$s" target="_blank">Search Console tab</a>. of Rank Math\'s General Settings menu.', 'rank-math' ) ), esc_url( Helper::get_settings_url( 'general', 'analytics' ) ) ) . '</p>' .
			/* translators: Link to Search Console KB article */
			'<p>' . sprintf( wp_kses_post( __( 'Read <a href="%1$s" target="_blank">this article</a> for detailed instructions on setting up your Google Webmaster account and getting Rank Math to work with the Google Search Console.', 'rank-math' ) ), KB::get( 'help-analytics', 'SEO Analysis GSC Test' ) ) . '</p>',
		'callback'    => 'rank_math_analyze_search_console',
		'kb_link'     => KB::get( 'analysis-search-console' ),
		'tooltip'     => esc_html__( 'Confirm if Rank Math is connected to Search Console', 'rank-math' ),
	];

	$tests['sitemaps'] = [
		'category'    => 'advanced',
		'title'       => esc_html__( 'Sitemaps', 'rank-math' ),
		'description' => esc_html__( 'XML sitemaps are a special type of text file that tells search engines about the structure of your site. They\'re a list of all the resources (pages and files) you would like the search engine to index. You can assign different priorities, so certain pages will be crawled first. Before XML sitemaps, search engines were limited to indexing the content they could find by following links. That\'s still an important feature for search engine spiders, but XML sitemaps have made it easier for content creators and search engines to collaborate.', 'rank-math' ),
		'how_to_fix'  => esc_html__( 'If you don\'t have an XML sitemap, the best option is to install a plugin that creates sitemaps for you. That way you\'ll know the sitemap will always be up-to-date. Plugins can also automatically ping the search engines when the XML file is updated. The Rank Math WordPress plugin gives you complete control over your site\'s XML sitemaps. You can control the settings for each page as you write or edit it, and Rank Math will ping Google as soon as you submit your edits. This results in fast crawls and indexing.', 'rank-math' ),
		'callback'    => 'rank_math_analyze_sitemap',
		'kb_link'     => KB::get( 'analysis-sitemaps' ),
		'tooltip'     => esc_html__( 'Check the presence of sitemaps on your website', 'rank-math' ),
	];

	return $tests;
}

add_filter( 'rank_math/seo_analysis/tests', 'rank_math_register_seo_analysis_auto_update_test', 20 );

/**
 * Register test for the auto update option.
 *
 * @param array $tests Array of tests.
 *
 * @return array
 */
function rank_math_register_seo_analysis_auto_update_test( $tests ) {
	$new_tests = [
		'auto_update' => [
			'category'    => 'priority',
			'title'       => esc_html__( 'Automatic Updates', 'rank-math' ),
			'description' => esc_html__( 'Enable automatic updates to ensure you are always using the latest version of Rank Math.', 'rank-math' ),
			'callback'    => 'rank_math_analyze_auto_update',
			'kb_link'     => KB::get( 'analysis-auto-update' ),
			'tooltip'     => esc_html__( 'Verify auto-updates are enabled for Rank Math', 'rank-math' ),
		],
	];

	// Move to top.
	$tests = array_merge( $new_tests, $tests );

	return $tests;
}

/**
 * Checks if auto update is enabled.
 *
 * @return array
 */
function rank_math_analyze_auto_update() {
	if ( Helper::is_plugin_update_disabled() ) {
		return [
			'status'  => 'warning',
			'message' => __( 'Site wide plugins auto-update option is disabled on your site.', 'rank-math' ),
		];
	}

	if ( Helper::get_auto_update_setting() ) {
		return [
			'status'  => 'ok',
			'message' => __( 'Rank Math auto-update option is enabled on your site.', 'rank-math' ),
		];
	}

	return [
		'status'  => 'warning',
		'message' => '<div class="auto-update-disabled">' . sprintf(
			// Translators: placeholder is an activate button.
			__( 'Automatic updates are not enabled on your site. %s', 'rank-math' ),
			'<a href="#" class="button button-primary button-small enable-auto-update">' . __( 'Enable Auto Updates', 'rank-math' ) . '</a>'
		) . '</div>' .
		'<div class="auto-update-enabled hidden">' .
		esc_html__( 'Rank Math auto-update option is enabled on your site.', 'rank-math' ) .
		'</div>',
	];
}

/**
 * Checks if site description is empty or set to default.
 *
 * @return array
 */
function rank_math_analyze_site_description() {
	$sitedesc = get_bloginfo( 'description' );

	if ( '' === $sitedesc ) {
		return [
			'status'  => 'warning',
			'message' => sprintf(
				/* translators: %1$s link to the customize settings, %2$s closing tag for the link */
				esc_html__( 'You have not entered a tagline yet. It is a good idea to choose one. %1$sYou can fix this in the customizer%2$s.', 'rank-math' ),
				'<a href="' . esc_url( admin_url( 'customize.php' ) ) . '" target="_blank">',
				'</a>'
			),
		];
	}

	if ( rank_math_is_default_tagline() ) {
		return [
			'status'  => 'fail',
			'message' => wp_kses_post( __( 'Your Site Tagline is set to the default value <em>Just another WordPress site</em>.', 'rank-math' ) ),
		];
	}

	return [
		'status'  => 'ok',
		'message' => esc_html__( 'Your Site Tagline is set to a custom value.', 'rank-math' ),
	];
}

/**
 * Check if the site uses the default WP tagline.
 *
 * @return bool
 */
function rank_math_is_default_tagline() {
	$description            = get_bloginfo( 'description' );
	$translated_description = translate( 'Just another WordPress site' ); // phpcs:ignore

	if ( $description === $translated_description ) {
		return true;
	}

	// Also check untranslated version.
	return 'Just another WordPress site' === $description;
}

/**
 * Checks if pretty permalinks are enabled and if they contain %postname%.
 *
 * @return array
 */
function rank_math_analyze_permalink_structure() {
	$permalink_structure = get_option( 'permalink_structure' );

	if ( '' === $permalink_structure ) {
		return [
			'status'  => 'fail',
			'message' => wp_kses_post( __( 'Permalinks are set to the default value. <em>Pretty permalinks</em> are disabled. ', 'rank-math' ) ),
		];
	}

	if ( ! rank_math_is_postname_in_permalink() ) {
		return [
			'status'  => 'fail',
			'message' => wp_kses_post( __( 'Permalinks are set to a custom structure but the post titles do not appear in the permalinks.', 'rank-math' ) ),
		];
	}

	return [
		'status'  => 'ok',
		/* translators: permalink structure */
		'message' => sprintf( wp_kses_post( __( 'Post permalink structure is set to %s.', 'rank-math' ) ), '<code>' . $permalink_structure . '</code>' ),
	];
}

/**
 * Check if the post permalink includes %postname%.
 *
 * @return bool
 */
function rank_math_is_postname_in_permalink() {
	return ( false !== strpos( get_option( 'permalink_structure' ), '%postname%' ) );
}

/**
 * Checks if Search Console is linked.
 *
 * @return array
 */
function rank_math_analyze_search_console() {
	$status = Authentication::is_authorized() && Console::get_site_url();

	return [
		'status'  => $status ? 'ok' : 'fail',
		'message' => $status ? esc_html__( 'Google Search Console has been linked.', 'rank-math' ) : esc_html__( 'You have not linked Google Search Console yet.', 'rank-math' ),
	];
}

/**
 * Checks for posts without a focus keyword.
 *
 * @return array
 */
function rank_math_analyze_focus_keywords() {
	global $wpdb;

	$postmeta_table_limit = apply_filters( 'rank_math/seo_analysis/postmeta_table_limit', 200000 );
	if ( DB::table_size_exceeds( $wpdb->postmeta, $postmeta_table_limit ) ) {
		return [
			'status'  => 'warning',
			'message' => esc_html__( 'Could not check Focus Keywords in posts - the post meta table exceeds the size limit.', 'rank-math' ),
		];
	}

	$in_search_post_types = Helper::get_allowed_post_types();
	$in_search_post_types = empty( $in_search_post_types ) ? '' : " AND {$wpdb->posts}.post_type IN ('" . join( "', '", array_map( 'esc_sql', $in_search_post_types ) ) . "')";

	$meta_query = new WP_Meta_Query(
		[
			'relation' => 'AND',
			[
				'key'     => 'rank_math_focus_keyword',
				'compare' => 'NOT EXISTS',
			],
			[
				'relation' => 'OR',
				[
					'key'     => 'rank_math_robots',
					'value'   => 'noindex',
					'compare' => 'NOT LIKE',
				],
				[
					'key'     => 'rank_math_robots',
					'compare' => 'NOT EXISTS',
				],
			],
		]
	);

	$mq_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
	$query  = "SELECT {$wpdb->posts}.ID, {$wpdb->posts}.post_type FROM $wpdb->posts {$mq_sql['join']} WHERE 1 = 1 {$mq_sql['where']}{$in_search_post_types} AND ({$wpdb->posts}.post_status = 'publish') GROUP BY {$wpdb->posts}.ID";
	$data   = DB_Helper::get_results( $query, ARRAY_A );

	// Early Bail!
	if ( empty( $data ) ) {
		return [
			'status'  => 'ok',
			'message' => esc_html__( 'All published posts have focus keywords set.', 'rank-math' ),
		];
	}

	$rows  = rank_math_analyze_group_result( $data );
	$links = rank_math_get_post_type_links( $rows, '&focus_keyword=1' );

	return [
		'status'  => 'fail',
		/* translators: post type links */
		'message' => sprintf( esc_html__( 'There are %s with no focus keyword set.', 'rank-math' ), join( ', ', $links ) ),
	];
}

/**
 * Checks for posts where the focus keyword doesn't appear in the title.
 *
 * @return array
 */
function rank_math_analyze_post_titles() {
	$info = [];
	$data = rank_math_get_posts_with_titles();

	// Early Bail!
	if ( empty( $data ) ) {
		return [
			'status'  => 'ok',
			'message' => esc_html__( 'Focus keywords appear in the titles of published posts where it is set.', 'rank-math' ),
		];
	}

	$rows  = rank_math_analyze_group_result( $data );
	$links = rank_math_get_post_type_links( $rows, '&fk_in_title=1' );

	$post_ids     = wp_list_pluck( $data, 'ID' );
	$post_ids_max = array_slice( $post_ids, 0, 20 );
	foreach ( $post_ids_max as $postid ) {
		$info[] = '<a href="' . get_permalink( $postid ) . '" target="_blank">' . get_the_title( $postid ) . '</a>';
	}
	$count = count( $post_ids ) - 20;
	if ( $count > 0 ) {
		/* translators: post ID count */
		$info[] = sprintf( esc_html__( '+%d More...', 'rank-math' ), $count );
	}

	return [
		'status'  => 'fail',
		/* translators: post type links */
		'message' => sprintf( esc_html__( 'There are %s published posts where the primary focus keyword does not appear in the post title.', 'rank-math' ), join( ', ', $links ) ),
		'info'    => $info,
	];
}

/**
 * Get `post_type` links.
 *
 * @param array  $rows         Rows.
 * @param string $extra_params Extra parameters.
 *
 * @return array
 */
function rank_math_get_post_type_links( $rows, $extra_params ) {
	$links = [];
	foreach ( $rows as $post_type => $row ) {
		$post_type = get_post_type_object( $post_type );
		$count     = count( $row );
		$links[]   = sprintf(
			'<a href="%1$s%2$s" target="_blank">%3$d %4$s</a>',
			esc_url( admin_url( 'edit.php?post_type=' . $post_type->name ) ),
			$extra_params,
			$count,
			$count > 1 ? $post_type->label : $post_type->labels->singular_name
		);
	}

	return $links;
}

/**
 * Get posts not set to noindex where the Focus Keyword has been added.
 *
 * @return mixed
 */
function rank_math_get_posts_with_titles() {
	global $wpdb;

	$in_post_types = Helper::get_allowed_post_types();
	$in_post_types = empty( $in_post_types ) ? '' : " AND {$wpdb->posts}.post_type IN ('" . join( "', '", array_map( 'esc_sql', $in_post_types ) ) . "')";
	$meta_query    = new WP_Meta_Query(
		[
			'relation' => 'AND',
			[
				'key'     => 'rank_math_focus_keyword',
				'compare' => 'EXISTS',
			],
			[
				'relation' => 'OR',
				[
					'key'     => 'rank_math_robots',
					'value'   => 'noindex',
					'compare' => 'NOT LIKE',
				],
				[
					'key'     => 'rank_math_robots',
					'compare' => 'NOT EXISTS',
				],
			],
		]
	);

	$mq_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
	$query  = "SELECT {$wpdb->posts}.ID, {$wpdb->posts}.post_type FROM $wpdb->posts {$mq_sql['join']} WHERE 1=1 {$mq_sql['where']}{$in_post_types} AND ({$wpdb->posts}.post_status = 'publish') AND REPLACE( {$wpdb->posts}.post_title, '&amp;', '&' ) NOT LIKE CONCAT( '%', SUBSTRING_INDEX( {$wpdb->postmeta}.meta_value, ',', 1 ), '%' ) GROUP BY {$wpdb->posts}.ID";

	return DB_Helper::get_results( $query, ARRAY_A );
}

/**
 * Group result data by post type.
 *
 * @param array $data Result data.
 *
 * @return array
 */
function rank_math_analyze_group_result( $data ) {
	foreach ( $data as $val ) {
		$key            = array_key_exists( 'post_type', $val ) ? $val['post_type'] : '';
		$rows[ $key ][] = $val;
	}
	return $rows;
}

/**
 * Check if sitemap module is active.
 *
 * @return array
 */
function rank_math_analyze_sitemap() {

	$found = Helper::is_module_active( 'sitemap' );
	if ( ! $found ) {
		$sitemap = wp_remote_get( RankMath\Sitemap\Router::get_base_url( 'sitemap.xml' ) );
		$found   = isset( $sitemap['response'] ) && 200 === $sitemap['response']['code'];
	}

	return [
		'status'  => $found ? 'ok' : 'fail',
		'message' => $found ? esc_html__( 'Your site has one or more sitemaps.', 'rank-math' ) : esc_html__( 'No sitemaps found.', 'rank-math' ),
	];
}


/**
 * Check if the site is globally set to noindex.
 */
function rank_math_analyze_blog_public() {
	$info_message  = '<strong>' . esc_html__( 'Attention: Search Engines can\'t see your website.', 'rank-math' ) . '</strong> ';
	$info_message .= sprintf(
		/* translators: %1$s: opening tag of the link, %2$s: the closing tag */
		esc_html__( 'Navigate to %1$sSettings > Reading%2$s and turn off this option: "Discourage search engines from indexing this site".', 'rank-math' ),
		'<a href="' . esc_url( admin_url( 'options-reading.php' ) ) . '">',
		'</a>'
	);

	$public = (bool) get_option( 'blog_public' );
	return [
		'status'  => $public ? 'ok' : 'fail',
		'message' => $public ? esc_html__( 'Your site is accessible by search engine.', 'rank-math' ) : $info_message,
	];
}
