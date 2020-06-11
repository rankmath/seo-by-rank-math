/**
 * External dependencies
 */
import { Helpers } from '@rankMath/analyzer'

/**
 * WordPress dependencies
 */
import { addAction } from '@wordpress/hooks'
import { safeDecodeURIComponent } from '@wordpress/url'

/**
 * Internal dependencies
 */
import { swapVariables } from '@helpers/swapVariables'
import generateDescription from '@helpers/generateDescription'
import highlight from '@helpers/highlight'
import LengthIndicator from './LengthIndicator'

class SerpHooks {
	/**
	 * Class constructor
	 */
	constructor() {
		this.title = ''
		this.description = ''
		this.lengthIndicator = new LengthIndicator()
	}

	hooks() {
		this.previewDevice = this.previewDevice.bind( this )
		this.updatePreview = this.updatePreview.bind( this )
		this.updateTitlePreview = this.updateTitlePreview.bind( this )
		this.updatePermalinkPreview = this.updatePermalinkPreview.bind( this )
		this.updateDescriptionPreview = this.updateDescriptionPreview.bind(
			this
		)

		addAction( 'rank_math_preview_device', 'rank-math', this.previewDevice )
		addAction(
			'rank_math_init_refresh',
			'rank-math',
			this.updatePreview,
			1
		)
		addAction(
			'rank_math_title_refresh',
			'rank-math',
			this.updateTitlePreview,
			1
		)
		addAction(
			'rank_math_permalink_refresh',
			'rank-math',
			this.updatePermalinkPreview,
			1
		)
		addAction(
			'rank_math_content_refresh',
			'rank-math',
			this.updateDescriptionPreview,
			1
		)
		addAction(
			'rank_math_keyword_refresh',
			'rank-math',
			this.updatePreview,
			11
		)
	}

	updatePreview() {
		this.updateTitlePreview()
		this.updatePermalinkPreview()
		this.updateDescriptionPreview()
	}

	/**
	 * Function to update SERP Preview
	 *
	 * @param {string} device Selected Device.
	 */
	previewDevice( device ) {
		this.devices.removeClass( 'active' )
		switch ( device ) {
			case 'desktop':
				this.serpWrapper.removeClass( 'mobile-preview' )
				this.serpWrapper.addClass( 'desktop-preview expanded-preview' )
				rankMathEditor.elemMetabox
					.find( '.rank-math-select-device.device-desktop' )
					.addClass( 'active' )
				break

			case 'mobile':
				this.serpWrapper.removeClass( 'desktop-preview' )
				this.serpWrapper.addClass( 'mobile-preview expanded-preview' )
				rankMathEditor.elemMetabox
					.find( '.rank-math-select-device.device-mobile' )
					.addClass( 'active' )
				break

			default:
				this.serpWrapper.removeClass(
					'mobile-preview expanded-preview'
				)
				this.serpWrapper.addClass( 'desktop-preview' )
		}
	}

	updateTitlePreview() {
		this.title = this.serpTitleField.val()
		const isTitle = '' === this.title ? false : true

		this.serpTitleField.val(
			isTitle ? this.title : this.serpTitle.data( 'format' )
		)

		this.title = Helpers.sanitizeText(
			swapVariables.swap(
				'' !== this.title ? this.title : this.serpTitle.data( 'format' )
			)
		)

		this.lengthIndicator.check( this.serpTitleField, {
			text: this.title,
			min: 15,
			max: 60,
		} )
		// Set Placeholder and Title
		this.serpTitle.html(
			highlight(
				rankMathEditor.getSelectedKeyword(),
				Helpers.sanitizeText( this.title ),
				60
			)
		)

		this.updatePreviewCallbacks( 'title', this.title )
		this.serpTitle.trigger( 'rank-math-vars-replaced' )
	}

	updatePermalinkPreview() {
		const format = this.serpPermalink.data( 'format' ) || ''
		const slug = this.serpPermalinkField.val()

		// Set Placeholder and Permalink
		this.permalink =
			'' !== slug
				? format
					.replace( /%(postname|pagename)%/, slug )
					.trimRight( '/' ) + '/'
				: ''
		this.serpCanonical.attr( 'placeholder', this.permalink )

		this.lengthIndicator.check( this.serpPermalinkField, {
			text: this.permalink,
			min: 5,
			max: 75,
		} )
		this.serpPermalink.html(
			highlight(
				rankMathEditor.getSelectedKeyword(),
				Helpers.sanitizeText(
					safeDecodeURIComponent( this.permalink )
				),
				75
			)
		)

		this.updatePreviewCallbacks( 'permalink', this.permalink )
		this.serpPermalink.trigger( 'rank-math-vars-replaced' )
	}

	updateDescriptionPreview() {
		// Set Placeholder and Description
		this.description = Helpers.sanitizeText(
			swapVariables.swap(
				generateDescription( this.serpDescriptionField.val() )
			)
		)

		this.lengthIndicator.check( this.serpDescriptionField, {
			text: this.description,
			min: 80,
			max: 160,
		} )
		this.serpDescription.html(
			highlight(
				rankMathEditor.getSelectedKeyword(),
				Helpers.sanitizeText( this.description ),
				160
			)
		)
		this.serpDescriptionField.attr(
			'placeholder',
			'' !== this.description
				? this.description
				: this.serpDescription.data( 'format' )
		)

		this.updatePreviewCallbacks( 'description', this.description )
		this.serpDescription.trigger( 'rank-math-vars-replaced' )
	}
}

export default SerpHooks
