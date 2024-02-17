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

export function updateAIAttributes( key, value ) {
	const attributes = select( 'rank-math-content-ai' ).getContentAiAttributes()
	attributes[ key ] = 'language' === key && ! value ? rankMath.ca_language : value
	return updateAppUi( 'contentAiAttributes', attributes )
}
