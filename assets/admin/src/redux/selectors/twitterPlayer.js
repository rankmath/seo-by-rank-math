/**
 * Get twitter player url.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return twitter player url.
 */
export function getTwitterPlayerUrl( state ) {
	return state.appData.twitterPlayerUrl
}

/**
 * Get twitter player size.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return twitter player size.
 */
export function getTwitterPlayerSize( state ) {
	return state.appData.twitterPlayerSize
}

/**
 * Get twitter player stream.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return twitter player stream.
 */
export function getTwitterPlayerStream( state ) {
	return state.appData.twitterPlayerStream
}

/**
 * Get twitter player stream content type.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return twitter player stream content type.
 */
export function getTwitterPlayerStreamCtype( state ) {
	return state.appData.twitterPlayerStreamCtype
}
