/**
 * External dependencies
 */
import { includes, isEmpty, isNull } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { ToolbarDropdownMenu, ToolbarGroup } from '@wordpress/components'
import { BlockControls } from '@wordpress/block-editor'
import { createHigherOrderComponent } from '@wordpress/compose'

/**
 * Internal dependencies
 */
import getLastParagraph from '../helpers/getLastParagraph'
import callApi from './callApi'

/**
 * Content AI Toolbar component.
 *
 * @param {Object} props       Component props.
 * @param {string} props.value Selected Content.
 */
export default createHigherOrderComponent( ( BlockEdit ) => {
	return ( selectedBlock ) => {
		if ( selectedBlock && ( ! includes( [ 'core/paragraph', 'core/heading' ], selectedBlock.name ) ) ) {
			return <BlockEdit { ...selectedBlock } />
		}

		const text = ! isEmpty( selectedBlock.attributes.content.text ) ? selectedBlock.attributes.content.text : selectedBlock.attributes.content
		if ( isEmpty( text ) ) {
			return <BlockEdit { ...selectedBlock } />
		}

		const language = rankMath.ca_language
		const controls = [
			{
				title: '💻  ' + __( 'Run as Command', 'rank-math' ),
				onClick: () => ( callApi( 'AI_Command', { command: text, language, choices: 1 }, selectedBlock ) ),
			},
			{
				title: '📖  ' + __( 'Write More', 'rank-math' ),
				onClick: () => ( callApi( 'Continue_Writing', { sentence: getLastParagraph(), choices: 1 }, selectedBlock ) ),
			},
			{
				title: '📝  ' + __( 'Improve', 'rank-math' ),
				onClick: () => ( callApi( 'Paragraph_Rewriter', { original_paragraph: text, language, choices: 1 }, selectedBlock ) ),
			},
		]

		if ( ! isNull( selectedBlock ) && selectedBlock.name === 'core/paragraph' ) {
			controls.push(
				{
					title: '📙  ' + __( 'Summarize', 'rank-math' ),
					onClick: () => ( callApi( 'Text_Summarizer', { text, language, choices: 1 }, selectedBlock ) ),
				},
				{
					title: '💭  ' + __( 'Write Analogy', 'rank-math' ),
					onClick: () => ( callApi( 'Analogy', { text, language, choices: 1 }, selectedBlock ) ),
				},
			)
		}

		controls.push(
			{
				title: '✨  ' + __( 'Fix Grammar', 'rank-math' ),
				onClick: () => ( callApi( 'Fix_Grammar', { text, choices: 1 }, selectedBlock ) ),
			},
		)

		return (
			<>
				<BlockEdit { ...selectedBlock } />
				<BlockControls>
					<ToolbarGroup>
						<ToolbarDropdownMenu
							icon="rm-icon rm-icon-content-ai"
							label={ __( 'Content AI Commands', 'rank-math' ) }
							controls={ controls }
						/>
					</ToolbarGroup>
				</BlockControls>
			</>
		)
	}
} )
