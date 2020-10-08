/**
 * External dependencies
 */
import { isUndefined, isEmpty, map, get } from 'lodash'
import { withRouter } from 'react-router-dom'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment } from '@wordpress/element'
import { dispatch, withSelect } from '@wordpress/data'

/**
 * Internal dependencies
 */
import humanNumber from '@helpers/humanNumber'
import TableCard from '@scShared/woocommerce/Table'
import { processRows, getPageOffset, filterShownHeaders } from '../functions'

const TABLE_PREF_KEY = 'keywords'

const KeywordsTable = ( props ) => {
	const { rows, summary, query, history, userPreference } = props
	if ( isUndefined( rows ) || isUndefined( summary ) ) {
		return 'Loading'
	}

	const headers = [
		{
			key: 'sequenceAdd',
			label: __( '#', 'rank-math' ),
			required: true,
			cellClassName: 'rank-math-col-index',
		},
		{
			key: 'query',
			label: __( 'Keywords', 'rank-math' ),
			required: true,
			cellClassName: 'rank-math-col-query',
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
		{
			label: __( 'Keywords', 'rank-math' ),
			value: get( summary, [ 'keywords', 'total' ], 0 ),
		},
		{
			label: __( 'Search Impressions', 'rank-math' ),
			value: humanNumber( get( summary, [ 'impressions', 'total' ], 0 ) ),
		},
		{
			label: __( 'Avg. CTR', 'rank-math' ),
			value: humanNumber( get( summary, [ 'ctr', 'total' ], 0 ) ),
		},
		{
			label: __( 'Search Clicks', 'rank-math' ),
			value: humanNumber( get( summary, [ 'clicks', 'total' ], 0 ) ),
		},
	]

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
	const tracked = isEmpty( props.tracked ) ? {} : props.tracked.rows

	return (
		<Fragment>
			<div className="rank-math-keyword-table">
				<TableCard
					className="rank-math-table"
					title={
						<Fragment>
							{ __( 'Rest of the Keywords', 'rank-math' ) }
						</Fragment>
					}
					headers={ filteredHeaders }
					rows={ processRows(
						rows,
						map( headers, 'key' ),
						getPageOffset( paged, rowsPerPage ),
						Object.keys( tracked )
					) }
					downloadable={ true }
					query={ query }
					rowsPerPage={ rowsPerPage }
					totalRows={ parseInt( get( summary, [ 'keywords', 'total' ], 0 ) ) }
					summary={ tableSummary }
					showPageArrowsLabel={ false }
					onPageChange={ ( newPage ) => {
						history.push( '/keywords/' + newPage )
					} }
					onQueryChange={ () => {} }
					onColumnsChange={ onColumnsChange }
				/>
			</div>
		</Fragment>
	)
}

export default withRouter(
	withSelect( ( select, props ) => {
		const query = props.match.params
		const { paged = 1 } = query

		return {
			query,
			history: props.history,
			rows: select( 'rank-math' ).getKeywordsRows( paged ),
			summary: select( 'rank-math' ).getKeywordsSummary(),
			tracked: select( 'rank-math' ).getTrackedKeywords(),
			userPreference: select( 'rank-math' ).getUserColumnPreference(
				TABLE_PREF_KEY
			),
		}
	} )( KeywordsTable )
)
