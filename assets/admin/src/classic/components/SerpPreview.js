/**
 * External dependencies
 */
import $ from 'jquery'
import { debounce } from 'lodash'

/**
 * WordPress dependencies
 */
import { doAction } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import SerpHooks from './serp/SerpHooks'

class SerpPreview extends SerpHooks {
	/**
	 * Class constructor
	 */
	constructor() {
		super()

		if ( ! rankMath.canUser.general ) {
			return
		}

		this.hooks()
		this.elemMetabox = rankMathEditor.elemMetabox
		// SERP fields
		this.serpCanonical = this.elemMetabox.find( '#rank_math_canonical_url' )
		this.serpTitleField = this.elemMetabox.find( '#rank_math_title' )
		this.serpPermalinkField = this.elemMetabox.find(
			'#rank_math_permalink'
		)
		this.serpDescriptionField = this.elemMetabox.find(
			'#rank_math_description'
		)

		// Serp Preview Holder
		this.serpWrapper = this.elemMetabox.find( '.serp-preview' )
		this.serpFieldsWrapper = this.elemMetabox.find(
			'.rank-math-serp-fields-wrapper'
		)
		this.serpTitle = this.serpWrapper.find( '.serp-title' )
		this.serpPermalink = this.serpWrapper.find( '.serp-url' )
		this.serpDescription = this.serpWrapper.find( '.serp-description' )
		this.devices = this.elemMetabox.find( '.rank-math-select-device' )

		this.init()
	}

	init() {
		this.previewEvent()
		this.updateEvents()
		this.serpEvents()
		this.robotsEvents()
	}

	previewEvent() {
		this.devices.on( 'click', ( event ) => {
			event.preventDefault()
			const button = $( event.currentTarget )
			const device = ! button.hasClass( 'active' )
				? button.data( 'device' )
				: ''

			doAction( 'rank_math_preview_device', device )
		} )
	}

	// Device Selector
	serpEvents() {
		const snippetButton = this.elemMetabox.find( '.rank-math-edit-snippet' )

		// Edit Snippet
		snippetButton.on( 'click', ( e ) => {
			e.preventDefault()
			snippetButton.toggleClass( 'hidden active' )
			this.serpFieldsWrapper.toggleClass( 'hidden' )
		} )

		this.elemMetabox.on(
			'click',
			'.serp-title, .serp-url, .serp-description',
			( e ) => {
				e.preventDefault()
				snippetButton.toggleClass( 'hidden active' )
				this.serpFieldsWrapper.toggleClass( 'hidden' )
			}
		)
	}

	updateEvents() {
		this.serpTitleField
			.on(
				'input',
				debounce( () => {
					rankMathEditor.refresh( 'title' )
				}, 500 )
			)
			.on( 'keypress', ( event ) => {
				if ( 13 === event.which || 13 === event.keyCode ) {
					event.preventDefault()
					$( event.target )
						.closest( '.cmb-row' )
						.prev()
						.trigger( 'click' )
					return false
				}
			} )

		// Permalink
		this.serpPermalinkField
			.val(
				this.serpPermalinkField.val() ||
					rankMathEditor.assessor.dataCollector.getSlug()
			)
			.on( 'blur', () => {
				rankMathEditor.updatePermalink( this.serpPermalinkField.val() )
			} )
			.on(
				'input',
				debounce( () => {
					this.updatePermalinkPreview()
				}, 500 )
			)
			.on( 'keypress', ( event ) => {
				if ( 13 === event.which || 13 === event.keyCode ) {
					event.preventDefault()
					$( event.target )
						.closest( '.cmb-row' )
						.prev()
						.trigger( 'click' )
					return false
				}
			} )

		if ( rankMath.is_front_page ) {
			this.serpPermalinkField.val( '/' ).attr( 'disabled', 'disabled' )
		}

		// Description
		this.serpDescriptionField
			.on(
				'input',
				debounce( () => {
					rankMathEditor.refresh( 'content' )
				}, 500 )
			)
			.on( 'keypress', ( event ) => {
				if ( 13 === event.which || 13 === event.keyCode ) {
					event.preventDefault()
					$( event.target )
						.closest( '.cmb-row' )
						.prev()
						.trigger( 'click' )
					return false
				}
			} )
	}

	updatePreviewCallbacks( updating, value ) {
		updating = updating || 'global'

		rankMathEditor.assessor.dataCollector.updateData( updating, value )
		this.elemMetabox.trigger( 'rank-math-updating-preview-' + updating )
		this.elemMetabox.trigger( 'rank-math-' + updating + '-updated', value )
	}

	robotsEvents() {
		// Robots Index
		const isIndex = $( '#rank_math_robots1' )
		const isNoIndex = $( '#rank_math_robots2' )

		isIndex.on( 'change', () => {
			if ( isIndex.is( ':checked' ) ) {
				this.serpWrapper.addClass( 'noindex-preview' )
				isNoIndex.prop( 'checked', false ).trigger( 'change' )
			} else {
				this.serpWrapper.removeClass( 'noindex-preview' )
			}
		} )

		// Robots NoIndex
		isNoIndex
			.on( 'change', () => {
				if ( isNoIndex.is( ':checked' ) ) {
					this.serpWrapper.addClass( 'noindex-preview' )
					isIndex.prop( 'checked', false )
					return
				}

				this.serpWrapper.removeClass( 'noindex-preview' )
			} )
			.trigger( 'change' )
	}
}

export default SerpPreview
