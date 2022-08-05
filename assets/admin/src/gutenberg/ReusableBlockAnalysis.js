/**
 * External dependencies
 */
import { forEach, debounce, isEmpty, isUndefined, isFunction } from 'lodash'

/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks'
import { isReusableBlock } from '@wordpress/blocks'
import { select, subscribe } from '@wordpress/data'

/**
 * RankMath Reusable Block integration class
 */
class ReusableBlockAnalysis {
	constructor() {
		this.core = select( 'core' )
		this.coreEditor = select( 'core/editor' )
		this.reusableBlocks = {}
		this.getContent = this.getContent.bind( this )
		this.reusableBlockEvent = this.reusableBlockEvent.bind( this )
		this.init()
	}

	/**
	 * Hook into Rank Math App eco-system
	 */
	init() {
		addFilter( 'rank_math_content', 'rank-math', this.getContent, 11 )

		subscribe( debounce( this.reusableBlockEvent, 500 ) )
	}

	/**
	 * Gather Reusable Block data for analysis
	 *
	 * @param {string} content Content
	 *
	 * @return {string} New content
	 */
	getContent( content ) {
		if ( isEmpty( this.reusableBlocks ) ) {
			return content
		}

		forEach( this.reusableBlocks, ( blocks ) => {
			forEach( blocks, ( blockContent ) => {
				content += blockContent
			} )
		} )

		return content
	}

	/**
	 * Event listener for Reusable block
	 */
	reusableBlockEvent() {
		const { blocks } = this.coreEditor.getPostEdits()
		if ( isEmpty( blocks ) ) {
			return
		}

		let refreshResults = false
		forEach( blocks, ( block ) => {
			if ( ! isReusableBlock( block ) ) {
				return
			}

			const clientId = block.clientId
			const content = this.getBlockContent( block.attributes.ref )
			if (
				isUndefined( this.reusableBlocks[ block.attributes.ref ] ) ||
				isUndefined( this.reusableBlocks[ block.attributes.ref ][ clientId ] )
			) {
				if ( isUndefined( this.reusableBlocks[ block.attributes.ref ] ) ) {
					this.reusableBlocks[ block.attributes.ref ] = {}
				}

				this.reusableBlocks[ block.attributes.ref ][ clientId ] = content
				refreshResults = true

				return
			}

			if ( this.reusableBlocks[ block.attributes.ref ][ clientId ].content !== content ) {
				this.reusableBlocks[ block.attributes.ref ][ clientId ] = content
				refreshResults = true
			}
		} )

		if ( refreshResults ) {
			rankMathEditor.refresh( 'content' )
		}
	}

	/**
	 * Get Reusable Block content by block ID.
	 *
	 * @param {number} blockID Reusable Block ID.
	 *
	 * @return {string} New content
	 */
	getBlockContent( blockID ) {
		const blockData = this.core.getEditedEntityRecord( 'postType', 'wp_block', blockID )
		if ( ! blockData || ! blockData.content ) {
			return
		}

		if ( isFunction( blockData.content ) ) {
			return blockData.content( blockData )
		}

		return blockData.content
	}
}

export default ReusableBlockAnalysis
