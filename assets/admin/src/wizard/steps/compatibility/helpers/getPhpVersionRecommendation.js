/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'

/**
 * Returns the recommended PHP version.
 *
 * @param {*} params
 */
export default ( { phpVersion, phpVersionOk, phpVersionRecommend, isWhitelabel } ) => {
	if ( ! phpVersionOk ) {
		return sprintf(
			// translators: php version
			__(
				'Your PHP Version: %s | Recommended version: 7.4 | Minimal required: 7.2',
				'rank-math'
			),
			phpVersion
		)
	}

	const phpVersionText = sprintf(
		// translators: php version
		__( 'Your PHP Version: %s', 'rank-math' ),
		phpVersion
	)

	const hasWhitelabelText = `${ __(
		'Rank Math is compatible with your PHP version but we recommend updating to PHP 7.4 for increased speed and security. ',
		'rank-math'
	) } <a href='${ getLink( 'requirements', 'Setup wizard compatibility step' ) }'>${ __( 'More information', 'rank-math' ) }</a>`

	const hasNoWhitelabelText = __(
		'This plugin is compatible with your PHP version but we recommend updating to PHP 7.4 for increased speed and security.',
		'rank-math'
	)

	const phpVersionRecommendText = phpVersionRecommend
		? `${ __( ' | Recommended: PHP 7.4 or later', 'rank-math' ) } <p class='description'>${ isWhitelabel ? hasWhitelabelText : hasNoWhitelabelText }</p>`
		: ''

	return `${ phpVersionText } ${ phpVersionRecommendText }`
}
