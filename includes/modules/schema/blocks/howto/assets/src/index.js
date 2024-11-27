/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks'

/**
 * Internal dependencies
 */
import example from './example'
import edit from './edit'
import save from './save'
import transforms from './transforms'

/**
 * Register HowTo block.
 */
registerBlockType(
	'rank-math/howto-block',
	{
		example,
		edit,
		save,
		transforms,
	}
)
