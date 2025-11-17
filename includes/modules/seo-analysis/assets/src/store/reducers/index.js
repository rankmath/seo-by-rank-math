const DEFAULT_STATE = {
	// SEO Analyzer Page.
	results: rankMath.results,
	url: rankMath.analyzeUrl,
}

/**
 * Reduces the dispatched action for the app ui state.
 *
 * @param {Object} state  The current state.
 * @param {Object} action The action that was just dispatched.
 *
 * @return {Object} The new state.
 */
export function appUi( state = DEFAULT_STATE, action ) {
	if ( 'RANK_MATH_APP_UI' === action.type ) {
		return {
			...state,
			[ action.key ]: action.value,
		}
	}

	return state
}
