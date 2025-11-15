/* global confirm */
/**
 * External Dependencies
 */
import jQuery from 'jquery'

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'
import apiFetch from '@wordpress/api-fetch'
import { FormFileUpload } from '@wordpress/components'
import { useState } from '@wordpress/element'

/**
 * Internal Dependencies
 */
import { Button } from '@rank-math/components'
import addNotice from '@helpers/addNotice'

/**
 * Import Rank Math settings
 */
export default () => {
	const [ importFile, setImportFile ] = useState( false )
	const formData = new FormData()
	formData.append( 'import-me', importFile )
	return (
		<div
			id="rank-math-import-form"
			className="rank-math-export-form field-form"
		>
			<div>
				<label htmlFor="import-me">
					<strong>{ __( 'Settings File', 'rank-math' ) }</strong>
				</label>
			</div>

			<div>
				<FormFileUpload
					__next40pxDefaultSize
					accept=".json"
					onChange={ ( event ) => setImportFile( event.currentTarget.files[ 0 ] ) }
				>
					<span className="import-file-button">{ __( 'Choose File', 'rank-math' ) }</span>
					{ importFile && <span>{ importFile.name }</span> }
				</FormFileUpload>
				<br />
				<span className="validation-message">
					{ __( 'Please select a file to import.', 'rank-math' ) }
				</span>
			</div>

			<div className="description">
				{ __(
					'Import settings by locating settings file and clicking "Import settings".',
					'rank-math'
				) }
			</div>

			<footer>
				<Button
					variant="primary"
					disabled={ importFile === false }
					onClick={ () => {
						// eslint-disable-next-line no-alert
						if ( ! confirm( __( 'Are you sure you want to import settings into Rank Math? Don\'t worry, your current configuration will be saved as a backup.', 'rank-math' ) ) ) {
							return
						}

						apiFetch( {
							method: 'POST',
							headers: {},
							path: '/rankmath/v1/status/importSettings',
							body: formData,
						} )
							.catch( ( error ) => {
								alert( error.message )
							} )
							.then( ( response ) => {
								const noticelocation = jQuery( '.rank-math-breadcrumbs-wrap' )
								if ( response.error ) {
									addNotice( response.error, 'error', noticelocation )
								} else {
									addNotice( response.success, 'success', noticelocation )
								}

								setImportFile( false )
							} )
					} }
				>
					{ __( 'Import', 'rank-math' ) }
				</Button>
			</footer>
		</div>
	)
}
