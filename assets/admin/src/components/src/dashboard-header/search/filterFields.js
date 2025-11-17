/**
 * External dependencies
 */
import { flatMap, entries, filter, map, includes, toLower } from 'lodash'

/**
 * Filters field entries across settings for a search term.
 *
 * @param {Object} fields      All structured fields.
 * @param {string} searchValue The search keyword.
 *
 * @return {Array<Object>} Matching fields with metadata (setting, tab).
 */
export default ( fields, searchValue ) => {
	if ( ! searchValue || ! fields || typeof fields !== 'object' ) {
		return []
	}

	const query = searchValue.toLowerCase()

	return flatMap( entries( fields ), ( [ setting, tabs ] ) =>
		flatMap( entries( tabs ), ( [ tab, tabFields ] ) =>
			map(
				filter( tabFields, ( field ) => includes( toLower( field.name ), query ) || includes( toLower( field.desc ), query ) ),
				( field ) => ( { ...field, setting, tab } )
			)
		)
	)
}
