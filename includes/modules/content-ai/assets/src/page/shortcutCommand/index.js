/**
 * External dependencies
 */
import jQuery from 'jquery'
import { includes, startsWith, isNull, isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { store as blockEditorStore } from '@wordpress/block-editor'
import { select, dispatch } from '@wordpress/data'
import { createBlock } from '@wordpress/blocks'

/**
 * Internal dependencies
 */
import insertCommandBox from './insertCommandBox'
import hasError from '../helpers/hasError'
import getWriteAttributes from '../helpers/getWriteAttributes'

/**
 * Keyboard Shortcut command. ( // Command + Enter)
 */
export default () => {
	const { updateBlockAttributes } = dispatch( blockEditorStore )

	const runCommand = () => {
		const selectedBlock = select( 'core/block-editor' ).getSelectedBlock()
		if ( isNull( selectedBlock ) ) {
			return
		}

		insertCommandBox( 'Write', getWriteAttributes( selectedBlock.attributes.content ), selectedBlock.clientId )
	}

	// Change caret position when ArrowLeft, Right is pressed.
	const changeCaretPosition = ( start = false ) => {
		const richTextContainer = document.activeElement
		const textElement = richTextContainer.childNodes[ 1 ]
		if ( isUndefined( textElement ) ) {
			return
		}

		const sel = window.getSelection()
		const range = document.createRange()

		range.setStart( textElement, start ? 0 : textElement.length )
		range.collapse( true )
		sel.removeAllRanges()
		sel.addRange( range )
	}

	jQuery( document ).on( 'click', '.rank-math-content-ai-command-button', () => {
		runCommand()
	} )

	// Code to move cursor at the end of the text when left arrow is pressed.
	jQuery( document ).on( 'keydown', '.rank-math-content-ai-command-button', ( event ) => {
		if ( event.code === 'Enter' ) {
			runCommand()
			return
		}

		if ( event.code !== 'ArrowLeft' ) {
			return
		}

		event.preventDefault()
		changeCaretPosition()
	} )

	let isSelectedAll = false
	const contentAIButton = '<button class="rank-math-content-ai-command-button" title="' + __( 'Click or Press Enter', 'rank-math' ) + '" contenteditable="false"><i class="rm-icon rm-icon-enter-key"></i></button>'
	document.addEventListener( 'keyup', ( event ) => {
		const selectedBlock = select( 'core/block-editor' ).getSelectedBlock()
		if ( isNull( selectedBlock ) ) {
			return
		}

		let selectionID = selectedBlock.clientId
		let content = selectedBlock.attributes.content

		// Convert Block to Content AI Command when content starts with //
		if ( startsWith( content, '//' ) && selectedBlock.name === 'core/paragraph' ) {
			const newBlock = createBlock( 'rank-math/command', {
				content: selectedBlock.attributes.content.replace( '//', '<span>//</span>' ),
				className: 'rank-math-content-ai-command',
			} )

			dispatch( 'core/block-editor' ).replaceBlock( selectionID, newBlock )

			selectionID = newBlock.clientId
		}

		if ( 'rank-math/command' !== selectedBlock.name ) {
			return false
		}

		if ( isSelectedAll && includes( [ 'ArrowLeft', 'ArrowRight' ], event.code ) ) {
			changeCaretPosition( 'ArrowLeft' === event.code )
			isSelectedAll = false
			return
		}

		if ( event.code !== 'Backspace' ) {
			isSelectedAll = event.code === 'KeyA'
		}

		// Replace Content AI Command block with paragraph after removing //
		if ( event.code === 'Backspace' && ( isSelectedAll || ! content.replace( /(<div[^>]*>.*<\/div>)/s, '' ).replace( /(<span[^>]*>.*<\/span>)/s, '' ) || '/' === content.replace( /(<([^>]+)>)/ig, '' ) ) ) {
			const newBlock = createBlock( 'core/paragraph' )
			dispatch( 'core/block-editor' ).replaceBlock( selectionID, newBlock )
			return
		}

		const classNames = ! isUndefined( selectedBlock.attributes.className ) ? selectedBlock.attributes.className.split( ' ' ) : []
		const isContentEmpty = '' === content.replace( '//', '' ).replace( ' ', '' ).replace( new RegExp( contentAIButton, 'i' ), '' ).replace( /(<([^>]+)>)/ig, '' )

		// Add or Remove Enter Button based on the Content.
		if ( isContentEmpty ) {
			updateBlockAttributes(
				selectionID,
				{
					content: content.replace( new RegExp( contentAIButton, 'i' ), '' ),
				}
			)
		} else if ( ! includes( classNames, 'typing' ) && ! content.includes( 'rank-math-content-ai-command-button' ) ) {
			content += contentAIButton
			updateBlockAttributes(
				selectionID,
				{
					content,
					className: 'rank-math-content-ai-command',
				}
			)
		}
	} )

	document.addEventListener( 'keydown', ( event ) => {
		if ( event.code !== 'Enter' || event.shiftKey || event.metaKey || 'button' === event.target.localName || hasError() ) {
			return
		}

		const selectedBlock = select( 'core/block-editor' ).getSelectedBlock()
		if ( isNull( selectedBlock ) || selectedBlock.name !== 'rank-math/command' ) {
			return
		}

		const text = selectedBlock.attributes.content
		if ( ! text.replace( '//', '' ).replace( ' ', '' ).replace( /(<([^>]+)>)/ig, '' ) ) {
			return
		}

		const selectionID = selectedBlock.clientId
		updateBlockAttributes(
			selectionID,
			{
				content: '',
				className: '',
			}
		)

		if ( ! select( 'rank-math-content-ai' ).isAutoCompleterOpen() ) {
			insertCommandBox( 'Write', getWriteAttributes( text ), selectionID )
		}
	} )
}
