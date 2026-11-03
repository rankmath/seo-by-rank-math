/**
 * External dependencies
 */
import { isEmpty, isUndefined, isString, kebabCase, includes, forEach, isEqual, map, isNull, get } from 'lodash'

/**
 * WordPress dependencies
 */
import { store as blockEditorStore } from '@wordpress/block-editor'
import { __unstableStripHTML as stripHTML } from '@wordpress/dom'
import { useSelect, useDispatch } from '@wordpress/data'

// Conditionally generate anchor from heading text.
// `allowFabricatedAnchor` must be false for blocks with nowhere to persist a generated slug as a
// real HTML id, otherwise the TOC would link to an id that never exists on the front end.
const generateAnchor = ( anchor, headingText, isGeneratedLink, settings, allowFabricatedAnchor = true ) => {
	if ( ! isEmpty( anchor ) ) {
		return anchor
	}

	if ( ! allowFabricatedAnchor ) {
		return anchor
	}

	if ( ! isUndefined( settings.generateAnchors ) && settings.generateAnchors === true ) {
		return anchor
	}

	return isGeneratedLink ? kebabCase( stripHTML( headingText ) ) : anchor
}

/**
 * Extract the heading text off a third-party heading block's attributes.
 *
 * @param {Object} headingAttributes Block attributes.
 * @param {Object} blockConfig       Attribute mapping for this block name.
 *
 * @return {string} The heading text, or an empty string.
 */
const getGenericHeadingText = ( headingAttributes, blockConfig ) => {
	const rawContent = headingAttributes[ blockConfig.contentAttr ]

	// A `rich-text`-sourced attribute is a RichTextData instance, so read its `.text` getter
	// directly rather than gating on `isEmpty( rawContent )`, which is always true for a class
	// instance with no own enumerable properties.
	if ( blockConfig.contentType === 'richText' && ! isUndefined( rawContent ) && ! isNull( rawContent ) && ! isUndefined( rawContent.text ) ) {
		return isString( rawContent.text ) ? rawContent.text : ''
	}

	return isString( rawContent ) ? rawContent : ''
}

/**
 * Extract the numeric heading level (1-6) off a third-party heading block's attributes.
 *
 * @param {Object} headingAttributes Block attributes.
 * @param {Object} blockConfig       Attribute mapping for this block name.
 *
 * @return {number} The heading level, or NaN if it can't be determined.
 */
const getGenericHeadingLevel = ( headingAttributes, blockConfig ) => {
	const rawLevel = headingAttributes[ blockConfig.levelAttr ]

	return blockConfig.levelType === 'tag'
		? parseInt( String( rawLevel ).replace( /[^0-9]/g, '' ), 10 )
		: parseInt( rawLevel, 10 )
}

/**
 * Read an already-existing HTML id off a third-party heading block's attributes.
 *
 * Only ever returns an id the block already renders itself, never a fabricated one: unlike
 * `core/heading`, most third-party blocks don't declare the editor's "anchor" support, so there
 * is nowhere to persist a generated slug.
 *
 * @param {Object} headingAttributes Block attributes.
 * @param {Object} blockConfig       Attribute mapping for this block name.
 *
 * @return {string} The existing anchor/id, or an empty string if none is configured or set.
 */
const getGenericHeadingAnchor = ( headingAttributes, blockConfig ) => {
	if ( isUndefined( blockConfig.anchorAttr ) ) {
		return ''
	}

	const rawAnchor = get( headingAttributes, blockConfig.anchorAttr )

	return isString( rawAnchor ) ? rawAnchor : ''
}

/**
 * Get the headings from the content.
 *
 * @param {Array} headings        Array of headings data
 * @param {Array} excludeHeadings Heading levels to exclude
 */
