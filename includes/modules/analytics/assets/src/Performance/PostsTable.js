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

/**
 * Internal dependencies
 */
import humanNumber from '@helpers/humanNumber'
import TableCard from '@scShared/woocommerce/Table'
import { processRows, getPageOffset } from '../functions'

const PostsTable = ( props ) => {
	const { tableData, summary, query, history } = props
	if ( isUndefined( tableData ) || isUndefined( summary ) ) {
		return 'Loading'
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
			key: 'pageviews',
			label: __( 'Search Traffic', 'rank-math' ),
			cellClassName: 'rank-math-col-pageviews',
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
			label: __( 'CTR', 'rank-math' ),
			cellClassName: 'rank-math-col-ctr',
		},
		{
			key: 'position',
			label: __( 'Position', 'rank-math' ),
			cellClassName: 'rank-math-col-position',
		},
	]

	const tableSummary = [
		{ label: __( 'Posts', 'rank-math' ), value: tableData.rowsFound },
		{
			label: __( 'Search Impressions', 'rank-math' ),
			value: humanNumber( summary.impressions ),
		},
		{
			label: __( 'Search Traffic', 'rank-math' ),
			value: humanNumber( summary.pageviews ),
		},
		{
			label: __( 'Search Clicks', 'rank-math' ),
			value: humanNumber( summary.clicks ),
		},
	]

	const rowsPerPage = 25
	const { paged = 1 } = query

	return (
		<div className="rank-math-posts">
			<TableCard
				className="rank-math-table"
				title={ __( 'Content', 'rank-math' ) }
				headers={ headers }
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
			/>
		</div>
	)
}

export default withRouter(
	withSelect( ( select, props ) => {
		const query = props.match.params
		const { paged = 1 } = query

		return {
			query,
			history: props.history,
			tableData: select( 'rank-math' ).getPostsRows( paged, {} ),
			summary: select( 'rank-math' ).getPostsSummary(),
		}
	} )( PostsTable )
)
