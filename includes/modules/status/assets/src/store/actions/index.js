/**
 * Internal dependencies
 */
import { updateAppUi } from './metadata'

/**
 * Update current view data.
 *
 * @param {string} tab  Active tab.
 * @param {Array}  data Tab data.
 */
export function updateViewData( tab, data ) {
	return updateAppUi( tab, { ...data } )
}
