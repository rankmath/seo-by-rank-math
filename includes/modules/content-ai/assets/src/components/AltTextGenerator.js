/**
 * External dependencies
 */
import jQuery from 'jquery'
import { isEmpty, isUndefined, last } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import apiFetch from '@wordpress/api-fetch'

// Update credits shown on the page.
const updateCredits = ( result ) => {
	if ( isUndefined( result.credits ) ) {
		return
	}

	let credits = result.credits
	if ( isEmpty( credits ) ) {
		return
	}

	credits = credits.available - credits.taken
	credits = credits < 0 ? 0 : credits

	apiFetch( {
		method: 'POST',
		path: '/rankmath/v1/ca/updateCredits',
		data: {
			credits,
		},
	} )
		.then( () => {
			wp.data.dispatch( 'rank-math-content-ai' ).updateData( 'credits', credits )

			if ( jQuery( '.credits-remaining' ).length ) {
				jQuery( '.credits-remaining strong' ).text( credits )
			}
		} )
}

/**
 * Convert image URL to base64 encoded data
 *
 * @param {string} imageUrl Image URL to convert.
 * @return {Promise<string>} Base64 encoded image data.
 */
const convertImageToBase64 = ( imageUrl ) => {
	return new Promise( ( resolve, reject ) => {
		const img = new window.Image()
		img.crossOrigin = 'anonymous'

		img.onload = () => {
			const canvas = document.createElement( 'canvas' )
			const ctx = canvas.getContext( '2d' )
			canvas.width = img.width
			canvas.height = img.height
			ctx.drawImage( img, 0, 0 )

			try {
				// Detect image format from URL
				const urlParts = imageUrl.toLowerCase().split( '.' )
				const extension = urlParts[ urlParts.length - 1 ]
				let mimeType = 'image/jpeg' // default

				// Map file extensions to MIME types
				switch ( extension ) {
					case 'png':
						mimeType = 'image/png'
						break
					case 'gif':
						mimeType = 'image/gif'
						break
					case 'webp':
						mimeType = 'image/webp'
						break
					case 'svg':
						mimeType = 'image/svg+xml'
						break
					case 'jpg':
					case 'jpeg':
					default:
						mimeType = 'image/jpeg'
						break
				}

				const dataURL = canvas.toDataURL( mimeType )
				resolve( dataURL )
			} catch ( error ) {
				reject( error )
			}
		}

		img.onerror = () => {
			reject( new Error( 'Failed to load image' ) )
		}

		img.src = imageUrl
	} )
}

/**
 * Extract filename from image URL
 *
 * @param {string} imageUrl Image URL.
 * @return {string} Filename.
 */
const getImageId = ( imageUrl ) => {
	const urlParts = imageUrl.split( '/' )
	return last( urlParts ) || 'image.jpg'
}

/**
 * Alt Text Generator - Send Request to generate Alt text for the given Image URL
 *
 * @param {string} imageUrl Image URL to generate the alt from.
 */
export default ( imageUrl ) => {
	return new Promise( ( resolve, reject ) => {
		const data = wp.data.select( 'rank-math-content-ai' ).getData()

		// Convert image to base64 and prepare the request
		convertImageToBase64( imageUrl )
			.then( ( base64Data ) => {
				const imageId = getImageId( imageUrl )

				fetch( data.url + 'generate_image_alt_v2', {
					method: 'POST',
					body: JSON.stringify(
						{
							images: [ { id: imageId, image: base64Data } ],
							username: data.connectData.username,
							api_key: data.connectData.api_key,
							site_url: data.connectData.site_url,
							plugin_version: rankMath.version,
							language: data.language,
						}
					),
					headers: { 'Content-Type': 'application/json' },
				} )
					.then( ( response ) => response.json() )
					.then( ( response ) => {
						if ( response.altTexts && response.altTexts[ imageId ] ) {
							resolve( response.altTexts[ imageId ] )
							updateCredits( response )
							return
						}

						reject( __( 'Failed to generate alt text.', 'rank-math' ) )
					} )
					.catch( ( error ) => {
						reject( error )
					} )
			} )
			.catch( ( error ) => {
				reject( error )
			} )
	} )
}
