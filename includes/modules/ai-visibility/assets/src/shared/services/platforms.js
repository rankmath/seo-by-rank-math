/**
 * AI platform registry helpers — mirrors `Platforms` in PHP.
 *
 * @since 1.0.275
 */

/**
 * External dependencies
 */
import { map, filter } from 'lodash'

/**
 * Convert the localized registry map into a list, preserving key order.
 *
 * @param {Object} platforms Registry keyed by platform ID.
 * @return {Array<{id: string, label: string, enabled: boolean}>} Platform list.
 */
export const toPlatformList = ( platforms = {} ) =>
	map( platforms, ( platform, id ) => ( {
		id,
		label: platform?.label ?? id,
		enabled: Boolean( platform?.enabled ),
	} ) )

/**
 * Platform IDs available for selection.
 *
 * @param {Array} platformList Output of toPlatformList().
 * @return {string[]} Enabled platform IDs.
 */
export const getEnabledPlatformIds = ( platformList = [] ) =>
	map( filter( platformList, 'enabled' ), 'id' )

/**
 * Default selection for a new brand — the first enabled platform.
 *
 * @param {Array} platformList Output of toPlatformList().
 * @return {string[]} Default platform IDs.
 */
export const getDefaultPlatforms = ( platformList = [] ) =>
	getEnabledPlatformIds( platformList ).slice( 0, 1 )
