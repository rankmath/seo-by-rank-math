/**
 * Get redirection id.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return redirection id.
 */
export function getRedirectionID( state ) {
	return state.appData.redirectionID
}

/**
 * Get redirection type.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return redirection type.
 */
export function getRedirectionType( state ) {
	return state.appData.redirectionType
}

/**
 * Get redirection url.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return redirection url.
 */
export function getRedirectionUrl( state ) {
	return state.appData.redirectionUrl
}

/**
 * Get redirection item.
 *
 * @param {Object} state The app state.
 *
 * @return {Object} Return selected keyword.
 */
export function getRedirectionItem( state ) {
	return state.appUi.redirectionItem
}

/**
 * Has redirect.
 *
 * @param {Object} state The app state.
 *
 * @return {boolean} Return redirect.
 */
export function hasRedirect( state ) {
	return state.appUi.hasRedirect
}
