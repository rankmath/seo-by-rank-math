/* global Node */

/**
 * External dependencies
 */
import { forEach, includes, toLower, isEmpty, filter } from 'lodash'

/**
 * Internal dependencies
 */
import isTinymceActive from '@helpers/isTinymceActive'
import { highlightContent } from './fixAnalysisTest'

/**
 * Set the content & re-run the content tests.
 *
 * @param {string} content Content to add to the editor.
 */
export const setContent = ( content ) => {
	const editor = tinymce.activeEditor
	editor.setContent( content )
	rankMathEditor.assessor.dataCollector.handleContentChange()
}

/**
 * Get the content from the TinyMCE editor.
 *
 * @return {string} Editor content.
 */
export const getContent = () => {
	return rankMathEditor.assessor.dataCollector.getContent()
}

export const removeTinyMceHighlighting = () => {
	const content = getContent().replaceAll( /<mark\b[^>]*class=["']rank-math-highlight["'][^>]*>(.*?)<\/mark>/gi, '$1' )
	setContent( content )
}

/**
 * Check if it's an inline tag.
 *
 * @param {string} tagName Tag name.
 */
const isInlineTag = ( tagName ) => {
	return includes(
		[
			'a', 'abbr', 'acronym', 'b', 'bdo', 'big', 'br', 'cite', 'code',
			'dfn', 'em', 'i', 'img', 'input', 'kbd', 'label', 'q', 's', 'samp',
			'small', 'span', 'strong', 'sub', 'sup', 'textarea', 'time', 'tt', 'u', 'var'
		],
		toLower( tagName )
	)
}

/**
 * Adds a unique `data-rm-block-id` attribute to all top-level HTML elements
 * within the TinyMCE editor content and returns an object mapping each
 * block ID to its corresponding HTML content.
 *
 * This function is useful for identifying, manipulating, or tracking
 * individual content blocks inside the TinyMCE editor.
 *
 * @return {Object} An object where the keys are `data-rm-block-id` values
 *                   and the values are the HTML content of the corresponding blocks.
 *
 * @example
 * {
 *   "block-1": "<p data-rm-block-id=\"block-1\">This is a paragraph.</p>",
 *   "block-2": "<h2 data-rm-block-id=\"block-2\">This is a heading.</h2>",
 *   ...
 * }
 */
export const getTinyMceBlocks = () => {
	if ( ! isTinymceActive() ) {
		return {}
	}

	const blocks = {}
	const editor = tinymce.activeEditor

	const content = editor.getContent()
	const div = document.createElement( 'div' )
	div.innerHTML = content

	let counter = 0

	const processNode = ( node ) => {
		// Only process element nodes
		if ( node.nodeType !== Node.ELEMENT_NODE ) {
			return
		}

		if ( isInlineTag( node.tagName ) ) {
			return
		}

		const childElements = filter( node.children, ( child ) => child.nodeType === Node.ELEMENT_NODE && ! isInlineTag( child.tagName ) )

		// Recurse on children
		if ( ! isEmpty( childElements ) ) {
			forEach( childElements, processNode )
			return
		}

		// If no element children (i.e., it's a leaf element), add the attribute
		counter++
		const blockID = `block-${ counter }`
		node.setAttribute( 'data-rm-block-id', blockID )
		blocks[ blockID ] = node.outerHTML
	}

	// Start from wrapper's children
	forEach( div.children, processNode )

	editor.setContent( div.innerHTML )
	return blocks
}

/**
 * Replaces the specified content with new AI-generated content
 * and highlights the updated content for easy identification.
 *
 * @param {string}          apiResponse The AI-generated content to replace the old content with.
 * @param {string|string[]} blocks      The old content or specific content blocks to be replaced.
 *
 * @return {void}
 */
export const replaceClassicContent = ( apiResponse, blocks ) => {
	const editor = tinymce.activeEditor
	const tinyMceContent = editor.getContent()
	const div = document.createElement( 'div' )
	div.innerHTML = tinyMceContent

	forEach( apiResponse, ( response ) => {
		let { action, refBlockId, content, position } = response

		const target = div.querySelector( `[data-rm-block-id="${ refBlockId }"]` )
		if ( ! target ) {
			console.warn( `Block with ID ${ refBlockId } not found.` )
			return
		}

		const block = blocks[ refBlockId ]
		const temp = document.createElement( 'div' )
		switch ( action ) {
			case 'replace':
				// Replace block content
				content = highlightContent( block, content )
				temp.innerHTML = content
				const newNode = temp.firstElementChild
				if ( newNode ) {
					target.replaceWith( newNode )
				}
				break

			case 'delete':
				// Delete block
				target.remove()
				break

			case 'insert':
				// Insert new block at specified position.
				temp.innerHTML = highlightContent( '', content )
				const nodeToInsert = temp.firstElementChild

				if ( ! nodeToInsert ) {
					console.warn( 'Invalid HTML to insert.' )
					return
				}

				if ( position === 'before' ) {
					target.parentNode.insertBefore( nodeToInsert, target )
				} else {
					target.parentNode.insertBefore( nodeToInsert, target.nextSibling )
				}

				break

			default:
				console.warn( `Unknown action: ${ action }` )
				break
		}

		setContent( div.innerHTML )
	} )
}

/**
 * Removes all `data-rm-block-id` attributes from the given HTML content.
 *
 * This function is typically used to clean up the content ensuring that internal block identifiers are not persisted.
 */
export const removeDataBlockID = () => {
	if ( rankMath.currentEditor !== 'classic' ) {
		return
	}

	const editor = tinymce.activeEditor
	const content = editor.getContent()
	const container = document.createElement( 'div' )
	container.innerHTML = content
	// Remove all `data-rm-block-id` attributes from any element
	container.querySelectorAll( '[data-rm-block-id]' ).forEach( ( el ) => {
		el.removeAttribute( 'data-rm-block-id' )
	} )
	setContent( container.innerHTML )
}
