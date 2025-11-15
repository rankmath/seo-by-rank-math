/**
 * External dependencies
 */
import { entries, filter, map } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __, sprintf } from '@wordpress/i18n'

/**
 * Internal Dependencies
 */
import getLink from '@helpers/getLink'

/**
 * Get description for choice field.
 *
 * @param {string}  slug     Plugin slug.
 * @param {Array}   name     Plugin name.
 * @param {boolean} isActive Is plugin active.
 */
const getChoiceDescription = ( slug, name, isActive ) => {
	let desc

	if ( slug === 'aio-rich-snippet' ) {
		// translators: %s is plugin name
		desc = sprintf( __( 'Import meta data from the %s plugin.', 'rank-math' ), name )
	} else {
		// translators: %s is plugin name
		desc = sprintf( __( 'Import settings and meta data from the %s plugin.', 'rank-math' ), name )
	}

	// translators: %1$s is link to Knowledge Base article
	desc += ' ' + sprintf( __( 'The process may take a few minutes if you have a large number of posts or pages <a href="%1$s">Learn more about the import process here.</a>', 'rank-math' ), getLink( 'seo-import', 'SW Import Step' ) )

	if ( isActive ) {
		// translators: %s is plugin name
		desc += '<br>' + sprintf( __( '%s plugin will be disabled automatically moving forward to avoid conflicts. <strong>It is thus recommended to import the data you need now.</strong>', 'rank-math' ), name )
	}

	return desc
}

/**
 * Retrieves all Redirection, SEO and Schema plugins currently installed
 *
 * @param data
 */
export default ( data ) => {
	const plugins = map(
		entries( data.importablePlugins ),
		( [ plugin, pluginOptions ] ) => {
			const { name, choices, isActive, checked } = pluginOptions

			if ( ! checked ) {
				return
			}

			const metaOptions = map( entries( choices ), ( [ id, label ] ) => ( { id, label } ) )
			const metaDescription = getChoiceDescription( plugin, name, isActive )

			return {
				...pluginOptions,
				plugin,
				metaOptions,
				metaDescription,
			}
		}
	)

	return filter( plugins, Boolean )
}
