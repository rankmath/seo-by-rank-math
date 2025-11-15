/**
 * WordPress Dependencies
 */
import { __, sprintf } from '@wordpress/i18n'

export default ( data ) => {
	return [
		{
			id: 'site_type',
			type: 'select',
			name: sprintf(
				// translators: sitename
				__( '%1$s is a…', 'rank-math' ),
				rankMath.blogName
			),
			options: {
				blog: __( 'Personal Blog', 'rank-math' ),
				news: __( 'Community Blog/News Site', 'rank-math' ),
				portfolio: __( 'Personal Portfolio', 'rank-math' ),
				business: __( 'Small Business Site', 'rank-math' ),
				webshop: __( 'Webshop', 'rank-math' ),
				otherpersonal: __( 'Other Personal Website', 'rank-math' ),
				otherbusiness: __( 'Other Business Website', 'rank-math' ),
			},
		},
		{
			id: 'business_type',
			type: 'select_search',
			name: __( 'Business Type', 'rank-math' ),
			desc: __(
				'Select the type that best describes your business. If you can\'t find one that applies exactly, use the generic "Organization" or "Local Business" types.',
				'rank-math'
			),
			options: data.businessTypesChoices,
			dep: {
				site_type: [ 'news', 'buisness', 'webshop', 'otherbusiness' ],
			},
		},
		{
			id: 'website_name',
			type: 'text',
			name: __( 'Website Name', 'rank-math' ),
			desc: __(
				'Enter the name of your site to appear in search results.',
				'rank-math'
			),
		},
		{
			id: 'website_alternate_name',
			type: 'text',
			name: __( 'Website Alternate Name', 'rank-math' ),
			desc: __(
				'An alternate version of your site name (for example, an acronym or shorter name).',
				'rank-math'
			),
		},
		{
			id: 'company_name',
			type: 'text',
			name: __( 'Person/Organization Name', 'rank-math' ),
			desc: __(
				"Your name or company name intended to feature in Google's Knowledge Panel.",
				'rank-math'
			),
		},
		{
			id: 'company_logo',
			type: 'file',
			name: __( 'Logo for Google', 'rank-math' ),
			description: __(
				'<strong>Min Size: 112Χ112px</strong>.<br />A squared image is preferred by the search engines.',
				'rank-math'
			),
		},
		{
			id: 'open_graph_image',
			type: 'file',
			name: __( 'Default Social Share Image', 'rank-math' ),
			description: __(
				'When a featured image or an OpenGraph Image is not set for individual posts/pages/CPTs, this image will be used as a fallback thumbnail when your post is shared on Facebook. <strong>The recommended image size is 1200 x 630 pixels.</strong>',
				'rank-math'
			),
		},
	]
}
