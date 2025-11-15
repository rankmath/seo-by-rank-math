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
 * Handles wrapper being a selector, jQuery object, Element, or null, and targets the
 * inner contenteditable element when available.
 *
 * @param {HTMLElement|Node|string|null} wrapper The wrapper where the content is being appended.
 */
const changeCursorPosition = ( wrapper ) => {
	// Normalize input to a real DOM Node
	const toNode = ( target ) => {
		if ( ! target ) {
			return null
		}
		// Already a DOM Node
		if ( target.nodeType ) {
			return target
		}
		// jQuery object
		if ( target.jquery ) {
			return target[ 0 ] || null
		}
		// CSS selector string
		if ( 'string' === typeof target ) {
			return document.querySelector( target )
		}
		// Array-like (e.g., NodeList or jQuery collection)
		if ( Array.isArray( target ) || target.length ) {
			return target[ 0 ] || null
		}
		return null
	}

	const node = toNode( wrapper )
	if ( ! node ) {
		return
	}

	// Prefer the actual editable area inside the block wrapper
	const editable = node.querySelector ? node.querySelector( '[contenteditable="true"]' ) || node : node

	// Ensure we have a Node to operate on
	if ( ! editable || ! editable.nodeType ) {
		return
	}

	const selection = getSelection && getSelection()
	if ( ! selection ) {
		return
	}

	const range = document.createRange()

	// Focus the editable element so the caret becomes visible
	if ( editable.focus ) {
		editable.focus()
	}

	selection.removeAllRanges()
	range.selectNodeContents( editable )
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
