/**
 * External dependencies
 */
import jQuery from 'jquery'
import { isNull, find, startCase, isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Modal } from '@wordpress/components'
import { createRoot } from '@wordpress/element'
import { select, dispatch } from '@wordpress/data'
import { createBlock } from '@wordpress/blocks'

/**
 * Internal dependencies
 */
import insertCommandBox from '../shortcutCommand/insertCommandBox'
import hasError from '../helpers/hasError'
import getTools from '../helpers/getTools'
import ErrorMessage from '../components/ErrorMessage'

const generatingText = __( 'Generating…', 'rank-math' )

// Function to run when Toolbar option is clicked.
export default ( endpoint, data, selectedBlock, replaceBlock = false ) => {
	if ( hasError() ) {
		let tool = find( getTools(), [ 'endpoint', endpoint ] )
		if ( isUndefined( tool ) ) {
			tool = find( getTools(), [ 'endpoint', 'Blog_Post_Idea' ] )
			tool.title = startCase( endpoint )
		}

		if ( isNull( document.getElementById( 'rank-math-content-ai-modal-wrapper' ) ) ) {
			jQuery( '#wpwrap' ).append( '<div id="rank-math-content-ai-modal-wrapper"></div>' )
		}

		setTimeout( () => {
			createRoot( document.getElementById( 'rank-math-content-ai-modal-wrapper' ) )
				.render(
					<Modal
						className="rank-math-contentai-modal rank-math-modal rank-math-error-modal"
						onRequestClose={ () => {
							jQuery( '.components-modal__screen-overlay' ).remove()
							document.getElementById( 'rank-math-content-ai-modal-wrapper' ).remove()
						} }
						shouldCloseOnClickOutside={ true }
					>
						<ErrorMessage width={ 100 } />
					</Modal>
				)
		}, 100 )

		return
	}

	const newBlock = createBlock( 'rank-math/command', {
		content: generatingText,
		className: 'rank-math-content-ai-command',
	} )

	const position = select( 'core/block-editor' ).getBlocks().map(
		( block ) => {
			return block.clientId === selectedBlock.clientId
		}
	).indexOf( true )
	dispatch( 'core/block-editor' ).insertBlocks( newBlock, position + 1 )

	insertCommandBox( endpoint, data, newBlock.clientId, selectedBlock.clientId, replaceBlock )
}
