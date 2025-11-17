/**
 * External dependencies
 */
import jQuery from 'jquery'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import AltTextGenerator from './AltTextGenerator'
import hasError from '../helpers/hasError'
import showCTABox from '@helpers/showCTABox'

// MediaHandler component to add Generate Button in Attachment modal.
class MediaHandler {
	/**
	 * Constructor.
	 */
	constructor() {
		this.injectGenerateAltTextButton()
		jQuery( window ).on( 'elementor:init', () => {
			this.injectGenerateAltTextButton()
		} )
	}

	// Add Generate Alt button in Attachment Details modal.
	injectGenerateAltTextButton() {
		// Two Column Attchment Details modal. Add Generate Button in Media library Grid mode.
		if ( wp.media !== undefined && wp.media.view.Attachment.Details.TwoColumn !== undefined ) {
			wp.media.view.Attachment.Details.TwoColumn = wp.media.view.Attachment.Details.TwoColumn.extend(
				{
					template: ( view ) => {
						return this.getTemplate( 'attachment-details-two-column', view, true )
					},
					events: {
						...wp.media.view.Attachment.Details.TwoColumn.prototype.events,
						'click .rank-math-generate-alt-button': this.generateAltTextForImage,
					},
				}
			)
		}

		// Image Details modal. Add Generate Button in Classic Editor Image details modal.
		if ( rankMath.currentEditor === 'classic' ) {
			wp.media.view.ImageDetails = wp.media.view.ImageDetails.extend(
				{
					template: ( view ) => {
						return this.getTemplate( 'image-details', view )
					},
					events: {
						...wp.media.view.ImageDetails.prototype.events,
						'click .rank-math-generate-alt-button': this.generateAltTextForImage,
					},
				}
			)
		}

		// Attachment Details modal. Add Generate Button in Block Editor Attachment details modal.
		if ( wp.media !== undefined ) {
			wp.media.view.Attachment.Details = wp.media.view.Attachment.Details.extend(
				{
					template: ( view ) => {
						return this.getTemplate( 'attachment-details', view )
					},
					events: {
						...wp.media.view.Attachment.Details.prototype.events,
						'click .rank-math-generate-alt-button': this.generateAltTextForImage,
					},
				}
			)
		}
	}

	// Get Media modal template.
	getTemplate( template, view, isTwoColumn = false ) {
		const html = wp.media.template( template )( view )
		const dom = document.createElement( 'div' )
		dom.innerHTML = html

		if ( ! dom.querySelector( '#alt-text-description' ) ) {
			return html
		}

		const generateAltTextButton = '<button class="rank-math-generate-alt-button" data-two-column="' + isTwoColumn + '"><i class="rm-icon rm-icon-content-ai"></i>' + __( 'Generate Alt', 'rank-math' ) + '</button><br />'

		// Add it to the beginning of #alt-text-description, along with a line break.
		const altText = dom.querySelector( '#alt-text-description' )
		altText.innerHTML = generateAltTextButton + altText.innerHTML

		return dom.innerHTML
	}

	// Generate Alt text for the selected image.
	generateAltTextForImage( e ) {
		e.preventDefault()
		e.stopPropagation()

		if ( hasError() || wp.data.select( 'rank-math-content-ai' ).getData().credits < 50 ) {
			jQuery( '.media-modal-close' ).trigger( 'click' )
			showCTABox( { creditsRequired: 50 } )
			return
		}

		const imageUrl = this.model.attributes.url
		if ( ! imageUrl ) {
			return
		}

		const generateAltTextButton = e.currentTarget
		const generateButtonText = generateAltTextButton.innerHTML
		// Disable the button while generating alt text
		generateAltTextButton.disabled = true
		generateAltTextButton.innerHTML = __( 'Generating…', 'rank-math' )

		const isTwoColumn = 'true' === generateAltTextButton.getAttribute( 'data-two-column' )
		AltTextGenerator( imageUrl )
			.then( ( generatedAltText ) => {
				// Successfully generated alt text, now update and save the alt text field
				this.model.set( 'alt', generatedAltText )

				if ( rankMath.currentEditor !== 'classic' ) {
					this.model.save()
				}

				const wrapper = isTwoColumn ? '#attachment-details-two-column-alt-text' : '#attachment-details-alt-text'
				const altTextField = document.querySelector( wrapper )
				if ( altTextField ) {
					altTextField.value = generatedAltText
				}

				generateAltTextButton.innerHTML = generateButtonText
			} )
			.catch( ( error ) => {
				console.error( error )
				// Update the button to show failure
				generateAltTextButton.classList.add( 'failed' )
				generateAltTextButton.innerHTML = __( 'Failed', 'rank-math' )
				setTimeout( () => {
					generateAltTextButton.classList.remove( 'failed' )
					generateAltTextButton.innerHTML = generateButtonText
				}, 3000 )
			} )
			.finally( () => {
				// Re-enable the button after operation
				generateAltTextButton.disabled = false
			} )
	}
}

export default MediaHandler
