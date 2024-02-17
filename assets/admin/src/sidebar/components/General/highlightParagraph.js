/**
 * External dependencies
 */
import { forEach, includes, remove } from 'lodash'

/**
 * WordPress dependencies
 */
import { select, dispatch } from '@wordpress/data'
import { count } from '@wordpress/wordcount'
import '@wordpress/annotations'

const ANNOTATION_NS = 'core/annotations'
const ANNOTATION_SOURCE = 'rank-math-annotations'

const tinyMceAnnotator = ( highlight ) => {
	const editor = window.tinymce.get( window.wpActiveEditor )
	if ( ! editor ) {
		return false
	}

	if ( ! highlight ) {
		editor.annotator.remove( ANNOTATION_SOURCE )
		return
	}

	const editorChildren = editor.getBody().children || []
	for ( const node of editorChildren ) {
		if ( 'p' !== node.localName ) {
			continue
		}

		if ( count( node.innerText, 'words' ) < 120 ) {
			editor.annotator.remove( ANNOTATION_SOURCE )

			if ( node.getElementsByClassName( 'rank-math-content-ai-tooltip' ).length ) {
				node.getElementsByClassName( 'rank-math-content-ai-tooltip' )[ 0 ].remove()
			}
			continue
		}

		const selection = editor.selection.win.getSelection()
		selection.selectAllChildren( node )

		editor.annotator.annotate( ANNOTATION_SOURCE, {
			uid: 'rank-math-annotation',
		} )

		selection.empty()
	}
}

export default ( highlight = true, highlightedParagraphs, updateHighlightedParagraphs ) => {
	if ( 'classic' === rankMath.currentEditor ) {
		tinyMceAnnotator( highlight )
		return
	}

	// Remove annotations.
	if ( ! highlight ) {
		dispatch( ANNOTATION_NS ).__experimentalRemoveAnnotationsBySource( ANNOTATION_SOURCE )
		updateHighlightedParagraphs( [] )
		return
	}

	// Add annotation.
	const blocks = select( 'core/block-editor' ).getBlocks()
	forEach( blocks, ( block ) => {
		if ( block.name !== 'core/paragraph' ) {
			return
		}

		const clientId = block.clientId
		if ( count( block.attributes.content, 'words' ) < 120 ) {
			if ( includes( highlightedParagraphs, clientId ) ) {
				dispatch( ANNOTATION_NS ).__experimentalRemoveAnnotation( clientId )
				highlightedParagraphs = remove( highlightedParagraphs, clientId )

				updateHighlightedParagraphs( highlightedParagraphs )
			}

			return
		}

		highlightedParagraphs.push( clientId )
		updateHighlightedParagraphs( highlightedParagraphs )

		dispatch( ANNOTATION_NS ).__experimentalAddAnnotation( {
			id: clientId,
			blockClientId: clientId,
			source: ANNOTATION_SOURCE,
			richTextIdentifier: 'content',
			range: {
				start: 0,
				end: block.attributes.content.length,
			},
		} )
	} )
}
