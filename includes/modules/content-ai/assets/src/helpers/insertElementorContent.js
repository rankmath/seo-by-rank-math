/**
 * External dependencies
 */
import jQuery from 'jquery'
import { forEach } from 'lodash'

/**
 * Internal dependencies
 */
import markdownConverter from '../helpers/markdownConverter'
import getTypingWorker from '../helpers/getTypingWorker'

const insertContent = ( value ) => {
	const doc = elementor.$preview[ 0 ].contentWindow.document
	let element = doc.getElementsByClassName( 'rank-math-active' )

	if ( ! element.length ) {
		element = doc.getElementsByClassName( 'elementor-widget-container' )
		element = element[ element.length - 1 ]
	}

	jQuery( element ).trigger( 'click' )
	const activeEelment = jQuery( element ).find( '[data-elementor-setting-key]' )
	const attrs = activeEelment.data()

	setTimeout( () => {
		if ( attrs.elementorSettingKey === 'title' ) {
			const editor = jQuery( '[data-setting="title"]' )
			editor.val( editor.val() + ' ' + value )
			activeEelment.text( activeEelment.text() + ' ' + value )
		} else {
			const editor = tinymce.activeEditor
			editor.selection.select( editor.getBody(), true )
			editor.selection.collapse( false )

			editor.insertContent( ' ' + value )
		}
	}, 300 )

	jQuery( element ).removeClass( 'rank-math-active' )
}

// Add Text Editor widget when content is blank.
const maybeAddTextAreaWidget = () => {
	const container = elementor.settings.page.getEditedView().getContainer()
	if ( container.children.length ) {
		return
	}

	const args = {
		model: {
			custom: '',
			elType: 'widget',
			widgetType: 'text-editor',
		},
		options: {
			at: undefined,
			side: 'top',
			default: '',
			value: '',
			text: '',
			html: '',
		},
		container,
	}

	$e.run( 'preview/drop', args )
}

const processContent = ( content, typingEffect ) => {
	const doc = elementor.$preview[ 0 ].contentWindow.document
	const arrayNodes = Array.from( doc.getElementsByClassName( 'elementor-widget-container' ) )
	forEach( arrayNodes.reverse(), ( block ) => {
		if ( ! block.innerText ) {
			return
		}

		jQuery( block ).addClass( 'rank-math-active' )
	} )

	content = markdownConverter( content )
	if ( ! typingEffect ) {
		insertContent( content )
		return
	}

	const typingWorker = getTypingWorker()
	typingWorker.onmessage = ( event ) => {
		const value = event.data
		if ( ! value || 'rank_math_process_complete' === value ) {
			return
		}

		insertContent( value )
	}

	typingWorker.postMessage( content )
}

export default ( content, typingEffect = true ) => {
	maybeAddTextAreaWidget()

	// Timeout is needed here to process the content only after container is added.
	setTimeout( () => {
		processContent( content, typingEffect )
	}, 500 )
}
