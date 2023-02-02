/**
 * External dependencies
 */
import { isEmpty, isUndefined, kebabCase, includes, forEach, isEqual, startsWith, map, isNull } from 'lodash'

/**
 * WordPress dependencies
 */
import { store as blockEditorStore } from '@wordpress/block-editor'
import { __unstableStripHTML as stripHTML } from '@wordpress/dom'
import { useSelect, useDispatch } from '@wordpress/data'
import { store as reusableBlocksStore } from '@wordpress/reusable-blocks'

/**
 * Get the headings from the content.
 *
 * @param {Array} headings        Array of headings data
 * @param {Array} excludeHeadings Heading levels to exclude
 */
export function GetLatestHeadings( headings, excludeHeadings ) {
	const { __experimentalConvertBlockToStatic: convertBlockToStatic } = useDispatch( reusableBlocksStore )
	return useSelect(
		( select ) => {
			const {
				getBlockAttributes,
				getBlockName,
				getClientIdsWithDescendants,
			} = select( blockEditorStore )

			// Get the client ids of all blocks in the editor.
			const allBlockClientIds = getClientIdsWithDescendants()
			const _latestHeadings = []
			let i = 0
			const anchors = []
			for ( const blockClientId of allBlockClientIds ) {
				const blockName = getBlockName( blockClientId )
				if ( blockName === 'core/block' ) {
					const attrs = getBlockAttributes( blockClientId )
					if ( ! isNull( attrs.ref ) ) {
						setImmediate( () => {
							const reusableBlock = wp.data.select( 'core' ).getEditedEntityRecord( 'postType', 'wp_block', attrs.ref )
							const blocks = map( reusableBlock.blocks, ( block ) => {
								return block.name
							} )

							if ( includes( blocks, 'rank-math/toc-block' ) && ! isNull( getBlockAttributes( blockClientId ) ) ) {
								convertBlockToStatic( blockClientId )
							}
						} )
					}

					continue
				}

				if ( blockName !== 'core/heading' ) {
					continue
				}

				const headingAttributes = getBlockAttributes( blockClientId )
				if ( includes( excludeHeadings, 'h' + headingAttributes.level ) ) {
					continue
				}

				const currentHeading = ! isUndefined( headings ) && ! isEmpty( headings[ _latestHeadings.length ] ) ? headings[ _latestHeadings.length ] : {
					content: '',
					level: '',
					disable: false,
					isUpdated: false,
					isGeneratedLink: true,
				}

				const isGeneratedLink = ! isUndefined( currentHeading.isGeneratedLink ) && currentHeading.isGeneratedLink
				let anchor = headingAttributes.anchor
				if ( isEmpty( headingAttributes.anchor ) || isGeneratedLink ) {
					anchor = kebabCase( headingAttributes.content )
				}

				if ( includes( anchors, anchor ) ) {
					i += 1
					anchor = anchor + '-' + i
				}

				anchors.push( anchor )
				headingAttributes.anchor = anchor

				const headingContent = stripHTML(
					headingAttributes.content.replace(
						/(<br *\/?>)+/g,
						' '
					)
				)

				_latestHeadings.push( {
					key: blockClientId,
					content: ! isUndefined( currentHeading.isUpdated ) && currentHeading.isUpdated ? currentHeading.content : `${ headingContent }`,
					level: headingAttributes.level,
					link: ! isGeneratedLink ? currentHeading.link : `#${ headingAttributes.anchor }`,
					disable: currentHeading.disable ? currentHeading.disable : false,
					isUpdated: ! isUndefined( currentHeading.isUpdated ) ? currentHeading.isUpdated : false,
					isGeneratedLink,
				} )
			}

			if ( isEqual( headings, _latestHeadings ) ) {
				return null
			}

			return _latestHeadings
		}
	)
}

/**
 * Nest heading based on the Heading level.
 *
 * @param {Array} headingList The flat list of headings to nest.
 *
 * @return {Array} The nested list of headings.
 */
export function linearToNestedHeadingList( headingList = [] ) {
	const nestedHeadingList = []
	forEach( headingList, ( heading, key ) => {
		if ( isEmpty( heading.content ) ) {
			return
		}

		// Make sure we are only working with the same level as the first iteration in our set.
		if ( heading.level === headingList[ 0 ].level ) {
			if ( headingList[ key + 1 ]?.level > heading.level ) {
				let endOfSlice = headingList.length
				for ( let i = key + 1; i < headingList.length; i++ ) {
					if ( headingList[ i ].level === heading.level ) {
						endOfSlice = i
						break
					}
				}

				nestedHeadingList.push( {
					heading,
					children: linearToNestedHeadingList(
						headingList.slice( key + 1, endOfSlice )
					),
				} )
			} else {
				nestedHeadingList.push( {
					heading,
					children: null,
				} )
			}
		}
	} )

	return nestedHeadingList
}
