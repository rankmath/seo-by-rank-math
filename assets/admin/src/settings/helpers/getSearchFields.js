/**
 * External Dependencies
 */
import { filter, flatMap, some, find, keys, values } from 'lodash'

/**
 * Internal Dependencies
 */
import { generalFields as general } from '../options/general'
import { titleFields as titles } from '../options/titles'
import { sitemapFields as sitemap } from '../options/sitemap'

const tabs = { ...general, ...titles, ...sitemap }

/**
 * Retrieve fields that can be shown.
 *
 * @return {Array} Array of fields that can be shown.
 */
export const getFields = () => {
	const fields = flatMap( tabs, ( option ) => option )

	// Filter fields by types that can be shown
	return filter(
		fields,
		( { type, exclude } ) => ! ( exclude || type === 'notice' || type === 'raw' )
	)
}

/**
 * Retrieves a field's options page and tab name.
 *
 * @param {string} id - The field ID.
 */
export const getFieldDetails = ( id ) => {
	const allOptions = { general, titles, sitemap }

	const hasId = ( fields ) => some( fields, ( field ) => field.id === id )

	const fieldPage = find( keys( allOptions ), ( page ) => some( values( allOptions[ page ] ), hasId ) ) || ''
	const fieldTab = find( keys( tabs ), ( tab ) => hasId( tabs[ tab ] ) ) || ''

	return { fieldPage, fieldTab }
}
