/**
 * External dependencies
 */
import { map, isUndefined } from 'lodash'
import { withRouter } from 'react-router-dom'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { withSelect } from '@wordpress/data'
import { withFilters } from '@wordpress/components'

/**
 * Internal dependencies
 */
import { processRows } from '../functions'
import humanNumber from '@helpers/humanNumber'
import TableCard from '@scShared/woocommerce/Table'

const PostsTable = ( props ) => {
	const { summary, seoScores } = props
	if ( isUndefined( summary ) || isUndefined( seoScores ) ) {
		return 'Loading'
	}

	const tableSummary = [
		{ label: __( 'Posts', 'rank-math' ), value: seoScores.total },
		{
			label: __( 'Search Impressions', 'rank-math' ),
			value: humanNumber( summary.impressions ),
		},
		{
			label: __( 'Search Clicks', 'rank-math' ),
			value: humanNumber( summary.clicks ),
		},
	]

	const rows = {
		'power-words': {
			id: 29,
			title: 'Power Words: The Art of Writing Headlines That Get Clicked',
			page: '/blog/power-words',
			object_type: 'post',
			object_subtype: 'post',
			object_id: '18',
			seo_score: '26',
			schemas_in_use: '',
			links: {
				internal: 3,
				external: 2,
				incoming: 1,
			},
			pageviews: {
				total: 4340,
				difference: 102,
			},
		},
		'howto-schema': {
			id: 4,
			title: 'How to Add "HowTo Schema" to your Website With Rank Math',
			page: '/blog/howto-schema',
			object_type: 'post',
			object_subtype: 'post',
			object_id: 154,
			seo_score: 88,
			schemas_in_use: 'Article',
			links: {
				internal: 2,
				external: 3,
				incoming: 5,
			},
			pageviews: {
				total: 2043,
				difference: 523,
			},
		},
		'/blog/faq-schema': {
			id: 31,
			title: 'FAQ Schema: A Practicla (and EASY) Guide',
			page: '/blog/faq-schema',
			object_type: 'post',
			object_subtype: 'post',
			object_id: 12,
			seo_score: 76,
			schemas_in_use: 'Article, MusicGroup',
			links: {
				internal: 4,
				external: 1,
				incoming: 2,
			},
			pageviews: {
				total: 809,
				difference: -49,
			},
		},
		'/blog/elementor-seo': {
			id: 29,
			title: "Elementor SEO: THe Solutions you've All Been Waiting For",
			page: '/blog/elementor-seo',
			object_type: 'post',
			object_subtype: 'post',
			object_id: '18',
			seo_score: '26',
			schemas_in_use: '',
			links: {
				internal: 1,
				external: 6,
				incoming: 3,
			},
			pageviews: {
				total: 1033,
				difference: 285,
			},
		},
		'/blog/seo-elements': {
			id: 4,
			title: 'Are You Missing These SEO Elements on Your WordPress Website?',
			page: '/blog/seo-elements',
			object_type: 'post',
			object_subtype: 'post',
			object_id: 154,
			seo_score: 88,
			schemas_in_use: 'Article',
			links: {
				internal: 5,
				external: 0,
				incoming: 2,
			},
			pageviews: {
				total: 3928,
				difference: 423,
			},
		},
	}

	const headers = [
		{
			key: 'sequence',
			label: __( '#', 'rank-math' ),
			required: true,
			cellClassName: 'rank-math-col-index',
		},
		{
			key: 'title',
			label: __( 'Title', 'rank-math' ),
			required: true,
			cellClassName: 'rank-math-col-title',
		},
		{
			key: 'seo_score',
			label: __( 'SEO Score', 'rank-math' ),
			cellClassName: 'rank-math-col-score',
		},
		{
			key: 'schemas_in_use',
			label: __( 'Schema', 'rank-math' ),
			cellClassName: 'rank-math-col-schema',
		},
		{
			key: 'links',
			label: __( 'Links', 'rank-math' ),
			required: true,
			cellClassName: 'rank-math-col-links',
		},
		{
			key: 'pageviews',
			label: __( 'Traffic', 'rank-math' ),
			cellClassName: 'rank-math-col-pageviews',
		},
	]

	return (
		<div className="rank-math-posts">
			<div id="rank-math-pro-cta" className="center">
				<div className="rank-math-cta-box blue-ticks top-20 width-50">
					<h3>{ __( 'Prioritize Your Content Efforts With Detailed Insights', 'rank-math' ) }</h3>
					<ul>
						<li>{ __( 'All the statistics about your content all in one place', 'rank-math' ) }</li>
						<li>{ __( 'Monitor key metrics like traffic and search performance', 'rank-math' ) }</li>
						<li>{ __( 'Use data provided by Google instead of 3rd party tools', 'rank-math' ) }</li>
					</ul>
					<a href="https://rankmath.com/pricing/?utm_source=Plugin&utm_medium=Analytics%20Tab%20Table&utm_campaign=WP" target="_blank" rel="noreferrer" className="button button-primary is-green">{ __( 'Upgrade', 'rank-math' ) }</a>
				</div>
			</div>
			<TableCard
				className="rank-math-table"
				title={ __( 'Content', 'rank-math' ) }
				headers={ headers }
				downloadable={ true }
				rowsPerPage={ 25 }
				rows={ processRows( rows, map( headers, 'key' ) ) }
				totalRows={ 50 }
				summary={ tableSummary }
				showPageArrowsLabel={ false }
			/>
		</div>
	)
}

export default withRouter( withFilters( 'rankMath.analytics.siteAnalyticsTable' )(
	withSelect( ( select ) => {
		const { summary } = select( 'rank-math' ).getAnalyticsSummary()

		return {
			summary,
			seoScores: select( 'rank-math' ).getDashboardStats(
				select( 'rank-math' ).getDaysRange()
			).optimization,
		}
	} )( PostsTable )
) )
