/**
 * Get posts rows.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return posts rows.
 */
export function getSinglePosts( state ) {
	return state.appData.singlePost
}

/**
 * Get dashboard stats.
 *
 * @param {Object} state The app state.
 * @param {number} id Single post id.
 *
 * @return {Object} Return single post.
 */
export function getSinglePost( state, id ) {
	return state.appData.singlePost[ id ]
}
