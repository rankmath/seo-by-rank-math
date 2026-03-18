/**
 * useTableData hook.
 *
 * Fetches paginated, filterable table data from a REST endpoint.
 */

/**
 * WordPress dependencies
 */
import { useState, useEffect, useCallback, useRef } from '@wordpress/element'
import apiFetch from '@wordpress/api-fetch'

/**
 * Fetch data from a REST endpoint with query args.
 *
 * @param {string} endpoint REST endpoint path (e.g. '/rankmath/v1/links/posts').
 * @param {Object} params   Query parameters.
 * @return {Promise<Object>} Response data.
 */
const fetchData = ( endpoint, params ) => {
	const query = Object.entries( params )
		.filter( ( [ , v ] ) => v !== '' && v !== null && v !== undefined )
		.map( ( [ k, v ] ) => {
			if ( Array.isArray( v ) ) {
				return v.map( ( item ) => `${ encodeURIComponent( k ) }[]=${ encodeURIComponent( item ) }` ).join( '&' )
			}
			return `${ encodeURIComponent( k ) }=${ encodeURIComponent( v ) }`
		} )
		.join( '&' )

	return apiFetch( { path: `${ endpoint }?${ query }` } )
}

/**
 * useTableData hook.
 *
 * @param {Object} options                Hook options.
 * @param {string} options.dataEndpoint   REST endpoint for rows.
 * @param {string} options.statsEndpoint  REST endpoint for stats.
 * @param {Object} options.initialFilters Initial filter values.
 * @return {Object} Table state and handlers.
 */
const useTableData = ( { dataEndpoint, statsEndpoint, initialFilters = {} } ) => {
	const [ data, setData ] = useState( [] )
	const [ stats, setStats ] = useState( null )
	const [ loading, setLoading ] = useState( true )
	const [ filters, setFilters ] = useState( initialFilters )
	const [ sortConfig, setSortConfig ] = useState( {
		orderby: initialFilters.orderby || '',
		order: initialFilters.order || 'ASC',
	} )
	const [ pagination, setPagination ] = useState( {
		page: 1,
		perPage: 10,
		total: 0,
		pages: 0,
	} )

	const fetchingRef = useRef( false )
	const isMountedRef = useRef( true )

	useEffect( () => {
		isMountedRef.current = true
		return () => {
			isMountedRef.current = false
		}
	}, [] )

	const fetchAll = useCallback( async ( currentFilters, currentSort, currentPage, perPage ) => {
		if ( fetchingRef.current ) {
			return
		}
		fetchingRef.current = true
		setLoading( true )

		const params = {
			...currentFilters,
			orderby: currentSort.orderby,
			order: currentSort.order,
			page: currentPage,
			per_page: perPage,
		}

		try {
			const [ dataResponse, statsResponse ] = await Promise.all( [
				fetchData( dataEndpoint, params ),
				statsEndpoint ? fetchData( statsEndpoint, {} ) : Promise.resolve( null ),
			] )

			if ( ! isMountedRef.current ) {
				return
			}

			// Determine data key from response (posts or links).
			const dataKey = dataResponse.posts ? 'posts' : 'links'
			setData( dataResponse[ dataKey ] || [] )
			setPagination( ( prev ) => ( {
				...prev,
				page: currentPage,
				perPage,
				total: dataResponse.total || 0,
				pages: dataResponse.pages || 0,
			} ) )

			if ( statsResponse ) {
				setStats( statsResponse )
			}
		} catch ( error ) {
			if ( isMountedRef.current ) {
				setData( [] )
			}
		} finally {
			if ( isMountedRef.current ) {
				setLoading( false )
			}
			fetchingRef.current = false
		}
	}, [ dataEndpoint, statsEndpoint ] ) // eslint-disable-line react-hooks/exhaustive-deps

	// Re-fetch when filters, sort, or page changes.
	useEffect( () => {
		fetchAll( filters, sortConfig, pagination.page, pagination.perPage )
	}, [ filters, sortConfig, pagination.page, pagination.perPage ] ) // eslint-disable-line react-hooks/exhaustive-deps

	const handleFilterChange = useCallback( ( key, value ) => {
		setFilters( ( prev ) => ( { ...prev, [ key ]: value } ) )
		setPagination( ( prev ) => ( { ...prev, page: 1 } ) )
	}, [] )

	const handlePageChange = useCallback( ( newPage ) => {
		setPagination( ( prev ) => ( { ...prev, page: newPage } ) )
	}, [] )

	const handlePerPageChange = useCallback( ( newPerPage ) => {
		setPagination( ( prev ) => ( { ...prev, page: 1, perPage: newPerPage } ) )
	}, [] )

	const handleSortChange = useCallback( ( orderby, order ) => {
		setSortConfig( { orderby, order } )
		setPagination( ( prev ) => ( { ...prev, page: 1 } ) )
	}, [] )

	return {
		data,
		stats,
		loading,
		pagination,
		filters,
		sortConfig,
		handleFilterChange,
		handlePageChange,
		handlePerPageChange,
		handleSortChange,
	}
}

export default useTableData
