/**
 * External dependencies
 */
import { isNull } from 'lodash'

/**
 * WordPress dependencies
 */
import { select, dispatch } from '@wordpress/data'
import { store as blockEditorStore } from '@wordpress/block-editor'

/**
 * Internal dependencies
 */
import { updateAppUi } from './metadata'

/**
 * Update the Autocompleter open state.
 *
 * @param {boolean} isOpen Autocompleter state.
 */
export function isAutoCompleterOpen( isOpen ) {
	if ( ! isOpen ) {
		return updateAppUi( 'isAutoCompleterOpen', isOpen )
	}

	const selectedBlock = select( 'core/block-editor' ).getSelectedBlock()
	if ( ! isNull( selectedBlock ) ) {
		const { removeBlock } = dispatch( blockEditorStore )
		removeBlock( selectedBlock.clientId )
	}

	return updateAppUi( 'isAutoCompleterOpen', isOpen )
}

/**
 * Update AI Attributes after selecting the value from the AI fields.
 *
 * @param {string} key   Attribute key.
 * @param {string} value Attribute value.
 */
export function updateAIAttributes( key, value ) {
	const attributes = select( 'rank-math-content-ai' ).getContentAiAttributes()
	attributes[ key ] = 'language' === key && ! value ? rankMath.contentAI.language : value
	return updateAppUi( 'contentAiAttributes', attributes )
}

/**
 * Update the Content AI data.
 *
 * @param {string} key   AI key.
 * @param {string} value AI value.
 */
export function updateData( key, value ) {
	const data = select( 'rank-math-content-ai' ).getData()
	data[ key ] = value
	return updateAppUi( 'data', { ...data } )
}

/**
 * Updates the `previousResults` in the Redux store when a new request
 * to generate the result is initiated from the FixWithAI component.
 *
 * @param {Object} value New result data to be added to the `previousResults`.
 */
export function updatePreviousResults( value ) {
	return updateAppUi( 'previousResults', value )
}
