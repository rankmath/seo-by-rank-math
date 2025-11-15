/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { RawHTML } from '@wordpress/element'

/**
 * Internal dependencies
 */
import getAdminURL from '../../../helpers/getAdminURL'
import appData from '../../../helpers/appData'

export default ( postType ) => {
	const fields = []
	const prefix = `pt_${ postType }_`
	const sitemapUrl = rankMath[ postType ].sitemapUrl

	let disabled = false

	const isAttachment = postType === 'attachment'

	fields.push( {
		type: 'notice',
		status: 'info',
		children: (
			<RawHTML>
				{ ! isAttachment
					? sprintf(
						/* translators: Post Type Sitemap Url */
						__( 'Sitemap URL: %s', 'rank-math' ),
						`<a href="${ sitemapUrl }" target="_blank">${ sitemapUrl }</a>`
					)
					: __(
						'Please note that this will add the attachment page URLs to the sitemap, not direct image URLs.',
						'rank-math'
					) }
			</RawHTML>
		),
	} )

	if ( isAttachment && rankMath.isRedirectAttachment ) {
		disabled = true

		fields.push( {
			id: 'attachment_redirect_urls_notice',
			type: 'notice',
			status: 'warning',
			children: (
				<RawHTML>
					{ sprintf(
						/* translators: The settings page link */
						__(
							'To configure meta tags for your media attachment pages, you need to first %s to parent.',
							'rank-math'
						),
						`<a href="${ getAdminURL(
							'options-general'
						) }">${ __( 'disable redirect attachments', 'rank-math' ) }</a>`
					) }
				</RawHTML>
			),
		} )
	}

	fields.push( {
		id: prefix + 'sitemap',
		type: 'toggle',
		name: __( 'Include in Sitemap', 'rank-math' ),
		desc: __( 'Include this post type in the XML sitemap.', 'rank-math' ),
		default: isAttachment ? false : true,
		disabled,
	} )

	fields.push( {
		id: prefix + 'html_sitemap',
		type: 'toggle',
		name: __( 'Include in HTML Sitemap', 'rank-math' ),
		desc: __(
			"Include this post type in the HTML sitemap if it's enabled.",
			'rank-math'
		),
		classes: `rank-math-html-sitemap ${ ! appData.html_sitemap ? 'hidden' : '' }`,
		default: isAttachment ? false : true,
		disabled,
	} )

	if ( ! isAttachment ) {
		fields.push( {
			id: prefix + 'image_customfields',
			type: 'textarea',
			name: __( 'Image Custom Fields', 'rank-math' ),
			desc: __(
				'Insert custom field (post meta) names which contain image URLs to include them in the sitemaps. Add one per line.',
				'rank-math'
			),
			dep: {
				[ prefix + 'sitemap' ]: true,
			},
			classes: 'rank-math-advanced-option',
		} )
	}

	return fields
}
