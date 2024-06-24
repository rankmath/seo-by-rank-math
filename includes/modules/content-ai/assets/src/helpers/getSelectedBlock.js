/**
 * External dependencies
 */
import { isEmpty } from 'lodash'

/**
 * Wordpress dependencies
 */
import { select } from '@wordpress/data'

export default () => {
	const selectedBlock = select( 'core/block-editor' ).getSelectedBlock()
	const blocks = select( 'core/block-editor' ).getBlocks()
	if ( ! isEmpty( selectedBlock ) ) {
		const position = blocks.map(
			function( block ) {
				return block.clientId === selectedBlock.clientId
			} ).indexOf( true ) + 1

		return {
			block: selectedBlock,
			position,
			clientId: selectedBlock.clientId,
		}
	}

	if ( isEmpty( blocks ) ) {
		return {
			block: [],
			position: 0,
		}
	}

	const block = blocks[ blocks.length - 1 ]
	return {
		block,
		position: blocks.length,
		clientId: block.clientId,
	}
}
