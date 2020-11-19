/**
 * Internal dependencies
 */
import { updateAppData, updateAppUi } from './metadata'
import { setCookie } from '@helpers/cookies'

/**
 * WordPress dependencies
 */
import { select } from '@wordpress/data'
import apiFetch from '@wordpress/api-fetch'

/**
 * Update days range.
 *
 * @param {Array} range The new stats.
 *
 * @return {Object} An action for redux.
 */
export function updateDaysRange( range ) {
	setCookie( 'rank_math_analytics_date_range', range )
	return updateAppData( 'daysRange', range )
}

/**
 * Update days range.
 *
 * @param {Object} pref The new stats.
 * @param {string} key Key to preference.
 *
 * @return {Object} An action for redux.
 */
export function updateUserPreferences( pref, key ) {
	const preferences = { ...select( 'rank-math' ).getUserColumnPreference() }
	preferences[ key ] = pref

	apiFetch( {
		method: 'POST',
		path: 'rankmath/v1/an/userPreferences',
		data: { preferences },
	} )

	return updateAppUi( 'userColumnPreference', preferences )
}
