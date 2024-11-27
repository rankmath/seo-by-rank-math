/**
 * External Dependencies
 */
import { isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks'

/**
 * Internal dependencies
 */
import edit from './edit'

/**
 * Register Schema block.
 */
registerBlockType(
	'rank-math/rich-snippet',
	{
		edit,
	}
)
