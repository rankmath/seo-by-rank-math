/**
 * External dependencies
 */
import { includes, isEmpty, isNull } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { ToolbarDropdownMenu, ToolbarGroup } from '@wordpress/components'
import { select, dispatch, useSelect } from '@wordpress/data'
import { createBlock } from '@wordpress/blocks'
import { BlockControls } from '@wordpress/block-editor'

/**
 * Internal dependencies
 */
import getLastParagraph from '../helpers/getLastParagraph'
import insertCommandBox from '../shortcutCommand/insertCommandBox'

const generatingText = __( 'Generating…', 'rank-math' )

// Function to run when Toolbar option is clicked.
const onClick = ( endpoint, data, selectedBlock ) => {
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

	insertCommandBox( endpoint, data, newBlock.clientId )
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
		<BlockControls>
			<ToolbarGroup>
				<ToolbarDropdownMenu
					icon="rm-icon rm-icon-content-ai"
					label={ __( 'Content AI Commands', 'rank-math' ) }
					controls={ controls }
				/>
			</ToolbarGroup>
		</BlockControls>
	)
}
