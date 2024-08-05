/* global getSelection */

/**
 * External dependencies
 */
import jQuery from 'jquery'

/**
 * WordPress dependencies
 */
import { select, dispatch } from '@wordpress/data'

/**
 * Internal dependencies
 */
import getTypingWorker from '../helpers/getTypingWorker'
import getBlockContent from '../helpers/getBlockContent'

/**
 * Fix cursor position at the end when the content is being appended to the editor.
 *
 * @param {string} wrapper The wrapper where the content is being appended.
 */
const changeCursorPosition = ( wrapper ) => {
	const selection = getSelection()
	const range = document.createRange()
	selection.removeAllRanges()
	range.selectNodeContents( wrapper )
	range.collapse( false )
	selection.addRange( range )
}

/**
 * Function to add the cotent in the editor with typing effect.
 *
 * @param {string} content         Content to insert in the editor.
 * @param {string} selectionID     Block ID
 * @param {string} appendContent   Content to append after typing effect is complete
 * @param {string} existingContent Existing content to add before the generated content
 */
export default ( content, selectionID = '', appendContent = false, existingContent = '' ) => {
	// Add content in Elementor editor.
	if ( existingContent ) {
		dispatch( 'core/block-editor' ).updateBlockAttributes(
			selectionID,
			{
				content: existingContent,
			}
		)
	}

	const typingWorker = getTypingWorker()
	typingWorker.onmessage = ( event ) => {
		const value = event.data
		if ( ! value ) {
			return
		}

		if ( 'classic' === rankMath.currentEditor ) {
			tinymce.activeEditor.insertContent( 'rank_math_process_complete' !== value ? ' ' + value : '' )
			return
		}

		// Add content in Block & Content editor.
		if ( ! selectionID ) {
			return
		}

		const selectedBlock = select( 'core/block-editor' ).getBlock( selectionID )

		const isBr = '<br>' === value
		let blockContent = getBlockContent( selectedBlock )

		if ( 'rank_math_process_complete' === value ) {
			if ( appendContent ) {
				dispatch( 'core/block-editor' ).updateBlockAttributes(
					selectedBlock.clientId,
					{
						content: blockContent + appendContent,
						className: '',
					}
				)

				setTimeout( () => {
					jQuery( '.rank-math-content-ai-command-buttons .rank-math-content-ai-use' ).trigger( 'focus' )
				}, 100 )
			}
			return
		}

		if ( blockContent ) {
			blockContent += '<br>' !== value && ! isBr ? ' ' + value : value
		} else {
			blockContent = value
		}
		dispatch( 'core/block-editor' ).updateBlockAttributes(
			selectedBlock.clientId,
			{
				content: blockContent,
			}
		)

		changeCursorPosition( document.getElementById( 'block-' + selectedBlock.clientId ) )
	}

	typingWorker.postMessage( content )
}
