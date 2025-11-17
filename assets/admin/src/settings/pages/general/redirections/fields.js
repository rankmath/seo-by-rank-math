/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

export default [
	{
		id: 'redirections_debug',
		type: 'toggle',
		name: __( 'Debug Redirections', 'rank-math' ),
		desc: __(
			'Display the Debug Console instead of being redirected. Administrators only.',
			'rank-math'
		),
		default: false,
	},
	{
		id: 'redirections_fallback',
		type: 'toggleGroup',
		name: __( 'Fallback Behavior', 'rank-math' ),
		desc: __(
			'If nothing similar is found, this behavior will be applied. <strong>Note</strong>: If the requested URL ends with <code>/login</code>, <code>/admin</code>, or <code>/dashboard</code>, WordPress will automatically redirect to respective locations within the WordPress admin area.',
			'rank-math'
		),
		options: {
			default: __( 'Default 404', 'rank-math' ),
			homepage: __( 'Redirect to Homepage', 'rank-math' ),
			custom: __( 'Custom Redirection', 'rank-math' ),
		},
		default: 'default',

	},
	{
		id: 'redirections_custom_url',
		type: 'text',
		name: __( 'Custom Url', 'rank-math' ),
		dep: {
			redirections_fallback: 'custom',
		},
	},
	{
		id: 'redirections_header_code',
		type: 'select',
		name: __( 'Redirection Type', 'rank-math' ),
		options: rankMath.redirectionTypes,
		default: '301',
	},
	{
		id: 'redirections_post_redirect',
		type: 'toggle',
		name: __( 'Auto Post Redirect', 'rank-math' ),
		desc: __(
			'Extend the functionality of WordPress by creating redirects in our plugin when you change the slug of a post, page, category or a CPT. You can modify the redirection further according to your needs.',
			'rank-math'
		),
		default: false,
	},
]
