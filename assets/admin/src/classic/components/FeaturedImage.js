/**
 * External dependencies
 */
import $ from 'jquery'
import { get, isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { doAction } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import isGutenbergAvailable from '@helpers/isGutenbergAvailable'

class FeaturedImage {
	constructor() {
		this.image_src = ''
		this.image_alt = ''
		this.getFeaturedImage()
		this.setImage()
		this.removeImage()

		if ( isGutenbergAvailable() ) {
			this.gutenbergImage()
		}
	}

	setImage() {
		if ( isUndefined( wp.media ) ) {
			return
		}

		const featuredImage = wp.media.featuredImage.frame()

		featuredImage.on( 'select', () => {
			let thumbnail = $( '#rank_math_post_thumbnail' )
			const attachment = featuredImage
				.state()
				.get( 'selection' )
				.first()
				.toJSON()

			this.sizeWarning( attachment )

			// Set social thumbnail
			if ( 1 > thumbnail.length ) {
				thumbnail = $( '<img id="rank_math_post_thumbnail" />' )
				$(
					'.facebook-thumbnail',
					'.rank-math-social-preview-image'
				).before( thumbnail )
			}

			if ( 'large' in attachment.sizes ) {
				thumbnail.attr( 'src', attachment.sizes.large.url )
			} else {
				thumbnail.attr( 'src', attachment.sizes.full.url )
			}
			this.setFeaturedImage( attachment )
		} )
	}

	removeImage() {
		$( '#postimagediv' ).on( 'click', '#remove-post-thumbnail', () => {
			$( '#rank_math_image_warning' ).remove()
			$( '#rank_math_post_thumbnail' ).remove()
			this.setFeaturedImage( '' )
		} )
	}

	gutenbergImage() {
		let imageData, previousImageData
		wp.data.subscribe( () => {
			const featuredImageId = wp.data
				.select( 'core/editor' )
				.getEditedPostAttribute( 'featured_media' )
			if ( ! this.isValidMediaId( featuredImageId ) ) {
				return
			}

			imageData = wp.data.select( 'core' ).getMedia( featuredImageId )
			if ( isUndefined( imageData ) ) {
				return
			}

			if ( imageData !== previousImageData ) {
				previousImageData = imageData
				this.setFeaturedImage( {
					url: imageData.guid.rendered,
					alt: imageData.alt_text,
				} )
			}
		} )
	}

	setFeaturedImage( attachment ) {
		const featuredImage = {
			src: get( attachment, 'url', '' ),
			alt: get( attachment, 'alt', '' ),
		}

		doAction( 'rank_math_updated_featured_image', featuredImage )
	}

	sizeWarning( attachment ) {
		// Size warning
		$( '#rank_math_image_warning' ).remove()

		if ( 200 < attachment.width && 200 < attachment.height ) {
			return
		}

		const postImageDiv = $( '#postimagediv' )
		const postImageHeading = postImageDiv.find( '.hndle' )
		$(
			'<div id="rank_math_image_warning" class="notice notice-error notice-alt"><p>' +
				rankMath.featuredImageNotice +
				'</p></div>'
		).insertAfter( postImageHeading )
	}

	/**
	 * Get featued image.
	 *
	 */
	getFeaturedImage() {
		const featuredImage = $( '#postimagediv img' )

		if ( featuredImage && ! featuredImage.length ) {
			return
		}
		const featuredImageData = {
			url: featuredImage[ 0 ].src,
			alt: featuredImage[ 0 ].alt,
		}

		this.setFeaturedImage( featuredImageData )
	}

	isValidMediaId( featuredImageId ) {
		return 'number' === typeof featuredImageId && 0 < featuredImageId
	}
}

export default FeaturedImage
