/**
 * External dependencies
 */
import jQuery from 'jquery'
import { includes, map } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import ajax from '@helpers/ajax'
import { Button } from '@rank-math/components'

export default ( conflictingPlugins ) => {
	/**
	 * Deactivate conflicting plugins to prevent compatibility issues.
	 *
	 * @param {Event}  event  The button click event.
	 * @param {string} plugin The name of the plugin to be deactivated.
	 */
	const handleDeactivate = ( event, plugin ) => {
		const target = jQuery( event.target )
		ajax( 'deactivate_plugins', { plugin } )
			.always( ( data ) => {
				if ( 1 === data ) {
					target
						.parents( 'tr' )
						.find( '.dashicons-warning' )
						.removeClass( 'dashicons-warning' )
						.addClass( 'dashicons-yes' )
					target
						.text( __( 'Deactivated', 'rank-math' ) )
						.attr( 'disabled', 'disabled' )
				}
			} )
	}

	return map( conflictingPlugins, ( name, plugin ) => {
		const canImportList = [
			'all-in-one-schemaorg-rich-snippets/index.php',
			'wordpress-seo/wp-seo.php',
			'wordpress-seo-premium/wp-seo-premium.php',
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
		]

		const canImportDescription = includes( canImportList, plugin )
			? `<span class='import-info'>${ __(
				'You can import settings in the next step.',
				'rank-math'
			) }</span>`
			: ''

		const pluginDescription = `<span class="dashicons dashicons-warning"></span> ${ name } ${ canImportDescription }`
		return [
			pluginDescription,
			<Button
				key={ plugin }
				size="small"
				variant="secondary"
				className="wizard-deactivate-plugin"
				onClick={ ( event ) => handleDeactivate( event, plugin ) }
			>
				{ __( 'Deactivate Plugin', 'rank-math' ) }
			</Button>,
		]
	} )
}
