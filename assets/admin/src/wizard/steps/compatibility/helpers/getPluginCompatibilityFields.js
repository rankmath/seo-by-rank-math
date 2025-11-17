/**
 * External dependencies
 */
import { map, concat } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import getPhpVersionRecommendation from './getPhpVersionRecommendation'
import getHeading from './getHeading'

/**
 * Returns the plugin/feature status.
 *
 * @param {*} hasPassed
 */
const getStatus = ( hasPassed ) => (
	<span
		className={ `dashicons dashicons-${ hasPassed ? 'yes' : 'no' }` }
	/>
)

export default ( {
	phpVersion,
	phpVersionOk,
	phpVersionRecommended,
	extensions,
	isWhitelabel,
} ) => {
	const { dom, simpleXml, image, mbString, openSsl, base64Func } = extensions
	const tableData = [
		{
			feature: __(
				'You are using the recommended WordPress version.',
				'rank-math'
			),
			passed: true,
			showStatus: false,
		},
		{
			feature: __( 'PHP DOM Extension', 'rank-math' ),
			passed: dom,
		},
		{
			feature: __( 'PHP SimpleXML Extension', 'rank-math' ),
			passed: simpleXml,
		},
		{
			feature: __( 'PHP GD or Imagick Extension', 'rank-math' ),
			passed: image,
		},
		{
			feature: __( 'PHP MBstring Extension', 'rank-math' ),
			passed: mbString,
		},
		{
			feature: __( 'PHP OpenSSL Extension', 'rank-math' ),
			passed: openSsl,
			showStatus: mbString,
		},
		{
			feature: __( 'Base64 encode & decode functions', 'rank-math' ),
			passed: base64Func,
			isBase64: true,
		},
	]

	let tableFields = map( tableData, ( data ) => {
		const heading = getHeading( data )
		const value = getStatus( data.status ?? data.passed )
		return [ heading, value ]
	} )

	const phpVersionRecommendationText = getPhpVersionRecommendation( {
		phpVersion,
		phpVersionOk,
		phpVersionRecommended,
		isWhitelabel,
	} )

	tableFields = concat(
		[ [ phpVersionRecommendationText, getStatus( phpVersion ) ] ],
		tableFields
	)

	return tableFields
}
