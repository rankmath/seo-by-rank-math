/**
 * External dependencies
 */
import { isEmpty, isArray } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Button, Notice } from '@wordpress/components'
import { useState } from '@wordpress/element'
import { dispatch } from '@wordpress/data'
import { createBlock } from '@wordpress/blocks'

/**
 * Internal dependencies
 */
import getData from '../helpers/getData'
import getFields from '../helpers/getFields'
import getLastParagraph from '../helpers/getLastParagraph'
import getSelectedBlock from '../helpers/getSelectedBlock'
import FreePlanNotice from '../components/FreePlanNotice'
import insertElementorContent from '../helpers/insertElementorContent'
import getBlockContent from '../helpers/getBlockContent'
import insertCommandBox from '../shortcutCommand/insertCommandBox'
import addContent from '../shortcutCommand/addContent'
import ErrorCTA from '@components/ErrorCTA'
import isGutenbergAvailable from '@helpers/isGutenbergAvailable'
import Interpolate from '@components/Interpolate'

/**
 * Content AI - Write Tab
 *
 * @param {Object} props Props passed to the component.
 */
const Write = ( props ) => {
	const [ attributes, setAttributes ] = useState( {
		document_title: typeof rankMathEditor !== 'undefined' ? rankMathEditor.assessor.dataCollector.getData().title : '',
		text: '',
		instructions: '',
		tone: rankMath.contentAI.tone,
		focus_keyword: [],
		length: 'medium',
		choices: 1,
	} )
	const [ isGenerating, setGenerating ] = useState( false )

	const onChange = ( key, value ) => {
		attributes[ key ] = value
		setAttributes( { ...attributes } )

		dispatch( 'rank-math-content-ai' ).updateAIAttributes( key, value )
	}

	const fields = {
		instructions: {
			isRequired: false,
		},
		tone: {
			isRequired: false,
		},
		focus_keyword: {
			isRequired: false,
		},
		length: {
			isRequired: true,
		},
	}

	const isBlockEditor = isGutenbergAvailable() || props.data.isContentAIPage
	return (
		<>
			<div className={ props.hasError ? 'rank-math-ui module-listing blurred' : 'rank-math-ui module-listing' }>
				<div className="rank-math-focus-keyword">
					<Notice status="warning" isDismissible={ false }>
						<FreePlanNotice addNotice={ false } />
						<Interpolate
							components={ {
								link: (
									<a
										href='https://rankmath.com/kb/content-ai-editor/?utm_source=Plugin&utm_medium=Write+Tab+Notice&utm_campaign=WP#write-tab'
										target="_blank"
										rel="noopener noreferrer"
									/>
								),
							} }
						>
							{ __(
								'{{link}}Click here{{/link}} to learn how to use it.',
								'rank-math'
							) }
						</Interpolate>
					</Notice>
				</div>
				{ getFields( fields, attributes, 'Write', onChange ) }
				<Button
					className="write-button is-primary"
					onClick={ () => {
						attributes.text = getLastParagraph()
						setAttributes( attributes )
						if ( ! isBlockEditor ) {
							setGenerating( true )
							getData( 'Write', attributes, ( result ) => {
								const content = isArray( result ) ? result[ 0 ] : result
								if ( 'elementor' === rankMath.currentEditor ) {
									insertElementorContent( content )
								} else {
									addContent( content )
								}

								setGenerating( false )
							} )

							return
						}

						let selectedBlock = getSelectedBlock()
						if ( isEmpty( selectedBlock ) || isEmpty( selectedBlock.block ) || ! getBlockContent( selectedBlock.block, false ) ) {
							const newBlock = createBlock( 'rank-math/command', {
								content: '',
							} )
							dispatch( 'core/block-editor' ).insertBlocks( newBlock, ! isEmpty( selectedBlock ) ? selectedBlock.position : 1 )

							selectedBlock = newBlock
						} else {
							const newBlock = createBlock( 'rank-math/command', {
								content: '',
								className: 'rank-math-content-ai-command',
							} )

							dispatch( 'core/block-editor' ).replaceBlock( selectedBlock.clientId, newBlock )
							selectedBlock = newBlock
						}
						insertCommandBox( 'Write', attributes, selectedBlock.clientId )
					} }
				>
					{
						<>
							{ isGenerating ? __( 'Generating…', 'rank-math' ) : __( 'Generate', 'rank-math' ) }
							{ isBlockEditor && <span>CTRL + /</span> }
						</>
					}
				</Button>
				<p style={ { marginTop: '10px', opacity: '0.7' } }><em>{ __( '1 Word Output = 1 Credit', 'rank-math' ) }</em></p>
			</div>
			{ props.hasError && ! props.isContentAIPage && <ErrorCTA /> }
		</>
	)
}

export default Write
