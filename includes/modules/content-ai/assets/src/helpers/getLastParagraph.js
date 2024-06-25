//global tinyMCE

/**
 * External dependencies
 */
import jQuery from 'jquery'
import { forEach, includes, isEmpty, isUndefined } from 'lodash'

/**
 * Wordpress dependencies
 */
import { select } from '@wordpress/data'

/**
 * Internal dependencies
 */
import isTinymceActive from '@helpers/isTinymceActive'
import getSelectedBlock from './getSelectedBlock'
import getBlockContent from './getBlockContent'
import markdownConverter from './markdownConverter'

/**
 * Get last paragraph from different editor.
 */
export default ( length = 0 ) => {
	let remainingLength = 800 - length
	const paragraphs = []

	// Get last paragraph from Elementor editor
	if ( rankMath.currentEditor === 'elementor' ) {
		const doc = elementor.$preview[ 0 ].contentWindow.document
		let element = doc.getElementsByClassName( 'elementor-element-editable' )
		let childrens = []
		element = doc.getElementsByClassName( 'elementor-widget-container' )
		childrens = element

		const arrayNodes = Array.from( childrens )
		let addedClass = false
		forEach( arrayNodes.reverse(), ( block ) => {
			if ( length >= 800 ) {
				return false
			}

			if ( ! block.innerText ) {
				return
			}

			if ( jQuery( block ).parents( '.elementor-element-editable' ).length ) {
				jQuery( doc ).find( '.rank-math-active' ).removeClass( 'rank-math-active' )
				jQuery( block ).addClass( 'rank-math-active' )
				addedClass = true
			}

			if ( ! addedClass ) {
				jQuery( block ).addClass( 'rank-math-active' )
				addedClass = true
			}

			const innerElements = block.querySelectorAll( '*' )
			const childNodes = Array.from( innerElements )
			forEach( childNodes.reverse(), ( child ) => {
				if ( length >= 600 ) {
					return false
				}

				const content = includes( [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ], child.localName ) ? 'Heading: ' + child.innerText : child.innerText
				if ( ! content || includes( [ 'div', 'b', 'i', 'u', 'em' ], child.localName ) ) {
					return
				}

				if ( content.length <= remainingLength ) {
					paragraphs.push( content )
					length = length + content.length
					remainingLength = remainingLength - content.length
				} else {
					const sentences = content.match( /[^\.!\?]+[\.!\?]+/g )

					forEach( sentences.reverse(), ( sentence ) => {
						if ( length < 600 ) {
							paragraphs.push( sentence )
							length = length + sentence.length
							remainingLength = remainingLength - sentence.length
						}
					} )
				}
			} )
		} )

		return markdownConverter( paragraphs.reverse().join( '\n\n' ), true )
	}

	// Get last paragraph from Classic editor
	if ( isTinymceActive() ) {
		const blocks = tinyMCE.activeEditor.selection.getSelectedBlocks()
		forEach( blocks.reverse(), ( block ) => {
			if ( length >= 800 ) {
				return false
			}

			const content = includes( [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ], block.localName ) ? '<' + block.localName + '>' + block.innerText + '</' + block.localName + '>' : block.innerHTML
			if ( content.length <= remainingLength ) {
				paragraphs.push( content )
				length = length + content.length
				remainingLength = remainingLength - content.length
			} else {
				const sentences = content.match( /[^\.!\?]+[\.!\?]+/g )

				forEach( sentences.reverse(), ( sentence ) => {
					if ( length < 800 ) {
						paragraphs.push( sentence )
						length = length + sentence.length
						remainingLength = remainingLength - sentence.length
					}
				} )
			}
		} )

		return markdownConverter( paragraphs.reverse().join( '\n\n' ), true )
	}

	// Get last paragraph from Block editor
	const selectedBlock = getSelectedBlock()
	if ( isEmpty( selectedBlock.block ) ) {
		return ''
	}

	const selectionID = selectedBlock.clientId
	const blocks = []
	forEach( select( 'core/block-editor' ).getBlocks(), ( block ) => {
		if ( 'rank-math/command' !== block.name ) {
			blocks.push( block )
		}

		if ( block.clientId === selectionID ) {
			return false
		}
	} )

	forEach( blocks.reverse(), ( block ) => {
		if ( length >= 800 ) {
			return false
		}

		const blockContent = getBlockContent( block )
		if ( isEmpty( blockContent ) ) {
			return
		}

		const content = block.name === 'core/heading' ? '<h' + block.attributes.level + '>' + blockContent + '</h' + block.attributes.level + '>' : '<p>' + blockContent + '</p>'
		if ( content.length <= remainingLength ) {
			paragraphs.push( content )
			length = length + content.length
			remainingLength = remainingLength - content.length
		} else {
			const sentences = content.match( /[^\.!\?]+[\.!\?]+/g )
			if ( ! isEmpty( sentences ) ) {
				forEach( sentences.reverse(), ( sentence ) => {
					if ( length < 800 ) {
						paragraphs.push( sentence )
						length = length + sentence.length
						remainingLength = remainingLength - sentence.length
					}
				} )
			}
		}
	} )

	return markdownConverter( paragraphs.reverse().join( '\n\n' ), true )
}
