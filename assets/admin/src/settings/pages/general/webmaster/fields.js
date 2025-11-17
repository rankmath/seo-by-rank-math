/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import createLink from '../../../helpers/createLink'

export default [
	{
		id: 'google_verify',
		type: 'text',
		name: __( 'Google Search Console', 'rank-math' ),
		desc:
			sprintf(
				// translators: Google Search Console Link
				__(
					'Enter your Google Search Console verification HTML code or ID. Learn how to get it: %s',
					'rank-math'
				),
				createLink(
					'google-verification-kb',
					'Google Verification Tool',
					__( 'Search Console Verification Page', 'rank-math' )
				)
			) +
			'<br><code>&lt;meta name="google-site-verification" content="your-id" /&gt;</code>',
	},
	{
		id: 'bing_verify',
		type: 'text',
		name: __( 'Bing Webmaster Tools', 'rank-math' ),
		desc:
			sprintf(
				// translators: Bing webmaster link
				__(
					'Enter your Bing Webmaster Tools verification HTML code or ID. Get it here: %s',
					'rank-math'
				),
				createLink(
					'bing-verification-kb',
					'Bing Verification Tool',
					__( 'Bing Webmaster Verification Page', 'rank-math' )
				)
			) +
			'<br><code>&lt;meta name="msvalidate.01" content="your-id" /&gt;</code>',
	},
	{
		id: 'baidu_verify',
		type: 'text',
		name: __( 'Baidu Webmaster Tools', 'rank-math' ),
		desc:
			sprintf(
				// translators: Baidu webmaster link
				__(
					'Enter your Baidu Webmaster Tools verification HTML code or ID. Learn how to get it: %s',
					'rank-math'
				),
				createLink(
					'baidu-verification-kb',
					'Baidu Verification Tool',
					__( 'Baidu Webmaster Tools', 'rank-math' )
				)
			) +
			'<br><code>&lt;meta name="baidu-site-verification" content="your-id" /&gt;</code>',
	},
	{
		id: 'yandex_verify',
		type: 'text',
		name: __( 'Yandex Verfication ID', 'rank-math' ),
		desc:
			sprintf(
				// translators: Yandex webmaster link
				__(
					'Enter your Yandex verification HTML code or ID. Learn how to get it: %s',
					'rank-math'
				),
				createLink(
					'yandex-verification-kb',
					'Yandex Verification Tool',
					__( 'Yandex.Webmaster Page', 'rank-math' )
				)
			) +
			'<br><code>&lt;meta name="yandex-verification" content="your-id" /&gt;</code>',
		classes: 'rank-math-advanced-option',
	},
	{
		id: 'pinterest_verify',
		type: 'text',
		name: __( 'Pinterest Verification ID', 'rank-math' ),
		desc:
			sprintf(
				// translators: Pinterest webmaster link
				__(
					'Enter your Pinterest verification HTML code or ID. Learn how to get it: %s',
					'rank-math'
				),
				createLink(
					'pinterest-verification-kb',
					'Pinterest Verification Tool',
					__( 'Pinterest Account', 'rank-math' )
				)
			) +
			'<br><code>&lt;meta name="p:domain_verify" content="your-id" /&gt;</code>',
	},
	{
		id: 'norton_verify',
		type: 'text',
		name: __( 'Norton Safe Web Verification ID', 'rank-math' ),
		desc:
			sprintf(
				// translators: Norton webmaster link
				__(
					'Enter your Norton Safe Web verification HTML code or ID. Learn how to get it: %s',
					'rank-math'
				),
				createLink(
					'norton-verification-kb',
					'Norton Verification Tool',
					__( 'Norton Ownership Verification Page', 'rank-math' )
				)
			) +
			'<br><code>&lt;meta name="norton-safeweb-site-verification" content="your-id" /&gt;</code>',
		classes: 'rank-math-advanced-option',
	},
	{
		id: 'custom_webmaster_tags',
		type: 'textarea',
		name: __( 'Custom Webmaster Tags', 'rank-math' ),
		desc: sprintf(
			// translators: %s: Allowed tags
			__(
				'Enter your custom webmaster tags. Only %s tags are allowed.',
				'rank-math'
			),
			'<code>&lt;meta&gt;</code>'
		),
		classes: 'rank-math-advanced-option',
		rows: 10,
	},
]
