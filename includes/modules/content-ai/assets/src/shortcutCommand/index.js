/**
 * External dependencies
 */
import { startsWith } from 'lodash'

/**
 * WordPress dependencies
 */
import { select, dispatch, subscribe } from '@wordpress/data'
import { createBlock } from '@wordpress/blocks'
import { store as blockEditorStore } from '@wordpress/block-editor'

/**
 * Internal dependencies
 */
import getBlockContent from '../helpers/getBlockContent'

const convertToCommandBlock = ( block ) => {
	const content = getBlockContent( block )
	if ( content && startsWith( content.trim(), '//' ) ) {
		const newBlock = createBlock( 'rank-math/command', {
			// Remove the `//` from the content before creating the new block.
			content: '',
			className: 'rank-math-content-ai-command',
		} )
		dispatch( blockEditorStore ).replaceBlock( block.clientId, newBlock )
	}
}

export default () => {
	let previouslySelectedBlockId = null
	let previouslySelectedBlockContent = null

	subscribe( () => {
		const currentSelectedBlock = select( blockEditorStore ).getSelectedBlock()
		const currentSelectedBlockId = currentSelectedBlock ? currentSelectedBlock.clientId : null
		const currentSelectedBlockContent = currentSelectedBlock ? getBlockContent( currentSelectedBlock ) : null

		// Condition to trigger the conversion logic:
		// 1. The selected block has changed.
		// 2. Or, the content of the current block has changed.
		if ( currentSelectedBlockId !== previouslySelectedBlockId ||
			currentSelectedBlockContent !== previouslySelectedBlockContent ) {
			if ( currentSelectedBlock && currentSelectedBlock.name === 'core/paragraph' ) {
				convertToCommandBlock( currentSelectedBlock )
			}
		}

		// Always update our "previous" state variables for the next run.
		previouslySelectedBlockId = currentSelectedBlockId
		previouslySelectedBlockContent = currentSelectedBlockContent
	} )
}
