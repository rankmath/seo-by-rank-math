/**
 * Analyses & Transcripts tab container.
 *
 * Rows are derived from the cached dashboard data (one row per brand = its
 * latest run); filtering, search, and pagination are client-side. No
 * dedicated API endpoints.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { useState, useEffect, useMemo, useCallback } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { Notice } from '@wordpress/components'
import { addQueryArgs } from '@wordpress/url'

/**
 * Internal dependencies
 */
import useDashboard from '../shared/hooks/useDashboard'
import usePagination from '../shared/hooks/usePagination'
import { getAnalysisState } from '../utils/analysisState'
import { SectionHeader } from '../shared/components'
import AnalysesFilterBar from './AnalysesFilterBar'
import AnalysesTable from './AnalysesTable'
import AnalysisRunDetail from './AnalysisRunDetail'
import './Analyses.scss'

const DEFAULT_FILTERS = {
	brandIds: [],
	status: '',
	search: '',
	dateFrom: '',
	dateTo: '',
}

/**
 * @param {Object} filters Active filter bag.
 */
const writeFiltersToUrl = ( filters ) => {
	const params = {
		aiv_brands: filters.brandIds.length ? filters.brandIds.join( ',' ) : undefined,
		aiv_status: filters.status || undefined,
		aiv_search: filters.search || undefined,
		aiv_from: filters.dateFrom || undefined,
		aiv_to: filters.dateTo || undefined,
	}

	const url = addQueryArgs( window.location.href, params )
	window.history.replaceState( {}, '', url )
}

/**
 * Derive a run row from a brand's rollup data.
 *
 * @param {Object} brand Brand row.
 * @return {Object} Analyses table row.
 */
const toRunRow = ( brand ) => {
	const state = getAnalysisState( brand )

	return {
		id: brand.id,
		brand_id: brand.id,
		brand_name: brand.name,
		started_at: brand.last_analyzed,
		// Matches the Dashboard column's derivation, so both tabs agree.
		status: state ?? ( brand.last_analyzed ? 'done' : 'none' ),
	}
}

/**
 * Analyses tab container component.
 *
 * @return {JSX.Element} Filter bar + derived runs table.
 */
const Analyses = () => {
	const { brands, loading, error } = useDashboard()

	const [ filters, setFilters ] = useState( DEFAULT_FILTERS )
	const [ selectedRow, setSelectedRow ] = useState( null )

	const brandOptions = useMemo(
		() => brands.map( ( b ) => ( { label: b.name, value: b.id } ) ),
		[ brands ]
	)

	const filteredItems = useMemo( () => {
		return brands.map( toRunRow ).filter( ( item ) => {
			if ( filters.search && ! item.brand_name.toLowerCase().includes( filters.search.toLowerCase() ) ) {
				return false
			}
			if ( filters.status && item.status !== filters.status ) {
				return false
			}
			if ( filters.brandIds.length && ! filters.brandIds.includes( item.brand_id ) ) {
				return false
			}

			const itemDate = item.started_at ? item.started_at.slice( 0, 10 ) : ''
			if ( filters.dateFrom && ( ! itemDate || itemDate < filters.dateFrom ) ) {
				return false
			}
			if ( filters.dateTo && ( ! itemDate || itemDate > filters.dateTo ) ) {
				return false
			}

			return true
		} )
	}, [ brands, filters ] )

	const { pagination, pageItems, total, pages, onPageChange, onPerPageChange, resetPage } =
		usePagination( filteredItems, { perPage: 10, syncUrl: true } )

	const handleFilterChange = useCallback( ( patch ) => {
		setFilters( ( prev ) => ( { ...prev, ...patch } ) )
		resetPage()
	}, [ resetPage ] )

	const handleClearFilters = useCallback( () => {
		setFilters( DEFAULT_FILTERS )
		resetPage()
	}, [ resetPage ] )

	useEffect( () => {
		writeFiltersToUrl( filters )
	}, [ filters ] )

	if ( selectedRow ) {
		return (
			<AnalysisRunDetail
				row={ selectedRow }
				onBack={ () => setSelectedRow( null ) }
			/>
		)
	}

	const ns = 'rank-math-ai-visibility-analyses'

	return (
		<div className={ ns }>

			<SectionHeader
				title={ __( 'Analyses & Transcripts', 'seo-by-rank-math' ) }
				subtitle={ __( 'Global view of all runs with per-query detail.', 'seo-by-rank-math' ) }
			/>

			{ error && (
				<Notice status="error" isDismissible={ false }>
					{ error }
				</Notice>
			) }

			<div className={ `${ ns }__card` }>
				<AnalysesFilterBar
					filters={ filters }
					onFilterChange={ handleFilterChange }
					onClearFilters={ handleClearFilters }
					brandOptions={ brandOptions }
				/>

				<AnalysesTable
					items={ pageItems }
					loading={ loading }
					pagination={ { ...pagination, total, pages } }
					onViewDetail={ ( row ) => setSelectedRow( row ) }
					onPageChange={ onPageChange }
					onPerPageChange={ onPerPageChange }
				/>
			</div>

		</div>
	)
}

export default Analyses
