/**
 * External dependencies
 */
import jQuery from 'jquery'
import { includes } from 'lodash'

/**
 * WordPress dependencies
 */
import { SearchControl } from '@wordpress/components'
import { useEffect, useCallback } from '@wordpress/element'

/**
 * Search Field component.
 *
 * @param {Object}   props           Component props
 * @param {string}   props.search    Search text
 * @param {Function} props.setSearch Function to call when value in field is changed
 */
export default ( { search, setSearch } ) => {
	const handleUserKeyPress = useCallback( ( event ) => {
		const searchField = jQuery( '.rank-math-content-ai-search-field input' )
		// eslint-disable-next-line @wordpress/no-global-active-element
		const activeElement = document.activeElement

		if (
			'/' !== event.key ||
			! searchField.length ||
			activeElement === searchField[ 0 ] ||
			! includes( [ 'BODY', 'DIV', 'BUTTON', 'SPAN' ], activeElement.tagName )
		) {
			return
		}

		event.preventDefault()
		searchField.trigger( 'focus' )
		return false
	}, [] )

	useEffect( () => {
		window.addEventListener( 'keydown', handleUserKeyPress )
		return () => {
			window.removeEventListener( 'keydown', handleUserKeyPress )
		}
	}, [ handleUserKeyPress ] )

	return (
		<div className="search-field">
			<SearchControl
				value={ search }
				className="rank-math-content-ai-search-field"
				onChange={ ( newSearch ) => {
					setSearch( newSearch )
				} }
			/>
		</div>
	)
}
