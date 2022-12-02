/**
 * Internal dependencies
 */
import { updateAppUi } from './metadata'
import { swapVariables } from '@helpers/swapVariables'
import generateDescription from '@helpers/generateDescription'

/**
 * Update title.
 *
 * @param {string} title Title to update.
 *
 * @return {Object} An action for redux.
 */
export function updateSerpTitle( title ) {
	title = swapVariables.swap(
		'' !== title ? title : rankMath.assessor.serpData.titleTemplate
	)
	rankMathEditor.refresh( 'title' )
	return updateAppUi( 'serpTitle', title )
}

/**
 * Update title.
 *
 * @param {string} slug Title to update.
 *
 * @return {Object} An action for redux.
 */
export function updateSerpSlug( slug ) {
	slug = '' !== slug ? slug : rankMathEditor.assessor.dataCollector.getSlug()
	rankMathEditor.refresh( 'permalink' )
	return updateAppUi( 'serpSlug', slug )
}

/**
 * Update description.
 *
 * @param {string} description Description to update.
 *
 * @return {Object} An action for redux.
 */
export function updateSerpDescription( description ) {
	description = swapVariables.swap( generateDescription( description ) )
	rankMathEditor.refresh( 'description' )
	return updateAppUi( 'serpDescription', description )
}

/**
 * Toggle snippet editor state.
 *
 * @param {boolean} state The snippet preview modal state.
 *
 * @return {Object} An action for redux.
 */
export function toggleSnippetEditor( state ) {
	return updateAppUi( 'isSnippetEditorOpen', state )
}

/**
 * Update snippet preview type.
 *
 * @param {number} type The snippet preview type.
 *
 * @return {Object} An action for redux.
 */
export function updateSnippetPreviewType( type ) {
	return updateAppUi( 'snippetPreviewType', type )
}
