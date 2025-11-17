// eslint-disable-next-line eslint-comments/disable-enable-pair
/* eslint-disable @wordpress/i18n-translator-comments */
/**
 * External dependencies
 */
import { get } from 'lodash'

const DEFAULT_STATE = {
	data: get( rankMath, 'data', {} ),
	analytics: {
		...rankMath,
		...get( rankMath, 'data', {} ),
		...get( rankMath, 'analytics', {} ),
	},
	// Misc.
	dirtySettings: {},
}

const RESET_STATE = { ...DEFAULT_STATE }

/**
 * Reduces the dispatched action for the app state.
 *
 * @param {Object} state  The current state.
 * @param {Object} action The action that was just dispatched.
 *
 * @return {Object} The new state.
 */
export function appData( state = DEFAULT_STATE, action ) {
	let dirtySettings = {
		...state.dirtySettings,
	}

	if ( false !== action.settingsKey ) {
		dirtySettings = action.settingsValue
	}

	if ( 'RANK_MATH_SETTINGS_DATA' === action.type ) {
		if ( 'dirtySettings' === action.key ) {
			return {
				...state,
				dirtySettings: action.value,
			}
		}

		return {
			...state,
			[ action.key ]: action.value,
			dirtySettings,
		}
	}

	if ( 'RESET_STORE' === action.type ) {
		return RESET_STATE
	}

	return state
}
