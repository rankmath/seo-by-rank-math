/**
 * External dependencies
 */
import { isNil, isFunction } from 'lodash'

/**
 * Checks if the data API from Gutenberg is available.
 *
 * @return {boolean} True if the data API is available.
 */
const isGutenbergAvailable = () => {
	if (
		isNil( window.wp ) ||
		isNil( wp.data ) ||
		isNil( wp.data.select( 'core/editor' ) ) ||
		! window.document.body.classList.contains( 'block-editor-page' ) ||
		! isFunction( wp.data.select( 'core/editor' ).getEditedPostAttribute )
	) {
		return false
	}

	return true
}

export default isGutenbergAvailable
