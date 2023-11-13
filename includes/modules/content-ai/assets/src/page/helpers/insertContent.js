/**
 * Externam dependencies
 */
import jQuery from 'jquery'
import { map, isNull } from 'lodash'

/**
 * WordPress dependencies
 */
import { select, dispatch } from '@wordpress/data'
import { rawHandler, createBlock } from '@wordpress/blocks'

/**
 * Internal dependencies
 */
import generateId from '@helpers/generateId'
import insertElementorContent from './insertElementorContent'
import markdownConverter from '../helpers/markdownConverter'

export default ( content, endpoint = '' ) => {
	content = markdownConverter( content )
	if ( 'elementor' === rankMath.currentEditor ) {
		insertElementorContent( content, false )
		jQuery( '.rank-math-contentai-modal-overlay .components-modal__header button' ).trigger( 'click' )
		return
	}

	if ( 'classic' === rankMath.currentEditor ) {
		tinymce.activeEditor.insertContent( ' ' + content )
		jQuery( '.rank-math-contentai-modal-overlay .components-modal__header button' ).trigger( 'click' )
		return
	}

	const selectedBlock = select( 'core/block-editor' ).getSelectedBlock()
	const blocks = select( 'core/block-editor' ).getBlocks()
	let position = 0
	if ( ! isNull( selectedBlock ) ) {
		position = blocks.map(
			function( block ) {
				return block.clientId === selectedBlock.clientId
			} ).indexOf( true )

		position = selectedBlock.attributes.content ? position + 1 : position
	} else {
		position = blocks.length
	}

	let newBlock = ''
	if ( 'Frequently_Asked_Questions' === endpoint ) {
		newBlock = createBlock( 'rank-math/faq-block', {
			questions: map( content, ( value ) => {
				return {
					id: generateId( 'faq-question' ),
					title: value.question,
					content: value.answer.replaceAll( /(?:\r\n|\r|\n)/g, '<br>' ).trim(),
					visible: true,
				}
			} ),
		} )
	} else {
		const parsedBlocks = rawHandler( {
			HTML: content,
			mode: 'BLOCKS',
		} )
		newBlock = parsedBlocks.map( ( parsedBlock ) =>
			createBlock( parsedBlock.name, parsedBlock.attributes, parsedBlock.innerBlocks )
		)
	}

	dispatch( 'core/block-editor' ).insertBlocks( newBlock, position )
	jQuery( '.rank-math-contentai-modal-overlay .components-modal__header button' ).trigger( 'click' )
}
