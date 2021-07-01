/**
 * External dependencies
 */
import { map, isUndefined } from 'lodash'
import { withRouter } from 'react-router-dom'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'
import { withFilters } from '@wordpress/components'
import { withSelect, dispatch } from '@wordpress/data'

/**
 * Internal dependencies
 */
import humanNumber from '@helpers/humanNumber'
import TableCard from '@scShared/woocommerce/Table'
import { processRows, getPageOffset, filterShownHeaders } from '../functions'

const TABLE_PREF_KEY = 'performance'

const PostsTable = ( props ) => {
	const { tableData, summary, query, history, userPreference } = props
	if ( isUndefined( tableData ) || isUndefined( summary ) ) {
		return 'Loading'
	}

	const headers = applyFilters(
		'rankMath.analytics.performanceHeaders',
		[
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
				key: 'impressions',
				label: __( 'Impressions', 'rank-math' ),
				cellClassName: 'rank-math-col-impressions',
			},
			{
				key: 'clicks',
				label: __( 'Clicks', 'rank-math' ),
				cellClassName: 'rank-math-col-click',
			},
			{
				key: 'ctr',
				label: __( 'Avg. CTR', 'rank-math' ),
				cellClassName: 'rank-math-col-ctr',
			},
			{
				key: 'position',
				label: __( 'Position', 'rank-math' ),
				cellClassName: 'rank-math-col-position',
			},
		]
	)

	const tableSummary = applyFilters(
		'rankMath.analytics.performanceTableSummary',
		[
			{ label: __( 'Posts', 'rank-math' ), value: tableData.rowsFound },
			{
				label: __( 'Search Impressions', 'rank-math' ),
				value: humanNumber( summary.impressions ),
			},
			{
				label: __( 'Search Clicks', 'rank-math' ),
				value: humanNumber( summary.clicks ),
			},
		],
		summary
	)

	const rowsPerPage = 25
	const { paged = 1 } = query
	const filteredHeaders = filterShownHeaders( headers, userPreference )
	const onColumnsChange = ( columns, toggled ) => {
		userPreference[ toggled ] = ! userPreference[ toggled ]
		dispatch( 'rank-math' ).updateUserPreferences(
			userPreference,
			TABLE_PREF_KEY
		)
	}

	return (
		<div className="rank-math-posts">
			<TableCard
				className="rank-math-table"
				title={ __( 'Content', 'rank-math' ) }
				headers={ filteredHeaders }
				downloadable={ true }
				rowsPerPage={ rowsPerPage }
				rows={ processRows(
					tableData.rows,
					map( headers, 'key' ),
					getPageOffset( paged, rowsPerPage )
				) }
				query={ query }
				totalRows={ parseInt( tableData.rowsFound ) }
				summary={ tableSummary }
				showPageArrowsLabel={ false }
				onPageChange={ ( newPage ) => {
					history.push( '/performance/' + newPage )
				} }
				onQueryChange={ () => () => {} }
				onColumnsChange={ onColumnsChange }
			/>
		</div>
	)
}

export default withRouter(
	withFilters( 'rankMath.analytics.postsTable' )(
		withSelect( ( select, props ) => {
			const query = props.match.params
			const { paged = 1 } = query

			return {
				query,
				history: props.history,
				tableData: select( 'rank-math' ).getPostsRowsByObjects( paged, {} ),
				summary: select( 'rank-math' ).getPostsSummary(),
				userPreference: select( 'rank-math' ).getUserColumnPreference(
					TABLE_PREF_KEY
				),
			}
		} )( PostsTable )
	)
)
