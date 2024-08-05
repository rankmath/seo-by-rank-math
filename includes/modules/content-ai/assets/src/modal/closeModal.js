/**
 * External dependencies
 */
import { includes, forEach, isEmpty, isUndefined, isNull } from 'lodash'

/**
 * WordPress dependencies
 */
import { dispatch } from '@wordpress/data'

const closeModal = ( setEndpoint ) => {
	if ( setEndpoint ) {
		setEndpoint( '' )
	}

	if ( ! isNull( document.getElementById( 'rank-math-content-ai-modal-wrapper' ) ) ) {
		document.getElementById( 'rank-math-content-ai-modal-wrapper' ).remove()
		document.querySelector( '.rank-math-contentai-modal-overlay' ).remove()
		document.body.classList.remove( 'modal-open' )
	}

	return true
}

export default ( e, params, attributes, setEndpoint ) => {
	if ( ! isNull( dispatch( 'rank-math' ) ) ) {
		dispatch( 'rank-math-content-ai' ).isAutoCompleterOpen( false )
	}

	if ( isUndefined( e ) ) {
		return
	}

	if ( e.type === 'blur' ) {
		if ( ! includes( e.target.classList, 'rank-math-contentai-modal' ) ) {
			return false
		}

		let canClose = true
		forEach( params, ( value, key ) => {
			if ( value.isRequired && ! isEmpty( attributes[ key ] ) ) {
				canClose = false
			}
		} )

		return ! canClose ? false : closeModal( setEndpoint )
	}

	if ( e.key === 'Escape' && ! isNull( document.querySelector( '.tagify__dropdown' ) ) ) {
		document.querySelector( '.tagify__dropdown' ).remove()
		return false
	}

	return closeModal( setEndpoint )
}
