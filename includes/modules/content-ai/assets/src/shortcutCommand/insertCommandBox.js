/**
 * External dependencies
 */
import jQuery from 'jquery'
import { isArray, isNull, isEmpty, forEach } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { store as blockEditorStore } from '@wordpress/block-editor'
import { select, dispatch } from '@wordpress/data'
import { rawHandler, createBlock } from '@wordpress/blocks'

/**
 * Internal dependencies
 */
import getData from '../helpers/getData'
import getLastParagraph from '../helpers/getLastParagraph'
import getBlockContent from '../helpers/getBlockContent'
import addContent from './addContent'

const generatingText = __( 'Generating…', 'rank-math' )

const { updateBlockAttributes, removeBlock } = dispatch( blockEditorStore )

const useButton = '<button class="button button-small rank-math-content-ai-use" tabindex="0"><span contenteditable="false">' + __( 'Use', 'rank-math' ) + '</span></button>'
const regenerateButton = '<button class="button button-small rank-math-content-ai-regenerate" tabindex="0"><span contenteditable="false">' + __( 'Regenerate', 'rank-math' ) + '</span></button>'
const writeMoreButton = '<button class="button button-small rank-math-content-ai-write-more" tabindex="0"><span contenteditable="false">' + __( 'Write More', 'rank-math' ) + '</span></button>'
const buttons = '<div class="rank-math-content-ai-command-buttons">' + useButton + regenerateButton + writeMoreButton + '</div>'

const runCommand = ( endpoint, params, clientId = null, existingContent = '' ) => {
	getData( endpoint, params, ( result ) => {
		if ( result.error ) {
			const dismissButton = '<div class="rank-math-content-ai-command-buttons"><button class="button button-small rank-math-content-ai-dismiss" contenteditable="false" contenteditable="true">' + __( 'Dismiss', 'rank-math' ) + '</button></div>'
			updateBlockAttributes(
				clientId,
				{
					content: result.error + dismissButton,
					className: 'rank-math-content-ai-command',
					isAiGenerated: true,
				}
			)

			return
		}

		result = isArray( result ) ? result[ 0 ] : result
		if ( isNull( clientId ) ) {
			addContent( result )
			return
		}

		updateBlockAttributes(
			clientId,
			{
				content: '',
				className: 'rank-math-content-ai-command typing',
				isAiGenerated: true,
			}
		)

		addContent( result, clientId, buttons, existingContent )
	} )
}

const useBlock = () => {
	const selectedBlock = select( 'core/block-editor' ).getSelectedBlock()
	const content = getBlockContent( selectedBlock ).replace( /<div .*<\/div>/g, '' ).replaceAll( '  ', '' )
	const parsedBlocks = rawHandler( {
		HTML: content,
		mode: 'BLOCKS',
	} )
	const newBlock = parsedBlocks.map( ( parsedBlock ) =>
		createBlock( parsedBlock.name, parsedBlock.attributes, parsedBlock.innerBlocks )
	)

	if ( selectedBlock.attributes.replaceBlock ) {
		dispatch( 'core/block-editor' ).replaceBlock( selectedBlock.attributes.selectedId, newBlock )
		removeBlock( selectedBlock.clientId )
		return
	}

	dispatch( 'core/block-editor' ).replaceBlock( selectedBlock.clientId, newBlock )
}

const regenerateOutput = () => {
	const selectedBlock = select( 'core/block-editor' ).getSelectedBlock()
	const clientId = selectedBlock.clientId
	const endpoint = selectedBlock.attributes.endpoint
	const params = selectedBlock.attributes.params

	updateBlockAttributes(
		clientId,
		{
			content: generatingText,
		}
	)

	runCommand( endpoint, params, clientId )
}

const trimContent = ( content ) => {
	if ( content.length === 800 ) {
		return content
	}

	if ( content.length > 800 ) {
		let length = 0
		const paragraphs = []
		const sentences = content.match( /[^\.!\?]+[\.!\?]+/g )
		if ( ! isEmpty( sentences ) ) {
			forEach( sentences.reverse(), ( sentence ) => {
				if ( length < 800 ) {
					paragraphs.push( sentence )
					length = length + sentence.length
				}
			} )
		}

		return paragraphs.reverse().join( ' ' )
	}

	return getLastParagraph( content.length ) + '\n' + content
}

const writeMore = () => {
	const selectedBlock = select( 'core/block-editor' ).getSelectedBlock()
	const clientId = selectedBlock.clientId
	const content = getBlockContent( selectedBlock ).replace( /<div .*<\/div>/g, '' ).replace( '<br>', '' ).replaceAll( '  ', '' )
	updateBlockAttributes(
		clientId,
		{
			content: content + '' + generatingText,
		}
	)

	runCommand( 'Continue_Writing', { sentence: trimContent( content ), choices: 1 }, clientId, content )
}

const commandActions = () => {
	jQuery( document ).on( 'click', '.rank-math-content-ai-dismiss', () => {
		const selectedBlock = select( 'core/block-editor' ).getSelectedBlock()
		removeBlock( selectedBlock.clientId )
	} )

	jQuery( document ).on( 'keydown', '.rank-math-content-ai-use', ( event ) => {
		if ( event.code === 'Enter' ) {
			useBlock()
		}
	} )

	jQuery( document ).on( 'click', '.rank-math-content-ai-use', () => {
		useBlock()
	} )

	jQuery( document ).on( 'keydown', '.rank-math-content-ai-regenerate', ( event ) => {
		if ( event.code === 'Enter' ) {
			regenerateOutput()
		}
	} )

	jQuery( document ).on( 'click', '.rank-math-content-ai-regenerate', () => {
		regenerateOutput()
	} )

	jQuery( document ).on( 'keydown', '.rank-math-content-ai-write-more', ( event ) => {
		if ( event.code === 'Enter' ) {
			writeMore()
		}
	} )

	jQuery( document ).on( 'click', '.rank-math-content-ai-write-more', () => {
		writeMore()
	} )
}

commandActions()

/**
 * Function to insert the API output in the content.
 *
 * @param {string}  endpoint     Selected Text.
 * @param {Object}  params       Selected Text.
 * @param {string}  clientId     Block ID.
 * @param {string}  selectedId   Selected Block ID.
 * @param {boolean} replaceBlock Whether to replace the existing block.
 */
export default ( endpoint, params, clientId = null, selectedId, replaceBlock = false ) => {
	if ( isNull( clientId ) ) {
		runCommand( endpoint, params, clientId )
		return
	}

	updateBlockAttributes(
		clientId,
		{
			content: __( 'Generating…', 'rank-math' ),
			className: 'typing rank-math-content-ai-command',
			endpoint,
			params,
			replaceBlock,
			selectedId,
		}
	)

	runCommand( endpoint, params, clientId )
}
