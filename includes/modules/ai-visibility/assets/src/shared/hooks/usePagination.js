/**
 * usePagination — client-side pagination over an in-memory array.
 *
 * @since 1.0.274
 */

/**
 * WordPress dependencies
 */
import { useState, useMemo, useCallback, useEffect, useRef } from '@wordpress/element'

/**
 * Internal dependencies
 */
import { getPageFromUrl, getPerPageFromUrl, writePaginationToUrl } from '../../utils/urlState'

const DEFAULT_PER_PAGE = 10

/**
 * @param {Array}   items                  Full, already-filtered item list to paginate.
 * @param {Object}  [options]
 * @param {number}  [options.perPage=10]   Default/initial page size.
 * @param {boolean} [options.syncUrl=false] Read the initial page from the URL and keep it updated.
 * @return {Object} `{ pagination, pageItems, total, pages, onPageChange, onPerPageChange, resetPage }`
 */
const usePagination = ( items = [], { perPage = DEFAULT_PER_PAGE, syncUrl = false } = {} ) => {
	const [ pagination, setPagination ] = useState( () => ( {
		page: syncUrl ? getPageFromUrl( 1 ) : 1,
		perPage: syncUrl ? getPerPageFromUrl( perPage ) : perPage,
	} ) )

	const total = items.length
	const pages = Math.max( 1, Math.ceil( total / pagination.perPage ) )

	const pageItems = useMemo( () => {
		const offset = ( pagination.page - 1 ) * pagination.perPage
		return items.slice( offset, offset + pagination.perPage )
	}, [ items, pagination ] )

	// Clamp out-of-range pages, but not on mount (would clobber a URL-provided page).
	const isFirstRun = useRef( true )
	useEffect( () => {
		if ( isFirstRun.current ) {
			isFirstRun.current = false
			return
		}
		setPagination( ( prev ) => ( prev.page > pages ? { ...prev, page: pages } : prev ) )
	}, [ pages ] )

	useEffect( () => {
		if ( syncUrl ) {
			writePaginationToUrl( pagination, perPage )
		}
	}, [ pagination, syncUrl, perPage ] )

	const onPageChange = useCallback(
		( page ) => setPagination( ( prev ) => ( { ...prev, page: Math.max( 1, page ) } ) ),
		[]
	)

	const onPerPageChange = useCallback(
		( newPerPage ) => setPagination( { page: 1, perPage: Math.max( 1, newPerPage ) } ),
		[]
	)

	const resetPage = useCallback(
		() => setPagination( ( prev ) => ( prev.page === 1 ? prev : { ...prev, page: 1 } ) ),
		[]
	)

	return { pagination, pageItems, total, pages, onPageChange, onPerPageChange, resetPage }
}

export default usePagination
