/**
 * External dependencies
 */
import { isUndefined, isEmpty, map, get } from 'lodash'
import { TableCard } from '@woocommerce/components'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment, useRef, useState } from '@wordpress/element'
import { applyFilters } from '@wordpress/hooks'
import { withFilters } from '@wordpress/components'
import { dispatch, withSelect, useSelect } from '@wordpress/data'

/**
 * Internal dependencies
 */
import humanNumber from '@helpers/humanNumber'
import { processRows, getPageOffset, filterShownHeaders, withRouter } from '../functions'
import { elementObserver, noDataMessage } from '../helpers'

const TABLE_PREF_KEY = 'keywords'

const KeywordsTable = ( props ) => {
	const { query, navigate, userPreference } = props
	const { paged = 1 } = query

	// Component state.
	const [ rows, setRows ] = useState( false )
	const [ summary, setSummary ] = useState( false )
	const [ totalRows, setTotalRows ] = useState( 0 )
	const intersectedState = useState( false )
	const [ isIntersected ] = intersectedState

	// Element reference.
	const elementReference = useRef( null )
	elementObserver( elementReference, intersectedState )

	useSelect( async ( select ) => {
		if ( false === isIntersected ) {
			return;
		}

		const responseRows = await select( 'rank-math' ).getKeywordsRows( paged )
		if ( ! isEmpty( responseRows ) && responseRows !== rows ) {
			setRows( responseRows )
		}

		const responseSummary = await select( 'rank-math' ).getKeywordsSummary()
		if ( ! isEmpty( responseSummary ) && responseSummary !== summary ) {
			setSummary( responseSummary )
		}

		const keywordsOverview = await select( 'rank-math' ).getKeywordsOverview()
		if ( ! isEmpty( keywordsOverview ) ) {
			const total = [ 'top3', 'top10', 'top50', 'top100' ].reduce((sum, key) => {
				return sum + parseInt( get( keywordsOverview, [ 'topKeywords', key, 'total' ], 0 ) )
			}, 0 )
			setTotalRows(total)
		}
	}, [ isIntersected, paged, rows, summary ] )

	if ( isUndefined( rows ) || isUndefined( summary ) ) {
		return 'Loading'
	}
	const keywordRows = 'No Data' === rows.response ? [] : rows
	if ( isEmpty( keywordRows ) ) {
		return (
			<div ref={elementReference}>
				{ noDataMessage( __( 'Rest of the Keywords', 'rank-math' ) ) }
			</div>
		)
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
				label: __( 'CTR', 'rank-math' ),
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
			label: __( 'Total Impressions', 'rank-math' ),
			value: humanNumber( get( summary, [ 'impressions', 'total' ], 0 ) ),
		},
		{
			label: __( 'CTR', 'rank-math' ),
			value: humanNumber( get( summary, [ 'ctr', 'total' ], 0 ) ),
		},
		{
			label: __( 'Total Clicks', 'rank-math' ),
			value: humanNumber( get( summary, [ 'clicks', 'total' ], 0 ) ),
		},
	]

	const rowsPerPage = 25
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
			<div className="rank-math-keyword-table" ref={elementReference}>
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
					totalRows={ totalRows }
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

			return {
				query,
				navigate: props.navigate,
				userPreference: select( 'rank-math' ).getUserColumnPreference(
					TABLE_PREF_KEY
				),
			}
		} )( KeywordsTable )
	)
)
