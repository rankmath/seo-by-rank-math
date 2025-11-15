/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Returns the plugin/feature details.
 *
 * @param {*} pluginFeatures
 */
export default ( { feature, passed, showStatus = true, isBase64 = false } ) => {
	const passedText = isBase64
		? __( 'available', 'rank-math' )
		: __( 'installed', 'rank-math' )

	const statusText = passed ? passedText : __( 'missing', 'rank-math' )
	const status = showStatus ? statusText : ''

	return `${ feature } ${ status }`
}
