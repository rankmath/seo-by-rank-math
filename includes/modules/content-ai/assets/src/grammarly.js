/**
 * External dependencies
 */
import jQuery from 'jquery'
import { isUndefined } from 'lodash'
import * as Grammarly from '@grammarly/editor-sdk'

/**
 * WordPress dependencies
 */
import { subscribe, select } from '@wordpress/data'

/**
 * Get Grammarly
 */
export const rankMathGrammarly = async () => {
	if ( isUndefined( rankMath.canAddGrammarly ) || ! rankMath.canAddGrammarly ) {
		return false
	}

	return await Grammarly.init( 'client_1koEZ9cDaJXxfVojByQKKL' )
}

if ( 'classic' === rankMath.currentEditor ) {
	jQuery( window ).on( 'scroll.editor-expand resize.editor-expand', function( event ) {
		const wrap = document.getElementById( 'wp-content-wrap' )
		const wrapRect = wrap.getBoundingClientRect()
		const button = jQuery( '.rank-math-grammarly-button' )

		button.removeClass()

		if ( wrapRect.bottom <= event.currentTarget.innerHeight ) {
			button.addClass( 'rank-math-grammarly-button rank-math-grammarly-button-absolute' )
		} else {
			button.addClass( 'rank-math-grammarly-button rank-math-grammarly-button-fixed' )
		}
	} )
}

const checkBlockEditorReady = () => {
	// If Block Editor only then it exist.
	const coreEditor = select( 'core/editor' )
	if ( ! coreEditor ) {
		unsubscribe()
		return
	}

	const isEditorReady = coreEditor.__unstableIsEditorReady()
	if ( isEditorReady ) {
		initGrammarlyForBlockEditor()
		unsubscribe()
	}
}

// Subscribe to changes in the editor
const unsubscribe = subscribe( checkBlockEditorReady )

// Check if the editor is already ready
checkBlockEditorReady()

/**
 * Init Grammarly for the Block Editor
 */
const initGrammarlyForBlockEditor = async () => {
	const grammarly = await rankMathGrammarly()
	if ( ! grammarly ) {
		return
	}

	const content = document.querySelector( '.wp-block-post-content' )
	if ( ! content ) {
		return
	}

	// Add Grammarly floating button.
	const button = document.createElement( 'grammarly-button' )
	button.classList.add( 'rank-math-grammarly-button' )

	const footer = document.querySelector( '.edit-post-layout__footer' )
	footer.append( button )

	// Init Grammalry.
	grammarly.addPlugin( content )
}
