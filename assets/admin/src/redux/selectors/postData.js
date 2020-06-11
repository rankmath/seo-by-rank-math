/**
 * Get post id.
 *
 * @param {Object} state The app state.
 *
 * @return {number} Return post id.
 */
export function getPostID( state ) {
	return state.appData.postID
}

/**
 * Get title.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return post title.
 */
export function getTitle( state ) {
	return state.appData.title
}

/**
 * Get permalink.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return post permalink.
 */
export function getPermalink( state ) {
	return state.appData.permalink
}

/**
 * Get description.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return post desctription.
 */
export function getDescription( state ) {
	return state.appData.description
}

/**
 * Get featured image.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return featured image.
 */
export function getFeaturedImage( state ) {
	return state.appData.featuredImage
}

/**
 * Get featured image html.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return featured image html.
 */
export function getFeaturedImageHtml( state ) {
	const imageData = state.appData.featuredImage
	return `<img src="${ imageData.source_url }" alt="${ imageData.alt_text }" >`
}
