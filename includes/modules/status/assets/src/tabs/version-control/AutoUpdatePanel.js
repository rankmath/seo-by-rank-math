/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal Dependencies
 */
import { ToggleControl, Notice } from '@rank-math/components'
import Footer from './Footer'
import Header from './Header'

/**
 * Auto Updater view in Version Control Tab.
 *
 * @param {Object}   props                Component props.
 * @param {Object}   props.data           Settings data.
 * @param {Function} props.updateViewData Function to update setting value.
 */
export default ( { data, updateViewData } ) => {
	const { autoUpdate, updateNotificationEmail, isPluginUpdateDisabled, rollbackVersion } = data

	return (
		<div className="rank-math-auto-update-form field-form rank-math-box">
			<Header
				title={ __( 'Auto Update', 'rank-math' ) }
				description={
					isPluginUpdateDisabled
						? __( 'You cannot turn on auto-updates to automatically update to stable versions of Rank Math as soon as they are released, because site wide plugins auto-update option is disabled on your site.', 'rank-math' )
						: __( 'Turn on auto-updates to automatically update to stable versions of Rank Math as soon as they are released. The beta versions of Rank Math as soon as they are released. The beta versions will never install automatically.', 'rank-math' )
				}
			/>
			{
				! isPluginUpdateDisabled &&
				<table className="form-table">
					<tbody>
						<tr className="field-row field-type-switch">
							<th scope="row">
								<label htmlFor="enable_auto_update">
									{ __( 'Auto Update Plugin', 'rank-math' ) }
								</label>
							</th>

							<td>
								<ToggleControl
									id="enable_auto_update"
									checked={ autoUpdate }
									onChange={ ( value ) => {
										data.autoUpdate = value
										updateViewData( data )
									} }
								/>
							</td>
						</tr>
					</tbody>
				</table>
			}

			<div id="control_update_notification_email">
				<p>
					{ __(
						'When auto-updates are turned off, you can enable update notifications, to send an email to the site administrator when an update is available for Rank Math.',
						'rank-math'
					) }
				</p>

				<table className="form-table">
					<tbody>
						<tr className="field-row field-type-switch">
							<th scope="row">
								<label htmlFor="enable_update_notification_email">
									{ __( 'Update Notification Email', 'rank-math' ) }
								</label>
							</th>

							<td>
								<ToggleControl
									id="enable_update_notification_email"
									checked={ updateNotificationEmail }
									onChange={ ( value ) => {
										data.updateNotificationEmail = value
										updateViewData( data )
									} }
								/>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			{ ! isPluginUpdateDisabled && rollbackVersion && (
				<Notice variant="alt" status="warning">
					<p>
						{ __(
							'Rank Math will not auto-update because you have rolled back to a previous version. Update to the latest version manually to make this option work again.',
							'rank-math'
						) }
					</p>
				</Notice>
			) }

			<Footer panel="auto_update" { ...data } />
		</div>
	)
}
