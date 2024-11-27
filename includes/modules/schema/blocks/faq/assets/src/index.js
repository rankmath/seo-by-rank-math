/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks'

/**
 * Internal dependencies
 */
import example from './example'
import edit from './edit'
import transforms from './transforms'
import save from './save'

/**
 * Register FAQ block.
 */
registerBlockType(
	'rank-math/faq-block',
	{
		example,
		edit,
		save,
		transforms,
	}
)
