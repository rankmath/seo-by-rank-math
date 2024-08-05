/**
 * External dependencies
 */
import jQuery from 'jquery'
import { isNull, isUndefined, isEmpty, includes } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { addFilter } from '@wordpress/hooks'
import { render } from '@wordpress/element'
import { registerFormatType } from '@wordpress/rich-text'
import { Popover, Button } from '@wordpress/components'
import { select } from '@wordpress/data'

/**
 * Internal dependencies
 */
import getTools from '../helpers/getTools'
import Modal from '../modal'
import contentAiToolbar from './contentAiToolbar'
import hasError from '../helpers/hasError'
import callApi from './callApi'
import getBlockContent from '../helpers/getBlockContent'

/**
 * Autocompleter function to register the shortcut & get the response from the API.
 *
 * @param {string} prefix Auto-completer prefix.
 */
const getContentAICompleters = ( prefix ) => {
	return {
		name: 'content-ai-tools',
		className: 'content-ai-autocompleters',
		triggerPrefix: prefix,
		isDebounced: true,
		allowContext: ( before, after ) => ( ! ( /\S/.test( before ) || /\S/.test( after ) ) ),
		options: () => ( getTools() ),
		getOptionKeywords( { endpoint, title, searchTerms } ) {
			const expansionWords = title.split( /\s+/ )
			expansionWords.push( expansionWords.join( ' ' ) )

			return ! isUndefined( searchTerms ) ? searchTerms : [ endpoint, ...expansionWords ]
		},
		getOptionLabel: ( tool ) => {
			return (
				<span>
					<i className={ 'ai-icon ' + tool.icon }></i> { tool.title }
				</span>
			)
		},
		getOptionCompletion: ( completer ) => {
			if ( ! completer.endpoint ) {
				return false
			}

			if ( isNull( document.getElementById( 'rank-math-content-ai-modal-wrapper' ) ) ) {
				jQuery( '#wpwrap' ).append( '<div id="rank-math-content-ai-modal-wrapper"></div>' )
			}

			wp.data.dispatch( 'rank-math-content-ai' ).isAutoCompleterOpen( true )

			setTimeout( () => {
				render(
					<Modal tool={ completer } />,
					document.getElementById( 'rank-math-content-ai-modal-wrapper' )
				)
			}, 100 )
		},
	}
}

// Add Shorten with AI button to the Highlighted Paragraphs.
const HighlightPopover = () => {
	const highlightedParagraphs = ! isUndefined( select( 'rank-math' ) ) ? select( 'rank-math' ).getHighlightedParagraphs() : []
	if ( isEmpty( highlightedParagraphs ) ) {
		return ''
	}

	const selectedBlock = select( 'core/block-editor' ).getSelectedBlock()
	if ( isEmpty( selectedBlock ) || ! includes( highlightedParagraphs, selectedBlock.clientId ) ) {
		jQuery( '.block-editor-block-popover' ).show()
		return ''
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
				onClick={ () => {
					const text = getBlockContent( selectedBlock )
					callApi( 'Text_Summarizer', { text, language: wp.data.select( 'rank-math-content-ai' ).getData().language, format: 'paragraph', choices: 1 }, selectedBlock, true )
				} }

			>
				{ __( 'Shorten with AI', 'rank-math' ) }
			</Button>
		</Popover>
	)
}

/**
 * Register Content AI Autocompleters to show AI tools on // & // .
 */
export default () => {
	registerFormatType( 'rank-math/content-ai', {
		title: __( 'Content AI', 'rank-math' ),
		tagName: 'p',
		className: null,
		edit: HighlightPopover,
	} )

	addFilter( 'editor.BlockEdit', 'rank-math', contentAiToolbar )

	if ( hasError() ) {
		return
	}

	addFilter(
		'editor.Autocomplete.completers',
		'rank-math/content-ai/tools',
		( completers, blockName ) => {
			return blockName === 'core/paragraph' || blockName === 'rank-math/command'
				? [ ...completers, getContentAICompleters( '//' ) ]
				: completers
		}
	)

	addFilter(
		'editor.Autocomplete.completers',
		'rank-math/content-ai/tools2',
		( completers, blockName ) => {
			return blockName === 'core/paragraph' || blockName === 'rank-math/command'
				? [ ...completers, getContentAICompleters( '// ' ) ]
				: completers
		}
	)
}
