/**
 * External dependencies
 */
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

export const runCommand = ( endpoint, params, clientId = null, existingContent = '' ) => {
	getData( endpoint, params, ( result ) => {
		if ( result.error ) {
			updateBlockAttributes(
				clientId,
				{
					content: result.error,
					className: 'rank-math-content-ai-command',
					isAiGenerated: false,
					hasApiError: true, // This will now work because the attribute is registered.
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
				className: 'rank-math-content-ai-command',
				isAiGenerated: true,
			}
		)

		addContent( result, clientId, null, existingContent )
	} )
}

export const useBlock = ( clientId, attributes ) => {
	const selectedBlock = select( blockEditorStore ).getBlock( clientId )
	const content = getBlockContent( selectedBlock ).replace( /<div .*<\/div>/g, '' ).replaceAll( '  ', '' )
	const parsedBlocks = rawHandler( {
		HTML: content,
		mode: 'BLOCKS',
	} )
	const newBlock = parsedBlocks.map( ( parsedBlock ) =>
		createBlock( parsedBlock.name, parsedBlock.attributes, parsedBlock.innerBlocks )
	)

	if ( attributes.replaceBlock ) {
		dispatch( blockEditorStore ).replaceBlock( attributes.selectedId, newBlock )
		removeBlock( clientId )
		return
	}

	dispatch( blockEditorStore ).replaceBlock( clientId, newBlock )
}

export const regenerateOutput = ( clientId, endpoint, params ) => {
	updateBlockAttributes(
		clientId,
		{
			content: generatingText,
			isAiGenerated: true,
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

export const writeMore = ( clientId ) => {
	const selectedBlock = select( blockEditorStore ).getBlock( clientId )
	const content = getBlockContent( selectedBlock ).replace( /<div .*<\/div>/g, '' ).replace( '<br>', '' ).replaceAll( '  ', '' )
	updateBlockAttributes(
		clientId,
		{
			content: content + '' + generatingText,
			isAiGenerated: true,
		}
	)

	runCommand( 'Continue_Writing', { sentence: trimContent( content ), choices: 1 }, clientId, content )
}

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
