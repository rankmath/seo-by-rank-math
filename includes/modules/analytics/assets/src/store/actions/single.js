/**
 * External dependencies
 */
import { isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { select } from '@wordpress/data'

/**
 * Internal dependencies
 */
import { updateAppData } from './metadata'

/**
 * Update post rows.
 *
 * @param {number} id Single post id.
 * @param {Object} post The post.
 * @param {string} params The filter parameter.
 *
 * @return {Object} An action for redux.
 */
export function updateSinglePost( id, post, params ) {
	const posts = { ...select( 'rank-math' ).getSinglePosts() }

	posts[ id ] = ! isUndefined( posts[ id ] ) ? posts[ id ] : {}
	posts[ id ][ params ] = post

	return updateAppData( 'singlePost', posts )
}
