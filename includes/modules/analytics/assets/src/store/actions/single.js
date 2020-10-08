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
 *
 * @return {Object} An action for redux.
 */
export function updateSinglePost( id, post ) {
	const posts = { ...select( 'rank-math' ).getSinglePosts() }
	posts[ id ] = post
	return updateAppData( 'singlePost', posts )
}

/**
 * Update post rows.
 *
 * @param {number} id Single post id.
 * @param {Object} data The pagespeed data.
 *
 * @return {Object} An action for redux.
 */
export function updatePagespeed( id, data ) {
	const speeds = { ...select( 'rank-math' ).getPagespeed() }
	speeds[ id ] = data
	return updateAppData( 'pageSpeed', speeds )
}
