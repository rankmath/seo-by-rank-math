/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks'

/**
 * Internal dependencies
 */
import edit from './edit'
import save from './save'
import deprecated from './deprecated'

/**
 * Register TOC block
 */
registerBlockType(
	'rank-math/toc-block',
	{
		edit,
		save,
		deprecated,
	}
)
