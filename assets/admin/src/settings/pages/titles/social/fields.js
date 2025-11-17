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
		id: 'social_url_facebook',
		type: 'text',
		name: __( 'Facebook Page URL', 'rank-math' ),
		desc:
			__( 'Enter your complete Facebook page URL here. eg:', 'rank-math' ) +
			`<br><code>https://www.facebook.com/RankMath/</code>`,
	},
	{
		id: 'facebook_author_urls',
		type: 'text',
		name: __( 'Facebook Authorship', 'rank-math' ),
		desc:
			__(
				'Insert personal Facebook profile URL to show Facebook Authorship when your articles are being shared on Facebook. eg:',
				'rank-math'
			) + `<br><code>https://www.facebook.com/zuck</code>`,
	},
	{
		id: 'facebook_admin_id',
		type: 'text',
		name: __( 'Facebook Admin', 'rank-math' ),
		desc: sprintf(
			/* translators: numeric user ID link */
			__(
				'Enter %s. Use a comma to separate multiple IDs. Alternatively, you can enter an app ID below.',
				'rank-math'
			),
			`<a href="https://lookup-id.com/?utm_campaign=Rank+Math" target="_blank">numeric user ID</a>`
		),
	},
	{
		id: 'facebook_app_id',
		type: 'text',
		name: __( 'Facebook App', 'rank-math' ),
		desc: sprintf(
			/* translators: numeric app ID link */
			__(
				'Enter %s. Alternatively, you can enter a user ID above.',
				'rank-math'
			),
			`<a href="https://developers.facebook.com/apps?utm_campaign=Rank+Math" target="_blank">numeric app ID</a>`
		),
	},
	{
		id: 'facebook_secret',
		type: 'text',
		name: __( 'Facebook Secret', 'rank-math' ),
		desc: sprintf(
			/* translators: Learn more link */
			__( 'Enter alphanumeric secret ID. %s.', 'rank-math' ),
			createLink( 'create-facebook-app', '', __( 'Learn more', 'rank-math' ) )
		),
		attributes: {
			type: 'password',
		},
	},
	{
		id: 'twitter_author_names',
		type: 'text',
		name: __( 'Twitter Username', 'rank-math' ),
		desc: __(
			'Enter the Twitter username of the author to add <code>twitter:creator</code> tag to posts. eg: <code>RankMathSEO</code>',
			'rank-math'
		),
	},
	{
		id: 'social_additional_profiles',
		type: 'textarea',
		name: __( 'Additional Profiles', 'rank-math' ),
		desc: __(
			'Additional Profiles to add in the <code>sameAs</code> Schema property.',
			'rank-math'
		),
	},
]
