/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks'

/**
 * Internal dependencies
 */
import edit from './edit'
import deprecated from './deprecated'

/**
 * Register TOC block
 */
registerBlockType(
	'rank-math/toc-block',
	{
		usesContext: [ 'postId' ],
		edit,
		deprecated,
	}
)
