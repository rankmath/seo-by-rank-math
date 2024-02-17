/**
 * External dependencies
 */
import { forEach, debounce, isEmpty, isFunction } from 'lodash'

/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks'
import { isReusableBlock } from '@wordpress/blocks'
import { select, subscribe } from '@wordpress/data'

const reusableBlocks = []

/**
 * Add Reusable Block content in Content analysis
 *
 * @param {string} content Content
 *
 * @return {string} New content
 */
const addReusableBlockContent = ( content ) => {
	if ( isEmpty( reusableBlocks ) ) {
		return content
	}

	forEach( reusableBlocks, ( blocks ) => {
		forEach( blocks, ( blockContent ) => {
			content += blockContent
		} )
	} )

	return content
}

/**
 * Get Reusable Block content from Block data.
 *
 * @param {Object} blockData Reusable Block data.
 *
 * @return {string} New content
 */
const getBlockContent = ( blockData ) => {
	if ( ! blockData || ! blockData.content ) {
		return ''
	}

	if ( isFunction( blockData.content ) ) {
		return blockData.content( blockData )
	}

	return blockData.content
}

export default () => {
	const core = select( 'core' )
	const coreEditor = select( 'core/editor' )

	addFilter( 'rank_math_content', 'rank-math', addReusableBlockContent, 11 )
	subscribe( debounce( () => {
		const blocks = coreEditor.getBlocks()
		if ( isEmpty( blocks ) ) {
			return
		}

		let refreshResults = false
		forEach( blocks, ( block ) => {
			if ( ! isReusableBlock( block ) ) {
				return
			}

			const blockID = block.attributes.ref
			const blockData = core.getEditedEntityRecord( 'postType', 'wp_block', blockID )
			const content = getBlockContent( blockData )

			if ( ! reusableBlocks[ blockID ] ) {
				reusableBlocks[ blockID ] = {
					id: blockID,
					clientId: block.clientId,
					content,
				}
				refreshResults = true

				return
			}

			if ( reusableBlocks[ blockID ].content !== content ) {
				reusableBlocks[ blockID ].content = content
				refreshResults = true
			}
		} )

		if ( refreshResults ) {
			rankMathEditor.refresh( 'content' )
		}
	}, 500 ) )
}
