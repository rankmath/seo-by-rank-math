/**
 * External dependencies.
 */
import { isEmpty, isUndefined } from 'lodash'

/**
 * Get Block Content for the given block.
 * This function is needed because since WPv6.5, the block content is added in the attributes.content.text.
 * In earlier versions, it was added directly in the attributes.content.
 *
 * @param {Object}  block         Selected Block
 * @param {boolean} returnContent Whether to return the content or only a boolean value when the block has content. This boolean value is used in the Write tab to position the content.
 */
export default ( block, returnContent = true ) => {
	if ( ! returnContent ) {
		return isEmpty( block.attributes.content ) && isEmpty( block.innerBlocks )
	}

	return ! isUndefined( block.attributes.content ) && ! isEmpty( block.attributes.content.text ) ? block.attributes.content.text : block.attributes.content
}
