/**
 * WordPress Dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { RawHTML, useState } from '@wordpress/element'
import apiFetch from '@wordpress/api-fetch'

/**
 * Internal Dependencies
 */
import { TextControl, Button } from '@rank-math/components'

export default () => {
	const [ apiKey, setApiKey ] = useState( rankMath.data.indexnow_api_key ) // initially fetch pre-existing APIKEY
	const apiKeyUrl = window.location.origin + '/' + apiKey + '.txt'

	const handleResetAPIKey = () => {
		apiFetch( {
			method: 'POST',
			path: '/rankmath/v1/in/resetKey',
		} )
			.then( ( response ) => {
				setApiKey( response.key )
			} )
	}

	return (
		<>
			<div className="field-row field-id-indexnow_api_key_location">
				<div className="field-th">
					<label htmlFor="indexnow_api_key_location">
						{ __( 'API Key', 'rank-math' ) }
					</label>
				</div>
				<div className="field-td">
					<TextControl value={ apiKey } onChange={ setApiKey } readOnly />

					<RawHTML className="field-description">
						{ __(
							'The IndexNow API key proves the ownership of the site. It is generated automatically. You can change the key if it becomes known to third parties.',
							'rank-math'
						) }
					</RawHTML>

					<Button
						onClick={ handleResetAPIKey }
						id="indexnow_reset_key"
						className="button button-secondary large-button"
					>
						<span className="dashicons dashicons-update"></span>
						{ __( 'Change Key', 'rank-math' ) }
					</Button>
				</div>
			</div>

			<div className="field-row field-id-indexnow-api-key-location rank-math-advanced-option">
				<div className="field-th">
					<label htmlFor="indexnow_api_key_location">
						{ __( 'API Key Location', 'rank-math' ) }
					</label>
				</div>
				<div className="field-td">
					<code id="indexnow_api_key_location">{ apiKeyUrl }</code>

					<RawHTML className="field-description">
						{ sprintf(
							// Translators: %s is the words "Check Key".
							__(
								'Use the %1$s button to verify that the key is accessible for search engines. Clicking on it should open the key file in your browser and show the API key.',
								'rank-math'
							),
							'<strong>' + __( 'Check Key', 'rank-math' ) + '</strong>'
						) }
					</RawHTML>

					<Button
						href={ apiKeyUrl }
						id="indexnow_check_key"
						className="button button-secondary large-button"
						target="_blank"
						rel="noreferrer"
					>
						<span className="dashicons dashicons-search"></span>
						{ __( 'Check Key', 'rank-math' ) }
					</Button>
				</div>
			</div>
		</>
	)
}
