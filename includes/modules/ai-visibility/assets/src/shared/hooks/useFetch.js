/**
 * useFetch — generic data-fetching hook with cancelled-flag guard.
 *
 * Options: skip (bail out when truthy), errorMessage (fallback), initialData.
 * Returns setData for optimistic updates without triggering a re-fetch.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { useState, useEffect } from '@wordpress/element'

/**
 * @param {Function} fetcher                Returns a Promise; re-called on deps change.
 * @param {Array}    deps                   useEffect dependency array.
 * @param {Object}   [options]
 * @param {boolean}  [options.skip]         Skip the fetch when truthy.
 * @param {string}   [options.errorMessage]
 * @param {*}        [options.initialData]
 * @return {Object}    { data, loading, error, setData }
 */
const useFetch = ( fetcher, deps, options = {} ) => {
	const {
		skip = false,
		errorMessage = 'Failed to load.',
		initialData = null,
	} = options

	const [ data, setData ] = useState( initialData )
	const [ loading, setLoading ] = useState( ! skip )
	const [ error, setError ] = useState( null )

	useEffect( () => {
		if ( skip ) {
			setLoading( false )
			return
		}

		let cancelled = false
		setLoading( true )
		setError( null )

		fetcher()
			.then( ( result ) => {
				if ( ! cancelled ) {
					setData( result )
					setLoading( false )
				}
			} )
			.catch( ( err ) => {
				if ( ! cancelled ) {
					setError( err?.message || errorMessage )
					setLoading( false )
				}
			} )

		return () => {
			cancelled = true
		}
	}, deps )

	return { data, loading, error, setData }
}

export default useFetch
