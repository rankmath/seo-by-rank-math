/**
 * Internal dependencies
 */
import { updateAppData } from './metadata'

/**
 * Update primary term ID.
 *
 * @param {number} id       The new ID.
 * @param {string} taxonomy The taxonomy.
 *
 * @return {Object} An action for redux.
 */
export function updatePrimaryTermID( id, taxonomy ) {
	return updateAppData(
		'primaryTerm',
		parseInt( id ),
		'rank_math_primary_' + taxonomy
	)
}
