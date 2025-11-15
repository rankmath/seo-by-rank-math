/**
 * External dependencies
 */
import jQuery from 'jquery'
import { isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import apiFetch from '@wordpress/api-fetch'

/**
 * Internal dependencies
 */
import AltTextGenerator from './components/AltTextGenerator'
import MediaHandler from './components/MediaHandler'
import hasError from './helpers/hasError'
import showCTABox from '@helpers/showCTABox'
import { getStore } from './store'

class AddAltTextGenerator {
	/**
	 * Constructor.
	 */
	constructor() {
		getStore()
		this.data = wp.data.select( 'rank-math-content-ai' ).getData()

		// Initiate MediaHandler to add Generate button in Attachment Details modal.
		new MediaHandler()

		setTimeout( () => {
			this.addBulkGenerateButton( this.data )
		}, 1000 )

		// If there is no #attachment_alt field, then we don't need to do anything.
		this.altTextField = document.querySelector( '#attachment_alt' )
		this.imageUrl = document.querySelector( '#attachment_url' )
		if ( ! this.altTextField || ! this.imageUrl ) {
			return
		}

		this.injectGenerateAltTextButton()
	}

	// Add Bulk Generate & Generate Alt button in Media Library Grid mode.
	addBulkGenerateButton( data ) {
		const mediaToolbar = jQuery( '.media-toolbar-secondary' )
		if ( mediaToolbar.length <= 0 ) {
			return
		}

		const buttonText = '<i class="rm-icon rm-icon-content-ai"></i> ' + __( 'Generate Alt with AI', 'rank-math' )
		const customButton = jQuery( '<button class="button media-button button-primary button-large delete-selected-button rank-math-bulk-generate-button hidden" disabled="disabled">' + buttonText + '</button>' )
		mediaToolbar.prepend( customButton )

		// Handle click event for the custom button
		customButton.on( 'click', function() {
			if ( hasError() || data.credits < 50 ) {
				showCTABox( { creditsRequired: 50 } )
				return
			}

			const selectedItems = jQuery( '.attachment.selected' )
			const selectedIds = selectedItems.map( function() {
				return jQuery( this ).data( 'id' )
			} ).get()

			if ( selectedIds.length <= 0 ) {
				return
			}

			customButton[ 0 ].innerHTML = __( 'Generating…', 'rank-math' )

			apiFetch( {
				method: 'POST',
				path: '/rankmath/v1/ca/generateAlt',
				data: {
					attachmentIds: selectedIds,
				},
			} )
				.catch( ( error ) => {
					console.log( error )
					customButton[ 0 ].innerHTML = buttonText
				} )
				.then( () => {
					customButton[ 0 ].innerHTML = buttonText
					window.location.reload()
				} )
		} )

		const frame = wp.media.frame.state( 'library' ).get( 'selection' )
		if ( ! isUndefined( frame ) ) {
			frame.on( 'selection:single', () => {
				// Handle single selection
				customButton[ 0 ].removeAttribute( 'disabled' )
			} )

			frame.on( 'selection:unsingle', () => {
				if ( ! jQuery( '.attachment.selected' ).length ) {
					customButton[ 0 ].setAttribute( 'disabled', 'disabled' )
				}
			} )
		}
	}

	// Add Generate with Alt Button on Attachment page.
	injectGenerateAltTextButton() {
		const generateAltTextButton = document.createElement( 'button' )
		generateAltTextButton.classList.add( 'rank-math-generate-alt-button' )
		generateAltTextButton.innerHTML = '<i class="rm-icon rm-icon-content-ai"></i>' + __( 'Generate Alt', 'rank-math' )

		// Add it right to next to the #attachment_alt field.
		this.altTextField.insertAdjacentElement( 'afterend', generateAltTextButton )
		this.altTextField.insertAdjacentHTML( 'afterend', '<br>' )

		this.generateAltTextButton = generateAltTextButton

		generateAltTextButton.addEventListener( 'click', this.generateAltTextForImage.bind( this ) )
	}

	// Funtion to Generate Alt text on Attachment page.
	generateAltTextForImage( e ) {
		e.preventDefault()
		e.stopPropagation()

		if ( hasError() || this.data.credits < 50 ) {
			showCTABox( { creditsRequired: 50 } )
			return
		}

		const imageUrl = this.imageUrl.value

		this.generateAltTextButton.innerHTML = __( 'Generating…', 'rank-math' )
		// Start generating the alt text
		AltTextGenerator( imageUrl )
			.then( ( generatedAltText ) => {
				// Successfully generated alt text, now update the alt text field
				this.altTextField.value = generatedAltText

				this.generateAltTextButton.innerHTML = '<i class="rm-icon rm-icon-content-ai"></i>' + __( 'Generate Alt', 'rank-math' )
			} )
			.catch( ( error ) => {
				console.error( 'Failed to generate alt text:', error )
				// Handle any errors that occurred during generation
				this.generateAltTextButton.classList.add( 'failed' )
				this.generateAltTextButton.innerHTML = __( 'Failed', 'rank-math' )
				setTimeout( () => {
					this.generateAltTextButton.classList.remove( 'failed' )
					this.generateAltTextButton.innerHTML = '<i class="rm-icon rm-icon-content-ai"></i>' + __( 'Generate Alt', 'rank-math' )
				}, 2000 )
			} )
	}
}

window.addEventListener( 'load', () => {
	new AddAltTextGenerator()
} )
