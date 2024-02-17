/**
 * External dependencies
 */
import jQuery from 'jquery'
import { includes, isEmpty, isNull, find, startCase, isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { ToolbarDropdownMenu, ToolbarGroup, Popover, Button, Modal } from '@wordpress/components'
import { Fragment, render } from '@wordpress/element'
import { select, dispatch, useSelect } from '@wordpress/data'
import { createBlock } from '@wordpress/blocks'
import { BlockControls } from '@wordpress/block-editor'
import ErrorMessage from '../components/ErrorMessage'

/**
 * Internal dependencies
 */
import getLastParagraph from '../helpers/getLastParagraph'
import insertCommandBox from '../shortcutCommand/insertCommandBox'
import hasError from '../helpers/hasError'
import getTools from '../helpers/getTools'

const generatingText = __( 'Generating…', 'rank-math' )

// Function to run when Toolbar option is clicked.
const onClick = ( endpoint, data, selectedBlock, replaceBlock = false ) => {
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
			render(
				<Modal
					className="rank-math-contentai-modal rank-math-modal rank-math-error-modal"
					onRequestClose={ () => {
						jQuery( '.components-modal__screen-overlay' ).remove()
						document.getElementById( 'rank-math-content-ai-modal-wrapper' ).remove()
					} }
					shouldCloseOnClickOutside={ true }
				>
					<ErrorMessage width={ 100 } />
				</Modal>,
				document.getElementById( 'rank-math-content-ai-modal-wrapper' )
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

const HighlightPopover = () => {
	const highlightedParagraphs = ! isUndefined( select( 'rank-math' ) ) ? select( 'rank-math' ).getHighlightedParagraphs() : []
	if ( isEmpty( highlightedParagraphs ) ) {
		return
	}

	const selectedBlock = select( 'core/block-editor' ).getSelectedBlock()
	if ( isEmpty( selectedBlock ) || ! includes( highlightedParagraphs, selectedBlock.clientId ) ) {
		jQuery( '.block-editor-block-popover' ).show()
		return
	}

	jQuery( '.block-editor-block-popover' ).hide()

	return (
		<Popover
			placement="top-start"
			focusOnMount="firstElement"
			key="rank-math-popover"
			expandOnMobile={ true }
			noArrow={ false }
			anchor={ document.getElementById( 'block-' + selectedBlock.clientId ) }
		>
			<Button
				variant="primary"
				onClick={ () => ( onClick( 'Text_Summarizer', { text: selectedBlock.attributes.content, language: rankMath.ca_language, format: 'paragraph', choices: 1 }, selectedBlock, true ) ) }
			>
				{ __( 'Shorten with AI', 'rank-math' ) }
			</Button>
		</Popover>
	)
}

/**
 * Content AI Toolbar component.
 *
 * @param {Object} props       Component props.
 * @param {string} props.value Selected Content.
 */
export default ( { value } ) => {
	const selectedBlock = useSelect( () => {
		return select( 'core/block-editor' ).getSelectedBlock()
	}, [] )

	if ( selectedBlock && ( ! includes( [ 'core/paragraph', 'core/heading' ], selectedBlock.name ) || isEmpty( selectedBlock.attributes.content ) ) ) {
		return null
	}

	const language = rankMath.ca_language
	const controls = [
		{
			title: '💻  ' + __( 'Run as Command', 'rank-math' ),
			onClick: () => ( onClick( 'AI_Command', { command: text, language, choices: 1 }, selectedBlock ) ),
		},
		{
			title: '📖  ' + __( 'Write More', 'rank-math' ),
			onClick: () => ( onClick( 'Continue_Writing', { sentence: getLastParagraph(), choices: 1 }, selectedBlock ) ),
		},
		{
			title: '📝  ' + __( 'Improve', 'rank-math' ),
			onClick: () => ( onClick( 'Paragraph_Rewriter', { original_paragraph: text, language, choices: 1 }, selectedBlock ) ),
		},
	]

	if ( ! isNull( selectedBlock ) && selectedBlock.name === 'core/paragraph' ) {
		controls.push(
			{
				title: '📙  ' + __( 'Summarize', 'rank-math' ),
				onClick: () => ( onClick( 'Text_Summarizer', { text, language, choices: 1 }, selectedBlock ) ),
			},
			{
				title: '💭  ' + __( 'Write Analogy', 'rank-math' ),
				onClick: () => ( onClick( 'Analogy', { text, language, choices: 1 }, selectedBlock ) ),
			},
		)
	}

	controls.push(
		{
			title: '✨  ' + __( 'Fix Grammar', 'rank-math' ),
			onClick: () => ( onClick( 'Fix_Grammar', { text, choices: 1 }, selectedBlock ) ),
		},
	)

	const text = ! isEmpty( value.text ) ? value.text.split( ' ' ).splice( 0, 149 ).join( ' ' ) : ''
	return (
		<Fragment>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarDropdownMenu
						icon="rm-icon rm-icon-content-ai"
						label={ __( 'Content AI Commands', 'rank-math' ) }
						controls={ controls }
					/>
				</ToolbarGroup>
			</BlockControls>
			<HighlightPopover />
		</Fragment>
	)
}
