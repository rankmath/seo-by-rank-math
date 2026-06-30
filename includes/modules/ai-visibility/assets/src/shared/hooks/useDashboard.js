/**
 * useDashboard — dashboard data hook.
 *
 * Manages summary metrics, per-brand rollup rows, mutations, and the
 * first-analysis poller. Implements stale-while-revalidate: serves a stale
 * cache immediately, then revalidates in the background.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { useState, useEffect, useCallback, useRef } from '@wordpress/element'
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import {
	getOverview,
	getInsights,
	createBrand,
	updateBrand,
	setBrandStatus,
} from '../services/api/aiVisibilityApi'
import { getAnalysisState } from '../../utils/analysisState'

const POLL_INTERVAL_MS = 20000
const POLL_MAX_ATTEMPTS = 10
const POLL_MAX_ERRORS = 3

/**
 * Whether a brand qualifies for first-analysis polling.
 *
 * Only brands awaiting their first result (no `last_analyzed` yet) are
 * pollable: the insights endpoint always returns the last *completed* run, so
 * polling a brand that already has data can't detect a newer run completing —
 * that resolves on the normal /overview refresh instead.
 *
 * @param {Object} brand Brand row.
 * @return {boolean} True when pollable.
 */
const isPollable = ( brand ) => 'running' === getAnalysisState( brand ) && ! brand.last_analyzed

/**
 * Dashboard data hook.
 *
 * @return {Object} summary, brands, loading, isStale, error, refetch, mutation handlers.
 */
const useDashboard = () => {
	const [ summary, setSummary ] = useState( null )
	const [ brands, setBrands ] = useState( [] )
	const [ loading, setLoading ] = useState( true )
	const [ isStale, setIsStale ] = useState( false )
	const [ error, setError ] = useState( null )

	const isFetching = useRef( false )
	const pollState = useRef( {} ) // per-brand attempt/error counters
	const searchRef = useRef( '' ) // preserved across refreshes/polls

	/**
	 * @param {boolean} [refresh=false] Force upstream revalidation.
	 * @return {Promise<Object|null>} Envelope or null on guard/error.
	 */
	const fetchDashboard = useCallback( async ( refresh = false ) => {
		if ( isFetching.current ) {
			return null
		}
		isFetching.current = true
		setError( null )

		try {
			const data = await getOverview( { refresh, search: searchRef.current } )
			setSummary( data?.summary ?? null )
			setBrands( Array.isArray( data?.brands ) ? data.brands : [] )
			setIsStale( !! data?.is_stale )
			return data
		} catch ( err ) {
			setError( err?.message || __( 'Failed to load dashboard data.', 'seo-by-rank-math' ) )
			return null
		} finally {
			isFetching.current = false
			setLoading( false )
		}
	}, [] )

	/**
	 * @param {string} term Search term (filters cached rows server-side).
	 * @return {Promise<Object|null>}
	 */
	const handleSearch = useCallback( ( term ) => {
		searchRef.current = term || ''
		return fetchDashboard()
	}, [ fetchDashboard ] )

	// Initial load; revalidate if the served cache was stale.
	useEffect( () => {
		const load = async () => {
			const data = await fetchDashboard()
			if ( data?.is_stale ) {
				await fetchDashboard( true )
			}
		}
		load()
	}, [ fetchDashboard ] )

	// First-analysis poller — bounded per-brand, paused when tab is hidden.
	useEffect( () => {
		const pollable = brands.filter( isPollable )
		if ( ! pollable.length ) {
			return
		}

		const intervalId = setInterval( async () => {
			if ( document.hidden ) {
				return
			}

			for ( const brand of pollable ) {
				if ( ! pollState.current[ brand.id ] ) {
					pollState.current[ brand.id ] = { attempts: 0, errors: 0 }
				}
				const state = pollState.current[ brand.id ]
				if ( state.attempts >= POLL_MAX_ATTEMPTS || state.errors >= POLL_MAX_ERRORS ) {
					continue
				}
				state.attempts++

				try {
					const data = await getInsights( brand.id )
					state.errors = 0
					// First analysis completed (404→insights). Refresh from the
					// API so the row's analysis_status reflects completion and
					// the running icon clears.
					if ( data?.insights ) {
						await fetchDashboard( true )
					}
				} catch {
					state.errors++
				}
			}
		}, POLL_INTERVAL_MS )

		return () => clearInterval( intervalId )
	}, [ brands, fetchDashboard ] )

	/**
	 * @param {Function} mutation    API call to run.
	 * @param {string}   fallbackMsg Error fallback message.
	 * @return {Promise<*>}
	 */
	const mutate = useCallback( async ( mutation, fallbackMsg ) => {
		try {
			const result = await mutation()
			await fetchDashboard()
			return result
		} catch ( err ) {
			setError( err?.message || fallbackMsg )
			throw err
		}
	}, [ fetchDashboard ] )

	const handleAddBrand = useCallback(
		( data ) => mutate( () => createBrand( data ), __( 'Failed to create brand.', 'seo-by-rank-math' ) ),
		[ mutate ]
	)

	const handleUpdateBrand = useCallback(
		( id, data ) => mutate( () => updateBrand( id, data ), __( 'Failed to update brand.', 'seo-by-rank-math' ) ),
		[ mutate ]
	)

	const handleDisableBrand = useCallback(
		( id ) => mutate( () => setBrandStatus( id, 'inactive' ), __( 'Failed to disable brand.', 'seo-by-rank-math' ) ),
		[ mutate ]
	)

	const handleEnableBrand = useCallback(
		( id ) => mutate( () => setBrandStatus( id, 'active' ), __( 'Failed to enable brand.', 'seo-by-rank-math' ) ),
		[ mutate ]
	)

	return {
		summary,
		brands,
		loading,
		isStale,
		error,
		refetch: fetchDashboard,
		handleSearch,
		handleAddBrand,
		handleUpdateBrand,
		handleDisableBrand,
		handleEnableBrand,
	}
}

export default useDashboard
