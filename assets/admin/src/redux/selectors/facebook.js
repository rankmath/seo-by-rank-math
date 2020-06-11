/**
 * Get facebook title.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return facebook title.
 */
export function getFacebookTitle( state ) {
	return state.appData.facebookTitle
}

/**
 * Get facebook description.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return facebook description.
 */
export function getFacebookDescription( state ) {
	return state.appData.facebookDescription
}

/**
 * Get facebook author.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return facebook author.
 */
export function getFacebookAuthor( state ) {
	return state.appData.facebookAuthor
}

/**
 * Get facebook image id.
 *
 * @param {Object} state The app state.
 *
 * @return {number} Return facebook image id.
 */
export function getFacebookImageID( state ) {
	return state.appData.facebookImageID
}

/**
 * Get facebook image.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return facebook image.
 */
export function getFacebookImage( state ) {
	return state.appData.facebookImage
}

/**
 * Get facebook has overlay.
 *
 * @param {Object} state The app state.
 *
 * @return {boolean} Return facebook has overlay.
 */
export function getFacebookHasOverlay( state ) {
	return state.appData.facebookHasOverlay
}

/**
 * Get facebook image overlay.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return facebook image overlay.
 */
export function getFacebookImageOverlay( state ) {
	return '' !== state.appData.facebookImageOverlay
		? state.appData.facebookImageOverlay
		: 'play'
}
