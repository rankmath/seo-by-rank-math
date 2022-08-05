/**
 * Get news sitemap robots.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return keywords data.
 */
export function getKeywordsData( state ) {
	return state.appUi.keywordsData
}

export function getContentAIScore( state ) {
	return state.appData.contentAIScore
}
