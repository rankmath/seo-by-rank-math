/**
 * External dependencies
 */
import jQuery from 'jquery'
import { isNull, isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { addFilter } from '@wordpress/hooks'
import { render } from '@wordpress/element'
import { registerFormatType } from '@wordpress/rich-text'

/**
 * Internal dependencies
 */
import getTools from '../helpers/getTools'
import Modal from '../modal'
import ContentAiToolbar from './contentAiToolbar'
import hasError from '../helpers/hasError'

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
					<Modal data={ completer } />,
					document.getElementById( 'rank-math-content-ai-modal-wrapper' )
				)
			}, 100 )
		},
	}
}

/**
 * Register Content AI Autocompleters to show AI tools on // & // .
 */
export default () => {
	registerFormatType( 'rank-math/content-ai', {
		title: __( 'Content AI', 'rank-math' ),
		tagName: 'p',
		className: null,
		edit: ContentAiToolbar,
	} )

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
