/**
 * External dependencies
 */
import { isEmpty, isNull } from 'lodash'

/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element'
import { dispatch, useDispatch, useSelect } from '@wordpress/data'
import { useShortcut, store as keyboardShortcutsStore } from '@wordpress/keyboard-shortcuts'
import { createBlock } from '@wordpress/blocks'

/**
 * Internal dependencies
 */
import getWriteAttributes from '../helpers/getWriteAttributes'
import getSelectedBlock from '../helpers/getSelectedBlock'
import insertCommandBox from '../shortcutCommand/insertCommandBox'
import getBlockContent from '../helpers/getBlockContent'

export default () => {
	useShortcut(
		'rank-math-contentai-write',
		useCallback( () => {
			const selectedBlock = getSelectedBlock()

			const newBlock = createBlock( 'rank-math/command' )
			if ( isEmpty( selectedBlock.block ) || getBlockContent( selectedBlock.block ) ) {
				dispatch( 'core/block-editor' ).insertBlock( newBlock, selectedBlock.position )
			} else {
				dispatch( 'core/block-editor' ).replaceBlock( selectedBlock.clientId, newBlock )
			}

			insertCommandBox( 'Write', getWriteAttributes(), newBlock.clientId )
		}, [] )
	)

	const shortcut = useSelect(
		( select ) => select( keyboardShortcutsStore ).getShortcutKeyCombination( 'rank-math-contentai-write' ), []
	)
	if ( ! isNull( shortcut ) ) {
		return null
	}

	const { registerShortcut } = useDispatch( keyboardShortcutsStore )
	registerShortcut( {
		name: 'rank-math-contentai-write',
		category: 'global',
		keyCombination: {
			modifier: 'ctrl',
			character: '/',
		},
	} )
}
