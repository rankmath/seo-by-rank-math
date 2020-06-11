/**
 * Internal dependencies
 */
import { updateAppData } from './metadata'

/**
 * Update postID.
 *
 * @param {string} postID The new postID.
 *
 * @return {Object} An action for redux.
 */
export function updatePostID( postID ) {
	rankMath.objectID = postID
	return updateAppData( 'postID', postID )
}

/**
 * Update permalink.
 *
 * @param {string} permalink Permalink to update.
 *
 * @return {Object} An action for redux.
 */
export function updatePermalink( permalink ) {
	return updateAppData( 'permalink', permalink, 'permalink' )
}

/**
 * Update title.
 *
 * @param {string} title Title to update.
 *
 * @return {Object} An action for redux.
 */
export function updateTitle( title ) {
	return updateAppData( 'title', title, 'rank_math_title' )
}

/**
 * Update description.
 *
 * @param {string} description Description to update.
 *
 * @return {Object} An action for redux.
 */
export function updateDescription( description ) {
	return updateAppData( 'description', description, 'rank_math_description' )
}

/**
 * Update featuredImage.
 *
 * @param {Object} featuredImage The new featuredImage.
 *
 * @return {Object} An action for redux.
 */
export function updateFeaturedImage( featuredImage ) {
	return updateAppData( 'featuredImage', featuredImage )
}
