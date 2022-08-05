/**
 * External dependencies
 */
import { isUndefined, isEmpty, map, get } from 'lodash'
import { TableCard } from '@woocommerce/components'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment } from '@wordpress/element'
import { applyFilters } from '@wordpress/hooks'
import { withFilters } from '@wordpress/components'
import { dispatch, withSelect } from '@wordpress/data'

/**
 * Internal dependencies
 */
import humanNumber from '@helpers/humanNumber'
import { processRows, getPageOffset, filterShownHeaders, withRouter } from '../functions'
import { noDataMessage } from '../helpers'

const TABLE_PREF_KEY = 'keywords'

const KeywordsTable = ( props ) => {
	const { rows, summary, query, navigate, userPreference } = props
	if ( isUndefined( rows ) || isUndefined( summary ) ) {
		return 'Loading'
	}
	const keywordRows = 'No Data' === rows.response ? [] : rows
	if ( isEmpty( keywordRows ) ) {
		return noDataMessage( __( 'Rest of the Keywords', 'rank-math' ) )
	}

	const headers = applyFilters(
		'rankMath.analytics.keywordsHeaders',
		[
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

	return (
		<Fragment>
			<div className="rank-math-keyword-table">
				<TableCard
					className="rank-math-table rank-math-analytics__card"
					title={ __( 'Rest of the Keywords', 'rank-math' ) }
					headers={ filteredHeaders }
					rows={ processRows(
						keywordRows,
						map( headers, 'key' ),
						getPageOffset( paged, rowsPerPage )
					) }
					downloadable={ true }
					query={ query }
					rowsPerPage={ rowsPerPage }
					totalRows={ parseInt( get( summary, [ 'keywords', 'total' ], 0 ) ) }
					summary={ tableSummary }
					isLoading={ isEmpty( rows ) }
					showPageArrowsLabel={ false }
					onPageChange={ ( newPage ) => {
						navigate( '/keywords/' + newPage )
					} }
					onQueryChange={ () => () => {} }
					onColumnsChange={ onColumnsChange }
				/>
			</div>
		</Fragment>
	)
}

export default withRouter(
	withFilters( 'rankMath.analytics.keywordsTable' )(
		withSelect( ( select, props ) => {
			const query = props.params
			const { paged = 1 } = query

			return {
				query,
				navigate: props.navigate,
				rows: select( 'rank-math' ).getKeywordsRows( paged ),
				summary: select( 'rank-math' ).getKeywordsSummary(),
				userPreference: select( 'rank-math' ).getUserColumnPreference(
					TABLE_PREF_KEY
				),
			}
		} )( KeywordsTable )
	)
)
