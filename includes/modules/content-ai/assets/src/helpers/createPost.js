/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch'

export default ( content, title = '' ) => {
	apiFetch( {
		method: 'POST',
		path: '/rankmath/v1/ca/createPost',
		data: {
			content,
			title,
		},
	} )
		.catch( ( error ) => {
			console.log( error )
		} )
		.then( ( response ) => {
			window.location.href = response
		} )
}
