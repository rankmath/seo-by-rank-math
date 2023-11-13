<?php
/**
 * The webmaster settings.
 *
 * @package    RankMath
 * @subpackage RankMath\Settings
 */

defined( 'ABSPATH' ) || exit;

use RankMath\KB;

$cmb->add_field(
	[
		'id'              => 'google_verify',
		'type'            => 'text',
		'name'            => esc_html__( 'Google Search Console', 'rank-math' ),
		/* translators: Google Search Console Link */
		'desc'            => sprintf( esc_html__( 'Enter your Google Search Console verification HTML code or ID. Learn how to get it: %s', 'rank-math' ), '<a href="' . KB::get( 'google-verification-kb', 'Google Verification Tool' ) . '" target="_blank">' . esc_html__( 'Search Console Verification Page', 'rank-math' ) . '</a>' ) .
			'<br><code>' . htmlspecialchars( '<meta name="google-site-verification" content="your-id" />' ) . '</code>',
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_webmaster_tags' ],
	]
);

$cmb->add_field(
	[
		'id'              => 'bing_verify',
		'type'            => 'text',
		'name'            => esc_html__( 'Bing Webmaster Tools', 'rank-math' ),
		/* translators: Bing webmaster link */
		'desc'            => sprintf( esc_html__( 'Enter your Bing Webmaster Tools verification HTML code or ID. Get it here: %s', 'rank-math' ), '<a href="' . KB::get( 'bing-verification-kb', 'Bing Verification Tool' ) . '" target="_blank">' . esc_html__( 'Bing Webmaster Verification Page', 'rank-math' ) . '</a>' ) .
			'<br><code>' . htmlspecialchars( '<meta name="msvalidate.01" content="your-id" />' ) . '</code>',
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_webmaster_tags' ],
	]
);

$cmb->add_field(
	[
		'id'              => 'baidu_verify',
		'type'            => 'text',
		'name'            => esc_html__( 'Baidu Webmaster Tools', 'rank-math' ),
		/* translators: Baidu webmaster link */
		'desc'            => sprintf( esc_html__( 'Enter your Baidu Webmaster Tools verification HTML code or ID. Learn how to get it: %s', 'rank-math' ), '<a href="' . KB::get( 'baidu-verification-kb', 'Baidu Verification Tool' ) . '" target="_blank">' . esc_html__( 'Baidu Webmaster Tools', 'rank-math' ) . '</a>' ) .
			'<br><code>' . htmlspecialchars( '<meta name="baidu-site-verification" content="your-id" />' ) . '</code>',
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_webmaster_tags' ],
	]
);

$cmb->add_field(
	[
		'id'              => 'yandex_verify',
		'type'            => 'text',
		'name'            => esc_html__( 'Yandex Verification ID', 'rank-math' ),
		/* translators: Yandex webmaster link */
		'desc'            => sprintf( esc_html__( 'Enter your Yandex verification HTML code or ID. Learn how to get it: %s', 'rank-math' ), '<a href="' . KB::get( 'yandex-verification-kb', 'Yandex Verification Tool' ) . '" target="_blank">' . esc_html__( 'Yandex.Webmaster Page', 'rank-math' ) . '</a>' ) .
			'<br><code>' . htmlspecialchars( '<meta name=\'yandex-verification\' content=\'your-id\' />' ) . '</code>',
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_webmaster_tags' ],
		'classes'         => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'              => 'pinterest_verify',
		'type'            => 'text',
		'name'            => esc_html__( 'Pinterest Verification ID', 'rank-math' ),
		/* translators: Pinterest webmaster link */
		'desc'            => sprintf( esc_html__( 'Enter your Pinterest verification HTML code or ID. Learn how to get it: %s', 'rank-math' ), '<a href="' . KB::get( 'pinterest-verification-kb', 'Pinterest Verification Tool' ) . '" target="_blank">' . esc_html__( 'Pinterest Account', 'rank-math' ) . '</a>' ) .
			'<br><code>' . htmlspecialchars( '<meta name="p:domain_verify" content="your-id" />' ) . '</code>',
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_webmaster_tags' ],
	]
);

$cmb->add_field(
	[
		'id'              => 'norton_verify',
		'type'            => 'text',
		'name'            => esc_html__( 'Norton Safe Web Verification ID', 'rank-math' ),
		/* translators: Norton webmaster link */
		'desc'            => sprintf( esc_html__( 'Enter your Norton Safe Web verification HTML code or ID. Learn how to get it: %s', 'rank-math' ), '<a href="' . KB::get( 'norton-verification-kb', 'Norton Verification Tool' ) . '" target="_blank">' . esc_html__( 'Norton Ownership Verification Page', 'rank-math' ) . '</a>' ) .
			'<br><code>' . htmlspecialchars( '<meta name="norton-safeweb-site-verification" content="your-id" />' ) . '</code>',
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_webmaster_tags' ],
		'classes'         => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'              => 'custom_webmaster_tags',
		'type'            => 'textarea',
		'name'            => esc_html__( 'Custom Webmaster Tags', 'rank-math' ),
		'desc'            => sprintf(
			/* translators: %s: Allowed tags */
			esc_html__( 'Enter your custom webmaster tags. Only %s tags are allowed.', 'rank-math' ),
			'<code>&lt;meta&gt;</code>'
		),
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_custom_webmaster_tags' ],
		'classes'         => 'rank-math-advanced-option',
	]
);
