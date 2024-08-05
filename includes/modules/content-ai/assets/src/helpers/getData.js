/**
 * External dependencies
 */
import jQuery from 'jquery'
import { isEmpty, isUndefined, merge } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
// eslint-disable-next-line import/default
import apiFetch from '@wordpress/api-fetch'
import { select, dispatch } from '@wordpress/data'

const errorMessage = __( 'Sorry, the request has failed. If the issue persists, please contact our Support for assistance.', 'rank-math' )

// Update credits shown on the page.
const updateCredits = ( result, setCredits ) => {
	if ( isUndefined( result.credits ) ) {
		return
	}

	let credits = result.credits
	if ( isEmpty( credits ) ) {
		return
	}

	credits = credits.available - credits.taken
	credits = credits < 0 ? 0 : credits
	// creditsRemaining = credits
	if ( setCredits ) {
		setCredits( credits )
	}

	dispatch( 'rank-math-content-ai' ).updateData( 'credits', credits )

	if ( jQuery( '.credits-remaining' ).length ) {
		jQuery( '.credits-remaining strong' ).text( credits )
	}
}

// Send request to the API.
const callAPI = ( { endpoint, attributes, callback, isChat, setCredits, repeat = 0, data = {} } ) => {
	// Reattempt and display error messages.
	const handleErrors = ( { error } ) => {
		if ( repeat < 2 && 'could_not_generate' === error.code ) {
			callAPI( { repeat: repeat + 1 } )
			return
		}

		const errorMessages = data.errors
		callback( { error: ! isUndefined( errorMessages[ error.code ] ) ? errorMessages[ error.code ] : errorMessage } )
	}

	// Save API output in the Database.
	const saveOutput = ( { result } ) => {
		const outputData = {
			endpoint,
			attributes,
			outputs: ! isEmpty( result.meta ) ? result.meta : result.results,
			isChat,
		}

		if ( ! isUndefined( result.credits ) ) {
			outputData.credits = {
				credits: result.credits,
				plan: result.plan,
				refreshDate: result.refreshDate,
			}
		}

		apiFetch( {
			method: 'POST',
			path: '/rankmath/v1/ca/saveOutput',
			data: outputData,
		} )
			.then( ( response ) => {
				if ( ! isChat && ! isUndefined( data.history ) ) {
					// rankMath.contentAI.history = response
					dispatch( 'rank-math-content-ai' ).updateData( 'history', response )
				}
			} )
			.catch( ( error ) => {
				// eslint-disable-next-line no-console
				console.log( error )
			} )
	}

	jQuery.ajax(
		{
			url: data.url + endpoint,
			type: 'POST',
			data: attributes,
			success: ( result ) => {
				if ( ! isEmpty( result.error ) ) {
					handleErrors( { error: result.error } )
					return
				}

				saveOutput( { result } )

				const response = ! isEmpty( result.meta ) ? result.meta : result.results
				if ( ! isEmpty( result.warning ) ) {
					const errorMessages = data.errors
					response.push( { warning: ! isUndefined( errorMessages[ result.warning ] ) ? errorMessages[ result.warning ] : errorMessage } )
				}

				callback( response )
				updateCredits( result, setCredits )
			},
			error: ( jqXHR ) => {
				// Try to parse the response payload.
				try {
					const result = JSON.parse( jqXHR.responseText )
					if ( ! isEmpty( result.err_key ) ) {
						if ( 'not_found' === result.err_key && repeat < 2 ) {
							apiFetch( {
								method: 'GET',
								path: '/rankmath/v1/ca/migrateUser',
							} )
								.then( ( response ) => {
									if ( 'completed' === response ) {
										callAPI(
											{
												endpoint,
												attributes,
												callback,
												isChat,
												setCredits,
												repeat: repeat + 1,
												data,
											}
										)
									}
								} )
								.catch( ( error ) => {
									// eslint-disable-next-line no-console
									console.log( error )
								} )
							return
						}

						const errorMessages = data.errors
						callback( { error: ! isUndefined( errorMessages[ result.err_key ] ) ? errorMessages[ result.err_key ] : errorMessage } )
						return
					}
				} catch ( error ) {
					// Fallback to a generic error message if try statement fails.
					callback( { error: errorMessage } )
				}

				// Fallback to a generic error message if parsing fails or if there's no error message in the payload.
				callback( { error: errorMessage } )
			},
		}
	)
}

/**
 * Function to get the data from API.
 *
 * @param {string}   endpoint   Endpoint to get the content from.
 * @param {Object}   attributes Attributes data.
 * @param {Function} callback   Function to run after getting the content from API.
 * @param {boolean}  isChat     Whether the requested content is a chat message.
 * @param {Function} setCredits Function to update the credits count.
 */
export default ( endpoint, attributes = {}, callback, isChat = false, setCredits = '' ) => {
	const store = select( 'rank-math-content-ai' ).getData()
	const data = store.connectData
	attributes = merge(
		attributes,
		{
			username: data.username,
			api_key: data.api_key,
			site_url: data.site_url,
			plugin_version: rankMath.version,
			debug: '1',
		}
	)

	// Early Bail if a site has exhausted the credits.
	if ( ! store.credits ) {
		callback( { error: store.errors.account_limit_reached } )
		return
	}

	if ( isUndefined( attributes.language ) || ! attributes.language ) {
		attributes.language = store.language
	}

	callAPI(
		{
			endpoint,
			attributes,
			callback,
			isChat,
			setCredits,
			data: store,
		}
	)
}
