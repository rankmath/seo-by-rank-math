/**
 * External dependencies
 */
import { map, isUndefined, isEmpty } from 'lodash'
import { TableCard } from '@woocommerce/components'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'
import { withFilters } from '@wordpress/components'
import { withSelect, dispatch, useSelect } from '@wordpress/data'

/**
 * Internal dependencies
 */
import humanNumber from '@helpers/humanNumber'
import { processRows, getPageOffset, filterShownHeaders, withRouter } from '../functions'
import {elementObserver, noDataMessage} from '../helpers'
import {useRef, useState} from '@wordpress/element';

const TABLE_PREF_KEY = 'performance'

const PostsTable = ( props ) => {
	const { query, navigate, userPreference } = props
	const { paged = 1 } = query;

	// Component state.
	const [ tableData, setTableData ] = useState( false )
	const [ summary, setSummary ] = useState( false )
	const intersectedState = useState( false )
	const [ isIntersected ] = intersectedState

	// Element reference.
	const elementReference = useRef( null )
	elementObserver( elementReference, intersectedState )

	useSelect( ( select ) => {
		if ( false === isIntersected ) {
			return;
		}

		const responseTableData = select( 'rank-math' ).getPostsRowsByObjects( paged, {} )
		if ( ! isEmpty( responseTableData ) && responseTableData !== tableData ) {
			setTableData( responseTableData )
		}

		const responseSummary = select( 'rank-math' ).getPostsSummary()
		if ( ! isEmpty( responseSummary ) && responseSummary !== summary ) {
			setSummary( responseSummary )
		}
	}, [ isIntersected, paged, tableData, summary ] )

	if ( isUndefined( tableData ) || isUndefined( summary ) ) {
		return 'Loading'
	}

	let postsRows = []
	if ( ! isUndefined( tableData.rows ) ) {
		postsRows = 'No Data' === tableData.rows.response ? [] : tableData.rows

		if ( isEmpty( postsRows ) ) {
			return noDataMessage( __( 'Content', 'rank-math' ) )
		}
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
	const filteredHeaders = filterShownHeaders( headers, userPreference )
	const onColumnsChange = ( columns, toggled ) => {
		userPreference[ toggled ] = ! userPreference[ toggled ]
		dispatch( 'rank-math' ).updateUserPreferences(
			userPreference,
			TABLE_PREF_KEY
		)
	}

	return (
		<div className="rank-math-posts" ref={elementReference}>
			<TableCard
				className="rank-math-table"
				title={ __( 'Content', 'rank-math' ) }
				headers={ filteredHeaders }
				downloadable={ true }
				rowsPerPage={ rowsPerPage }
				rows={ processRows(
					postsRows,
					map( headers, 'key' ),
					getPageOffset( paged, rowsPerPage )
				) }
				isLoading={ isEmpty( tableData.rows ) }

				query={ query }
				totalRows={ parseInt( tableData.rowsFound ) }
				summary={ tableSummary }
				showPageArrowsLabel={ false }
				onPageChange={ ( newPage ) => {
					navigate( '/performance/' + newPage )
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
			const query = props.params

			return {
				query,
				navigate: props.navigate,
				userPreference: select( 'rank-math' ).getUserColumnPreference(
					TABLE_PREF_KEY
				),
			}
		} )( PostsTable )
	)
)
