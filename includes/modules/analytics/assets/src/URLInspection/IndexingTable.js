/**
 * External dependencies
 */
import { map, filter, isUndefined, isEmpty } from 'lodash'
import { TableCard } from '@woocommerce/components'

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
import { processRows, getPageOffset, filterShownHeaders, withRouter } from '../functions'

const TABLE_PREF_KEY = 'indexing'

const IndexingTable = ( props ) => {
	const { tableData, summary, query, navigate, userPreference } = props
	if ( isUndefined( tableData ) || isUndefined( summary ) ) {
		return 'Loading'
	}

	const postsRows = isUndefined( tableData.rows ) || 'No Data' === tableData.rows.response ? [] : tableData.rows
	if ( ! isUndefined( userPreference.index_verdict ) ) {
		userPreference.index_verdict = true
	}

	const headers = applyFilters(
		'rankMath.analytics.indexingHeaders',
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
				key: 'index_verdict',
				label: __( 'Status', 'rank-math' ),
				cellClassName: 'rank-math-col-status',
				isReadOnly: true,
			},
			{
				key: 'indexing_state',
				label: __( 'Indexing Allowed', 'rank-math' ),
				cellClassName: 'rank-math-col-indexing-allowed',
			},
			{
				key: 'rich_results_items',
				label: __( 'Rich Results', 'rank-math' ),
				cellClassName: 'rank-math-col-rich-results',
			},
			{
				key: 'page_fetch_state',
				label: __( 'Page Fetch', 'rank-math' ),
				cellClassName: 'rank-math-col-page-fetch',
			},
			{
				key: 'crawled_as',
				label: __( 'Crawled As [PRO]', 'rank-math' ),
				cellClassName: 'rank-math-col-crawled-as',
				disabled: true,
			},
			{
				key: 'robots_txt_state',
				label: __( 'Robots state [PRO]', 'rank-math' ),
				cellClassName: 'rank-math-col-robots-state',
				disabled: true,
			},
		]
	)

	const tableSummary = applyFilters(
		'rankMath.analytics.indexingSummary',
		[
			{ label: __( 'Posts', 'rank-math' ), value: tableData.rowsFound },
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
					postsRows,
					filter( map( headers, ( header ) => header.disabled ? '' : header.key ) ),
					getPageOffset( paged, rowsPerPage )
				) }
				isLoading={ isEmpty( tableData.rows ) }
				query={ query }
				totalRows={ parseInt( tableData.rowsFound ) }
				summary={ tableSummary }
				showPageArrowsLabel={ false }
				onPageChange={ ( newPage ) => {
					navigate( '/indexing/' + newPage )
				} }
				onQueryChange={ () => () => {} }
				onColumnsChange={ onColumnsChange }
				indexingData={ postsRows }
			/>
		</div>
	)
}

export default withRouter(
	withFilters( 'rankMath.analytics.indexingTable' )(
		withSelect( ( select, props ) => {
			const query = props.params
			const { paged = 1 } = query
			return {
				query,
				navigate: props.navigate,
				tableData: select( 'rank-math' ).getIndexingReport( paged, {} ),
				summary: select( 'rank-math' ).getPostsSummary(),
				userPreference: select( 'rank-math' ).getUserColumnPreference( TABLE_PREF_KEY ),
			}
		} )( IndexingTable )
	)
)
