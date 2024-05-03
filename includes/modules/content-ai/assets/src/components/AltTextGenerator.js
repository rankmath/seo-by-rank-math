/**
 * External dependencies
 */
import jQuery from 'jquery'
import { isEmpty, isUndefined } from 'lodash'

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
			if ( ! isUndefined( rankMath.ca_credits ) ) {
				rankMath.ca_credits = credits
			}

			if ( ! isUndefined( rankMath.contentAICredits ) ) {
				rankMath.contentAICredits = credits
			}

			if ( jQuery( '.credits-remaining' ).length ) {
				jQuery( '.credits-remaining strong' ).text( credits )
			}
		} )
}

/**
 * Alt Text Generator - Send Request to generate Alt text for the given Image URL
 *
 * @param {string} imageUrl Image URL to generate the alt from.
 */
export default ( imageUrl ) => {
	return new Promise( ( resolve, reject ) => {
		const data = rankMath.connectData

		fetch( rankMath.contentAiUrl + 'generate_image_alt', {
			method: 'POST',
			body: JSON.stringify(
				{
					images: [ imageUrl ],
					username: data.username,
					api_key: data.api_key,
					site_url: data.site_url,
					plugin_version: rankMath.version,
				}
			),
			headers: { 'Content-Type': 'application/json' },
		} )
			.then( ( response ) => response.json() )
			.then( ( response ) => {
				if ( response.altTexts && response.altTexts[ imageUrl ] ) {
					resolve( response.altTexts[ imageUrl ] )
					updateCredits( response )
					return
				}

				reject( __( 'Failed to generate alt text.', 'rank-math' ) )
			} )
			.catch( ( error ) => {
				reject( error )
			} )
	} )
}
