/**
 * External dependencies
 */
import $ from 'jquery'
import { isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { addAction } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import { swapVariables } from '@helpers/swapVariables'
import decodeEntities from '@helpers/decodeEntities'

class SocialFields {
	/**
	 * Class constructor
	 */
	constructor() {
		this.elemMetabox = rankMathEditor.elemMetabox
		// Social Fields
		this.currentNetwork = 'facebook'
		this.useFacebook = true
		this.shouldUpdatePreview = false
		this.facebookTitleField = this.elemMetabox.find(
			'#rank_math_facebook_title'
		)
		this.facebookDescriptionField = this.elemMetabox.find(
			'#rank_math_facebook_description'
		)
		this.twitterTitleField = this.elemMetabox.find(
			'#rank_math_twitter_title'
		)
		this.twitterDescriptionField = this.elemMetabox.find(
			'#rank_math_twitter_description'
		)
		this.facebookImageField = this.elemMetabox.find(
			'#rank_math_facebook_image'
		)
		this.twitterImageField = this.elemMetabox.find(
			'#rank_math_twitter_image'
		)
		this.facebookImageStatus = this.elemMetabox.find(
			'#rank_math_facebook_image-status'
		)
		this.twitterImageStatus = this.elemMetabox.find(
			'#rank_math_twitter_image-status'
		)

		// Social Preview Holder
		this.socialWrapper = this.elemMetabox.find(
			'.rank-math-social-preview'
		)
		this.socialPreview = this.socialWrapper.find(
			'.rank-math-social-preview-item'
		)
		this.socialTitle = this.socialWrapper.find(
			'.rank-math-social-preview-title'
		)
		this.socialDescription = this.socialWrapper.find(
			'.rank-math-social-preview-description'
		)

		this.events()

		this.shouldUpdatePreview = true
		this.updateTitlePreview = this.updateTitlePreview.bind( this )
		this.updateDescriptionPreview = this.updateDescriptionPreview.bind(
			this
		)
		this.updateThumbnailPreview = this.updateThumbnailPreview.bind( this )
		this.updatePreview = this.updatePreview.bind( this )
		addAction( 'rank_math_init_refresh', 'rank-math', this.updatePreview )

		addAction(
			'rank_math_title_refresh',
			'rank-math',
			this.updateTitlePreview,
			2
		)
		addAction(
			'rank_math_content_refresh',
			'rank-math',
			this.updateDescriptionPreview,
			2
		)
		addAction(
			'rank_math_featuredImage_refresh',
			'rank-math',
			this.updateThumbnailPreview
		)
		this.updatePreview()
	}

	events() {
		// Facebook
		this.facebookTitleField.on( 'input', () => {
			this.updateTitlePreview()
		} )

		this.facebookDescriptionField.on( 'input', () => {
			this.updateDescriptionPreview()
		} )

		this.facebookImageField.on( 'input', () => {
			this.updateThumbnailPreview()
		} )

		//Twitter
		this.twitterTitleField.on( 'input', () => {
			this.updateTitlePreview()
		} )

		this.twitterDescriptionField.on( 'input', () => {
			this.updateDescriptionPreview()
		} )

		this.twitterImageField.on( 'input', () => {
			this.updateThumbnailPreview()
		} )

		// Preview Button
		const previewButton = $( '.rank-math-social-preview-button' ),
			icon = previewButton.find( '>span' )

		previewButton.on( 'click', ( event ) => {
			event.preventDefault()

			icon.toggleClass( 'dashicons-arrow-down dashicons-arrow-up' )
			this.updatePreview()
			this.socialWrapper.toggleClass( 'open' )
			this.socialPreview.slideToggle()
		} )

		// Preview Tab Buttons
		const cardSelector = $( '#rank_math_twitter_card_type' )
		$( '.preview-network' ).on( 'click', ( event ) => {
			if ( $( event.target ).hasClass( 'tab-facebook' ) ) {
				previewButton.show()
			} else {
				cardSelector.trigger( 'change' )
			}
			this.updatePreview()
		} )

		// Player Only
		cardSelector
			.on( 'change', () => {
				const val = cardSelector.val()

				if ( 'player' === val ) {
					$(
						'.cmb2-id-rank-math-twitter-image, .cmb2-id-rank-math-twitter-title, .cmb2-id-rank-math-twitter-description'
					).show()
				}

				// Show / Hide preview button
				const isPlayerApp = 'player' === val || 'app' === val
				previewButton.toggle( ! isPlayerApp )
				$( '.cmb2-id-rank-math-twitter-use-facebook' ).toggle(
					! isPlayerApp
				)

				if ( ! isPlayerApp ) {
					this.updatePreview()
				}
			} )
			.trigger( 'change' )

		// Use Facebook data for Twitter
		const useFacebook = $( '#rank_math_twitter_use_facebook' )
		useFacebook.on( 'input change', () => {
				this.useFacebook = useFacebook.is( ':checked' )
				this.updatePreview()
			} )
			.trigger( 'change' )

		//  Change Overlay Icons
		$(
			'.cmb2-id-rank-math-facebook-enable-image-overlay, .cmb2-id-rank-math-facebook-image-overlay, .cmb2-id-rank-math-twitter-enable-image-overlay, .cmb2-id-rank-math-twitter-image-overlay'
		).on( 'change', 'input', () => {
			this.updateThumbnailOverlay()
		} )

		// Change for image fields
		$( document ).on(
			'cmb_media_modal_select',
			( event, selection, media ) => {
				if (
					'rank_math_facebook_image' === media.field ||
					'rank_math_twitter_image' === media.field
				) {
					this.updateThumbnailPreview()
				}
			}
		)

		$( document ).on( 'cmb_init', ( event, cmb ) => {
			cmb.$metabox.on( 'click', '.cmb2-remove-file-button', () => {
				this.updateThumbnailPreview()
			} )
		} )
	}

	updatePreview() {
		if (
			false === this.shouldUpdatePreview ||
			! $( '.preview-network.tab-active' ).length
		) {
			return
		}

		this.currentNetwork = $( '.preview-network.tab-active' )
			.attr( 'href' )
			.replace( '#setting-panel-social-', '' )

		this.socialWrapper.removeClass()
		this.socialWrapper.addClass(
			'rank-math-social-preview rank-math-social-preview-' +
				this.currentNetwork
		)

		if ( 'twitter' === this.currentNetwork ) {
			this.socialWrapper.addClass(
				$( '#rank_math_twitter_card_type' ).val()
			)
		}

		this.updateTitlePreview()
		this.updateDescriptionPreview()
		this.updateThumbnailPreview()
	}

	updateTitlePreview() {
		let title =
			'twitter' === this.currentNetwork && this.useFacebook
				? this.facebookTitleField.val()
				: this[ this.currentNetwork + 'TitleField' ].val()

		if ( '' !== title ) {
			title = this.truncate( swapVariables.swap( title ), 90 )
		} else {
			title = decodeEntities(
				rankMathEditor.assessor.dataCollector.getData( 'title' )
			)
			this.facebookTitleField.attr( 'placeholder', title )
			this.twitterTitleField.attr( 'placeholder', title )
		}

		this.socialTitle.text( title )
		if ( 'facebook' === this.currentNetwork ) {
			this.hideDescription()
		}
	}

	updateDescriptionPreview() {
		let description =
			'twitter' === this.currentNetwork && this.useFacebook
				? this.facebookDescriptionField.val()
				: this[ this.currentNetwork + 'DescriptionField' ].val()
		if ( '' !== description ) {
			description = this.truncate(
				swapVariables.swap( description ),
				240
			)
		} else {
			description = rankMathEditor.assessor.dataCollector.getData(
				'description'
			)
			this.facebookDescriptionField.attr( 'placeholder', description )
			this.twitterDescriptionField.attr( 'placeholder', description )
		}

		this.socialDescription.text(
			! isUndefined( description ) ? description : ''
		)
	}

	updateThumbnailPreview() {
		const thumbnail = $( '#rank_math_post_thumbnail' ).attr( 'src' ),
			content = rankMathEditor.assessor.dataCollector.getData(
				'content'
			),
			contentImage = /<img(?:[^>]+)?>/.test( content )
				? $( content )
					.find( 'img:first' )
					.attr( 'src' )
				: false,
			facebook =
				this.facebookImageField.val() ||
				thumbnail ||
				contentImage ||
				rankMath.defautOgImage
		let twitter =
			this.twitterImageField.val() ||
			thumbnail ||
			contentImage ||
			rankMath.defautOgImage

		if ( 'twitter' === this.currentNetwork && this.useFacebook ) {
			twitter = facebook
		} else if (
			this.twitterImageStatus.find( 'img.cmb-file-field-image' ).length
		) {
			const notice = this.twitterImageStatus.siblings( '.notice' ),
				image = this.twitterImageStatus.find(
					'img.cmb-file-field-image'
				)[ 0 ]

			notice.addClass( 'hidden' )
			$( image ).on( 'load', function() {
				if ( 200 > image.naturalWidth || 200 > image.naturalHeight ) {
					notice.removeClass( 'hidden' )
				}
			} )
		}

		const fbNotice = this.facebookImageStatus.siblings( '.notice' ),
			fbImage = this.facebookImageStatus.find(
				'img.cmb-file-field-image'
			)

		fbNotice.addClass( 'hidden' )

		if (
			'facebook' === this.currentNetwork &&
			this.facebookImageField.val() &&
			fbImage.length
		) {
			$( fbImage[ 0 ] ).on( 'load', function() {
				if (
					200 > fbImage[ 0 ].naturalWidth ||
					200 > fbImage[ 0 ].naturalHeight
				) {
					fbNotice.removeClass( 'hidden' )
				}
			} )
		}

		const preview = $( '.rank-math-social-preview-image' )
		$( '.facebook-thumbnail', preview ).attr( 'src', facebook )
		$( '.twitter-thumbnail', preview ).attr( 'src', twitter )

		preview.toggleClass( 'no-facebook-image', ! facebook )
		preview.toggleClass( 'no-twitter-image', ! twitter )
		preview
			.parents( '.rank-math-social-preview-facebook' )
			.find( '.error-msg' )
			.toggleClass( 'show', ! facebook )
		preview
			.parents( '.rank-math-social-preview-twitter' )
			.find( '.error-msg' )
			.toggleClass( 'show', ! twitter )

		this.updateThumbnailOverlay()
	}

	updateThumbnailOverlay() {
		const overlay = $( '.rank-math-social-preview-image-overlay' ),
			isOverlay = $(
				'[name="rank_math_' +
					this.currentNetwork +
					'_enable_image_overlay"]:checked'
			).val(),
			preview = $( '.rank-math-social-preview-image' )

		let showOverlay = ! preview.hasClass(
				'no-' + this.currentNetwork + '-image'
			),
			iconOverlay = $(
				'[name="rank_math_' +
					this.currentNetwork +
					'_image_overlay"]:checked'
			).val()

		if ( 'twitter' === this.currentNetwork && this.useFacebook ) {
			iconOverlay = $(
				'[name="rank_math_facebook_image_overlay"]:checked'
			).val()
			showOverlay = false
		}

		const currentNetwork = $(
			'.cmb2-id-rank-math-' +
				this.currentNetwork +
				'-enable-image-overlay'
		)
		const showNotice =
			'on' === currentNetwork.find( 'input:checked' ).val() ? true : false
		currentNetwork.toggle( showOverlay )
		currentNetwork.find( '.notice-warning' ).toggle( showNotice )

		if ( 'on' === isOverlay ) {
			overlay
				.attr( 'src', rankMath.overlayImages[ iconOverlay ].url )
				.show()

			$(
				'.cmb2-id-rank-math-' + this.currentNetwork + '-image-overlay'
			).toggle( showOverlay )
		} else {
			overlay.hide()
		}
	}

	// Hide FB Description if title breaks into new line.
	hideDescription() {
		this.socialDescription.removeClass( 'hidden' )

		if ( this.socialTitle.height() > 22 ) {
			this.socialDescription.addClass( 'hidden' )
		}
	}

	truncate( str, length ) {
		return str.length > length ? str.substring( 0, length ) : str
	}
}
export default SocialFields
