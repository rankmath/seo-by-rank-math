/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { RawHTML } from '@wordpress/element'

/**
 * Internal dependencies
 */
import appData from '../../../helpers/appData'

export default ( taxonomy ) => {
	const prefix = `tax_${ taxonomy }_`
	const sitemapUrl = rankMath[ taxonomy ].sitemapUrl
	const isCategory = taxonomy === 'category'

	return [
		{
			type: 'notice',
			status: 'info',
			children: (
				<RawHTML>
					{ sprintf(
						/* translators: Taxonomy Sitemap Url */
						__( 'Sitemap URL: %s', 'rank-math' ),
						`<a href="${ sitemapUrl }" target="_blank">${ sitemapUrl }</a>`
					) }
				</RawHTML>
			),
		},
		{
			id: prefix + 'sitemap',
			type: 'toggle',
			name: __( 'Include in Sitemap', 'rank-math' ),
			desc: __(
				'Include archive pages for terms of this taxonomy in the XML sitemap.',
				'rank-math'
			),
			default: isCategory ? true : false,
		},
		{
			id: prefix + 'html_sitemap',
			type: 'toggle',
			name: __( 'Include in HTML Sitemap', 'rank-math' ),
			desc: __(
				'Include archive pages for terms of this taxonomy in the HTML sitemap.',
				'rank-math'
			),
			classes: `rank-math-html-sitemap ${ ! appData.html_sitemap ? 'hidden' : '' }`,
			default: isCategory ? true : false,
		},
		{
			id: prefix + 'include_empty',
			type: 'toggle',
			name: __( 'Include Empty Terms', 'rank-math' ),
			desc: __(
				'Include archive pages of terms that have no posts associated.',
				'rank-math'
			),
			dep: {
				[ prefix + 'sitemap' ]: true,
			},
			default: false,
			classes: 'rank-math-advanced-option',
		},
	]
}
