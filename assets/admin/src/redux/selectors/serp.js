import decodeEntities from '@helpers/decodeEntities'

/**
 * Get serp title.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return post serp title.
 */
export function getSerpTitle( state ) {
	return decodeEntities( state.appUi.serpTitle )
}

/**
 * Get serp description.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return serp description.
 */
export function getSerpDescription( state ) {
	return state.appUi.serpDescription
}

/**
 * Get snippet editor state.
 *
 * @param {Object} state The app state.
 *
 * @return {boolean} Return snippet editor state.
 */
export function isSnippetEditorOpen( state ) {
	return state.appUi.isSnippetEditorOpen
}

/**
 * Get snippet preview type.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return snippet preview type.
 */
export function getSnippetPreviewType( state ) {
	return state.appUi.snippetPreviewType
}