export function GetLatestHeadings( headings, excludeHeadings ) {
	return useSelect(
		( select ) => {
			const {
				getBlockAttributes,
				getBlockName,
				getClientIdsWithDescendants,
				getSettings,
			} = select( blockEditorStore )
			const { __experimentalConvertBlockToStatic: convertBlockToStatic } = useDispatch( 'core/reusable-blocks' )

			// Third-party heading blocks registered via the `rank_math/schema/toc/heading_blocks` PHP filter.
			const genericHeadingBlocks = ( typeof rankMath !== 'undefined' && rankMath.tocHeadingBlocks ) || {}

			// Get the client ids of all blocks in the editor.
			const allBlockClientIds = getClientIdsWithDescendants()
			const _latestHeadings = []
			let i = 0
			const anchors = []

			// Build and push a heading entry shared by `core/heading` and third-party heading blocks.
			const pushHeadingEntry = ( blockClientId, level, headingText, existingAnchor, allowFabricatedAnchor = true ) => {
				const currentHeading = ! isUndefined( headings ) && ! isEmpty( headings[ _latestHeadings.length ] ) ? headings[ _latestHeadings.length ] : {
					content: '',
					level: '',
					disable: false,
					isUpdated: false,
					isGeneratedLink: true,
				}

				const isGeneratedLink = ! isUndefined( currentHeading.isGeneratedLink ) && currentHeading.isGeneratedLink

				const settings = getSettings()
				let anchor = generateAnchor( existingAnchor, headingText, isGeneratedLink, settings, allowFabricatedAnchor )

				if ( anchor && includes( anchors, anchor ) ) {
					i += 1
					anchor = anchor + '-' + i
				}

				if ( anchor ) {
					anchors.push( anchor )
				}

				const headingContent = isString( headingText ) ? stripHTML(
					headingText.replace(
						/(<br *\/?>)+/g,
						' '
					)
				) : ''

				const content = ! isUndefined( currentHeading.isUpdated ) && currentHeading.isUpdated ? currentHeading.content : headingContent
				const generatedLink = anchor ? `#${ anchor }` : ''

				_latestHeadings.push( {
					key: blockClientId,
					content: stripHTML( content ),
					level,
					link: ! isGeneratedLink ? currentHeading.link : generatedLink,
					disable: currentHeading.disable ? currentHeading.disable : false,
					isUpdated: ! isUndefined( currentHeading.isUpdated ) ? currentHeading.isUpdated : false,
					isGeneratedLink,
				} )
			}

			for ( const blockClientId of allBlockClientIds ) {
				const blockName = getBlockName( blockClientId )
				if ( blockName === 'core/block' ) {
					const attrs = getBlockAttributes( blockClientId )
					if ( ! isNull( attrs.ref ) ) {
						const reusableBlock = wp.data.select( 'core' ).getEditedEntityRecord( 'postType', 'wp_block', attrs.ref )
						const blocks = map( reusableBlock.blocks, ( block ) => {
							return block.name
						} )

						if ( includes( blocks, 'rank-math/toc-block' ) && ! isNull( getBlockAttributes( blockClientId ) ) ) {
							convertBlockToStatic( blockClientId )
						}
					}

					continue
				}

				const genericBlockConfig = genericHeadingBlocks[ blockName ]
				if ( ! includes( [ 'rank-math/faq-block', 'rank-math/howto-block', 'core/heading' ], blockName ) && ! genericBlockConfig ) {
					continue
				}

				const headingAttributes = getBlockAttributes( blockClientId )

				if ( genericBlockConfig ) {
					const level = getGenericHeadingLevel( headingAttributes, genericBlockConfig )
					if ( isNaN( level ) || level < 1 || level > 6 || includes( excludeHeadings, 'h' + level ) ) {
						continue
					}

					const headingText = getGenericHeadingText( headingAttributes, genericBlockConfig )
					const existingAnchor = getGenericHeadingAnchor( headingAttributes, genericBlockConfig )
					pushHeadingEntry( blockClientId, level, headingText, existingAnchor, false )

					continue
				}
				if ( blockName === 'rank-math/faq-block' || blockName === 'rank-math/howto-block' ) {
					const titleWrapper = headingAttributes.titleWrapper
					if (
						includes( excludeHeadings, titleWrapper ) ||
						includes( [ 'div', 'p' ], titleWrapper )
					) {
						continue
					}

					const data = blockName === 'rank-math/howto-block' ? headingAttributes.steps : headingAttributes.questions
					if ( isEmpty( data ) ) {
						continue
					}

					forEach( data, ( value ) => {
						const currentHeading = ! isUndefined( headings ) && ! isEmpty( headings[ _latestHeadings.length ] ) ? headings[ _latestHeadings.length ] : {
							content: '',
							level: '',
							disable: false,
							isUpdated: false,
							isGeneratedLink: true,
						}

						const isGeneratedLink = ! isUndefined( currentHeading.isGeneratedLink ) && currentHeading.isGeneratedLink
						const content = ! isUndefined( currentHeading.isUpdated ) && currentHeading.isUpdated ? currentHeading.content : value.title

						_latestHeadings.push( {
							key: value.id,
							content: stripHTML( content ),
							level: parseInt( headingAttributes.titleWrapper.replace( 'h', '' ) ),
							link: ! isGeneratedLink ? currentHeading.link : `#${ value.id }`,
							disable: currentHeading.disable ? currentHeading.disable : false,
							isUpdated: ! isUndefined( currentHeading.isUpdated ) ? currentHeading.isUpdated : false,
							isGeneratedLink,
						} )
					} )

					continue
				}

				if ( blockName === 'core/heading' ) {
					if ( includes( excludeHeadings, 'h' + headingAttributes.level ) ) {
						continue
					}

					const headingText = ! isEmpty( headingAttributes.content.text ) ? headingAttributes.content.text : headingAttributes.content
					pushHeadingEntry( blockClientId, headingAttributes.level, headingText, headingAttributes.anchor )
				}
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
