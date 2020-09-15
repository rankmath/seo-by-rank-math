/**
 * External Dependencies
 */
import { isUndefined } from 'lodash'

/**
 * WordPress Dependencies
 */
import { updateCategory } from '@wordpress/blocks'

/**
 * Internal Dependencies
 */
import icon from '@blocks/icon'
import blockFAQ from '@blocks/faqs'
import blockHowTo from '@blocks/howto'
import schemaSnippet from '@blocks/schemaSnippet'

/**
 * Update category icon.
 */
( function() {
	updateCategory( 'rank-math-blocks', { icon } )
}() )

/**
 * Register FAQ block.
 */
blockFAQ()

/**
 * Register HowTo block.
 */
blockHowTo()

/**
 * Register Schema block.
 */
if ( rankMath.canUser.snippet && ! isUndefined( rankMath.schemas ) ) {
	schemaSnippet()
}
