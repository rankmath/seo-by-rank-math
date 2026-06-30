/**
 * AnalysesFilterBar — filter controls above the analyses table.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { useMemo, useState, useEffect } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { SelectControl, SearchControl, Button } from '@wordpress/components'

/**
 * Internal dependencies
 */
import './AnalysesFilterBar.scss'
import { DateInput } from '../shared/components'

// Status options — matches the states derived by `getAnalysisState`.
const STATUS_OPTIONS = [
	{ label: __( 'All Statuses', 'seo-by-rank-math' ), value: '' },
	{ label: __( 'Done', 'seo-by-rank-math' ), value: 'done' },
	{ label: __( 'Running', 'seo-by-rank-math' ), value: 'running' },
	{ label: __( 'Error', 'seo-by-rank-math' ), value: 'error' },
]

/**
 * AnalysesFilterBar component.
 *
 * @param {Object}   props
 * @param {Object}   props.filters           Active filter bag.
 * @param {Function} props.onFilterChange    Receives a partial patch object.
 * @param {Function} props.onClearFilters    Resets all filters to defaults.
 * @param {Array}    [props.brandOptions=[]] `[{ label, value }]` for the brand dropdown.
 * @return {JSX.Element} Single-row filter bar.
 */
const AnalysesFilterBar = ( {
	filters,
	onFilterChange,
	onClearFilters,
	brandOptions = [],
} ) => {
	const ns = 'rank-math-ai-visibility-analyses-filter-bar'

	// Prepend "All Brands" option to the brand list.
	const brandSelectOptions = useMemo( () => [
		{ label: __( 'All Brands', 'seo-by-rank-math' ), value: '' },
		...brandOptions,
	], [ brandOptions ] )

	// The hook stores brandIds as an array. For the single-select UI we store
	// the first item (or '') and convert back on change.
	const activeBrandId = ( filters.brandIds && filters.brandIds[ 0 ] )
		? String( filters.brandIds[ 0 ] )
		: ''

	// Local search state — debounced 300 ms before propagating to the hook.
	// This prevents a new API request on every keystroke.
	const [ localSearch, setLocalSearch ] = useState( filters.search || '' )

	// Sync local state if the parent resets filters externally (e.g. "clear all").
	useEffect( () => {
		setLocalSearch( filters.search || '' )
	}, [ filters.search ] )

	useEffect( () => {
		const timer = setTimeout( () => {
			onFilterChange( { search: localSearch } )
		}, 300 )
		return () => clearTimeout( timer )
	}, [ localSearch, onFilterChange ] )

	const hasActiveFilters = (
		activeBrandId !== '' ||
		filters.status !== '' ||
		( filters.search || '' ) !== '' ||
		( filters.dateFrom || '' ) !== '' ||
		( filters.dateTo || '' ) !== ''
	)

	return (
		<div className={ ns }>

			{ /* Brand — standard SelectControl, matches Status style */ }
			<SelectControl
				className={ `${ ns }__brand` }
				value={ activeBrandId }
				options={ brandSelectOptions }
				onChange={ ( val ) => onFilterChange( { brandIds: val ? [ val ] : [] } ) }
				__next40pxDefaultSize={ true }
				__nextHasNoMarginBottom={ true }
			/>

			{ /* Status */ }
			<SelectControl
				className={ `${ ns }__status` }
				value={ filters.status }
				options={ STATUS_OPTIONS }
				onChange={ ( value ) => onFilterChange( { status: value } ) }
				__next40pxDefaultSize={ true }
				__nextHasNoMarginBottom={ true }
			/>

			{ /* Search — value bound to local state; propagation is debounced 300 ms */ }
			<SearchControl
				className={ `${ ns }__search` }
				placeholder={ __( 'Search', 'seo-by-rank-math' ) }
				aria-label={ __( 'Search runs', 'seo-by-rank-math' ) }
				value={ localSearch }
				onChange={ ( value ) => setLocalSearch( value ) }
				__nextHasNoMarginBottom={ true }
			/>

			{ hasActiveFilters && (
				<Button
					className={ `${ ns }__clear` }
					variant="link"
					isDestructive
					onClick={ onClearFilters }
				>
					{ __( 'Clear filters', 'seo-by-rank-math' ) }
				</Button>
			) }

			{ /* Date range: [📅 From] - [📅 To] */ }
			<div className={ `${ ns }__dates` }>
				<div className={ `${ ns }__date-wrap` }>
					<DateInput
						value={ filters.dateFrom }
						onChange={ ( e ) => onFilterChange( { dateFrom: e.target.value } ) }
						ariaLabel={ __( 'From date', 'seo-by-rank-math' ) }
						className={ `${ ns }__date-input` }
					/>
				</div>
				<span className={ `${ ns }__date-sep` }>-</span>
				<div className={ `${ ns }__date-wrap` }>
					<DateInput
						value={ filters.dateTo }
						onChange={ ( e ) => onFilterChange( { dateTo: e.target.value } ) }
						ariaLabel={ __( 'To date', 'seo-by-rank-math' ) }
						className={ `${ ns }__date-input` }
					/>
				</div>
			</div>

		</div>
	)
}

AnalysesFilterBar.displayName = 'AnalysesFilterBar'

export default AnalysesFilterBar
