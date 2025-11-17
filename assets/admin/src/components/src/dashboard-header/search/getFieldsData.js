
/**
 * External dependencies
 */
import { includes, forEach, isEmpty } from 'lodash'

/**
 * Internal dependencies
 */
import getTabs from '@rank-math-settings/pages'

/**
 * Retrieves and structures all fields data from multiple Rank Math settings pages.
 *
 * @return {Promise<Object>} A map of setting -> tab -> field[]
 */

export default async () => {
	const result = {}

	for ( const setting of [ 'general', 'titles', 'sitemap' ] ) {
		result[ setting ] = {}
		try {
			const data = await getTabs( setting )
			forEach( data, ( tab ) => {
				if ( isEmpty( tab.fields ) ) {
					return
				}

				result[ setting ][ tab.name ] = []
				forEach( tab.fields, ( field ) => {
					if ( includes( [ 'notice', 'raw' ], field.type ) ) {
						return
					}
					result[ setting ][ tab.name ].push( field )
				} )
			} )
		} catch ( error ) {
			// If required global vars aren't available for this page
			result[ setting ] = {}
		}
	}

	return result
}
