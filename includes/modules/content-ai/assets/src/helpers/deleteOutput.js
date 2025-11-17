/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch'

/**
 * Function to delete Chat & History output.
 *
 * @param {boolean} isChat Whether the deleting output is a Chat message.
 * @param {index}   index  Chat/History array index to delete.
 */
export default ( isChat = false, index = 0 ) => {
	apiFetch( {
		method: 'POST',
		path: '/rankmath/v1/ca/deleteOutput',
		data: {
			isChat,
			index,
		},
	} )
		.catch( ( error ) => {
			console.log( error )
		} )
}
