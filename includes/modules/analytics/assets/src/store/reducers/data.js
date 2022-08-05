/**
 * Internal dependencies
 */
import { getCookie } from '@helpers/cookies'

const DEFAULT_STATE = {
	score: 0,
	dashboardStats: false,
	keywordsOverview: false,
	postsOverview: false,
	postsRows: {},
	postsRowsByObjects: {},
	indexingReport: {},
	postsSummary: false,
	keywordsRows: {},
	keywordsSummary: false,
	singlePost: false,
	daysRange: getCookie( 'rank_math_analytics_date_range', '-30 days' ),
	analyticsSummary: false,
}

/**
 * Reduces the dispatched action for the app state.
 *
 * @param {Object} state  The current state.
 * @param {Object} action The action that was just dispatched.
 *
 * @return {Object} The new state.
 */
export function appData( state = DEFAULT_STATE, action ) {
	if ( 'RANK_MATH_APP_DATA' === action.type ) {
		return {
			...state,
			[ action.key ]: action.value,
		}
	}

	return state
}
