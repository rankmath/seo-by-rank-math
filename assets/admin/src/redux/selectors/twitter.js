/**
 * Get twitter image overlay.
 *
 * @param {Object} state The app state.
 *
 * @return {boolean} Return twitter image overlay.
 */
export function getTwitterUseFacebook( state ) {
	return state.appData.twitterUseFacebook
}

/**
 * Get twitter card type.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return twitter card type.
 */
export function getTwitterCardType( state ) {
	return state.appData.twitterCardType
}

/**
 * Get twitter title.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return twitter title.
 */
export function getTwitterTitle( state ) {
	return state.appData.twitterTitle
}

/**
 * Get twitter description.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return twitter description.
 */
export function getTwitterDescription( state ) {
	return state.appData.twitterDescription
}

/**
 * Get twitter author.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return twitter author.
 */
export function getTwitterAuthor( state ) {
	return state.appData.twitterAuthor
}

/**
 * Get twitter image id.
 *
 * @param {Object} state The app state.
 *
 * @return {number} Return twitter image id.
 */
export function getTwitterImageID( state ) {
	return state.appData.twitterImageID
}

/**
 * Get twitter image.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return twitter image.
 */
export function getTwitterImage( state ) {
	return state.appData.twitterImage
}

/**
 * Get twitter has overlay.
 *
 * @param {Object} state The app state.
 *
 * @return {boolean} Return twitter has overlay.
 */
export function getTwitterHasOverlay( state ) {
	return state.appData.twitterHasOverlay
}

/**
 * Get twitter image overlay.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return twitter image overlay.
 */
export function getTwitterImageOverlay( state ) {
	return '' !== state.appData.twitterImageOverlay
		? state.appData.twitterImageOverlay
		: 'play'
}
