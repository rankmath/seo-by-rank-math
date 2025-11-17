/* global confirm, alert */

/**
 * External Dependencies
 */
import jQuery from 'jquery'
import { map, isEmpty } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import apiFetch from '@wordpress/api-fetch'

/**
 * Internal Dependencies
 */
import { Button } from '@rank-math/components'
import addNotice from '@helpers/addNotice'

export default ( { data, updateViewData } ) => {
	const backups = data.backups
	const runBackup = ( action, key = null ) => {
		apiFetch( {
			method: 'POST',
			path: '/rankmath/v1/status/runBackup',
			data: {
				action,
				key,
			},
		} )
			.catch( ( error ) => {
				// eslint-disable-next-line no-alert
				alert( error.message )
			} )
			.then( ( response ) => {
				const noticeLocation = jQuery( '.wp-header-end' )
				addNotice( response.message, response.type, noticeLocation )

				if ( response.backups !== false ) {
					updateViewData( { backups: response.backups } )
				}
			} )
	}
	return (
		<div className="settings-backup">
			<Button
				variant="primary"
				className="alignright"
				onClick={ () => ( runBackup( 'create' ) ) }
			>
				{ __( 'Create Backup', 'rank-math' ) }
			</Button>

			<h3>{ __( 'Settings Backup', 'rank-math' ) }</h3>

			<p className="description">
				{ __(
					'Take a backup of your plugin settings in case you wish to restore them in future. Use it as backup before making substantial changes to Rank Math settings. For taking a backup of the SEO data of your content, use the XML Export option.',
					'rank-math'
				) }
			</p>

			<div className="rank-math-settings-backup-form field-form">
				<div className="list-table with-action">
					<table className="form-table">
						<tbody>
							{ ! isEmpty( backups ) &&
								map( backups, ( backup, index ) => (
									<tr key={ index }>
										<th>
											{ sprintf(
												// translators: Snapshot formatted date
												__( 'Backup: %s', 'rank-math' ),
												backup
											) }
										</th>
										<td>
											<Button
												size="small"
												variant="secondary"
												onClick={ () => {
													// eslint-disable-next-line no-alert
													if ( ! confirm( __( 'Are you sure you want to restore this backup? Your current configuration will be overwritten.', 'rank-math' ) ) ) {
														return
													}

													runBackup( 'restore', index )
												} }
											>
												{ __( 'Restore', 'rank-math' ) }
											</Button>
											<Button
												size="small"
												isDestructive
												onClick={ () => {
													// eslint-disable-next-line no-alert
													if ( ! confirm( __( 'Are you sure you want to delete this backup?', 'rank-math' ) ) ) {
														return
													}

													runBackup( 'delete', index )
												} }
											>
												{ __( 'Delete', 'rank-math' ) }
											</Button>
										</td>
									</tr>
								) ) }
						</tbody>
					</table>
				</div>

				{
					isEmpty( backups ) &&
					<p id="rank-math-no-backup-message">
						{ __( 'There is no backup.', 'rank-math' ) }
					</p>
				}
			</div>
		</div>
	)
}
