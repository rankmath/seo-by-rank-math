/**
 * External dependencies
 */
import { forEach, filter } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Checks if all required fields have been filled before the form is submitted.
 */
export default () => {
	const form = document.querySelector( '.rank-math-editcreate-form' )
	const requiredInputs = form.querySelectorAll(
		'input[type="text"]:not(#add_category):not(#exclude)'
	)

	// Remove any existing validation messages
	forEach( requiredInputs, ( input ) => {
		input.classList.remove( 'invalid' )
		const validationMessage = input.nextElementSibling

		if ( validationMessage && validationMessage.classList.contains( 'validation-message' ) ) {
			validationMessage.remove()
		}
	} )

	const emptyRequiredInputs = filter(
		requiredInputs,
		( input ) => ! input.value.trim()
	)

	// If there are no empty required inputs, validation passes
	if ( ! emptyRequiredInputs.length > 0 ) {
		return true
	}

	// Add validation message and invalid class
	forEach( emptyRequiredInputs, ( input, index ) => {
		const span = document.createElement( 'span' )
		span.className = 'validation-message'
		span.innerText = __( 'This field must not be empty.', 'rank-math' )

		input.classList.add( 'invalid' )
		input.after( span )

		if ( index === 0 ) {
			input.scrollIntoView( { behavior: 'smooth', block: 'nearest' } )
		}
	} )

	return false
}
