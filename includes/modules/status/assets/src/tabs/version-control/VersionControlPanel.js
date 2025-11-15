/**
 * External Dependencies
 */
import { reduce, isEmpty } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState } from '@wordpress/element'

/**
 * Internal Dependencies
 */
import { Button, SelectControl, TextControl } from '@rank-math/components'
import Header from './Header'

/**
 * The Version Control View.
 */
export default ( { data } ) => {
	const {
		latestVersion,
		isRollbackVersion,
		isPluginUpdateDisabled,
		availableVersions,
		updateCoreUrl,
		rollbackNonce,
	} = data

	if ( isEmpty( availableVersions ) ) {
		return ''
	}

	const currentVersion = rankMath.version

	const [ selectedVersion, setVersion ] = useState( availableVersions[ 1 ] )
	const [ loading, setLoading ] = useState( false )

	const rollbackVersions = reduce(
		availableVersions,
		( acc, version ) => {
			acc[ version ] = version

			return acc
		},
		{}
	)

	return (
		<form className="rank-math-rollback-form field-form rank-math-box" method="post" action="">
			<Header
				title={ __( 'Rollback to Previous Version', 'rank-math' ) }
				description={ __( 'If you are facing issues after an update, you can reinstall a previous version with this tool.', 'rank-math' ) }
				warning={ __( 'Previous versions may not be secure or stable. Proceed with caution and always create a backup.', 'rank-math' ) }
			/>

			<table className="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label htmlFor="your-verions">
								{ __( 'Your Version', 'rank-math' ) }
							</label>
						</th>
						<td>
							<strong>{ currentVersion }</strong>

							{ isRollbackVersion && (
								<>
									<br />
									<span className="rollback-version-label">
										{ __( 'Rolled Back Version: ', 'rank-math' ) }
									</span>
									{ __(
										'Auto updates will not work, please update the plugin manually.',
										'rank-math'
									) }
								</>
							) }

							{ currentVersion === latestVersion ? (
								<p className="description">
									{ __(
										'You are using the latest version of the plugin.',
										'rank-math'
									) }
								</p>
							) : (
								<p className="description">
									{ __(
										'This is the version you are using on this site.',
										'rank-math'
									) }
								</p>
							) }
						</td>
					</tr>

					{ currentVersion !== latestVersion && (
						<tr>
							<th scope="row">
								<label htmlFor="latest-stable">
									{ __( 'Latest Stable Version', 'rank-math' ) }
								</label>
							</th>
							<td>
								<strong>{ latestVersion }</strong>
								{ isPluginUpdateDisabled && currentVersion < latestVersion && (
									<a href={ updateCoreUrl } className="update-link">
										{ __( 'Update Now', 'rank-math' ) }
									</a>
								) }
								<p className="description">
									{ __( 'This is the latest version of the plugin.', 'rank-math' ) }
								</p>
							</td>
						</tr>
					) }

					<tr>
						<th scope="row">
							<label htmlFor="rollback_version">
								{ __( 'Rollback Version', 'rank-math' ) }
							</label>
						</th>

						<td>
							<SelectControl
								variant="default"
								id="rm_rollback_version"
								name="rm_rollback_version"
								value={ selectedVersion }
								options={ rollbackVersions }
								disabledOptions={ [ currentVersion ] }
								onChange={ ( newVersion ) => ( setVersion( newVersion ) ) }
							/>

							<p className="description">
								{ __( 'Roll back to this version.', 'rank-math' ) }
							</p>
						</td>
					</tr>
				</tbody>
			</table>

			<footer>
				<TextControl
					type="hidden"
					name="_wpnonce"
					value={ rollbackNonce }
				/>
				<Button
					type="submit"
					variant="primary"
					size="xlarge"
					id="rm-rollback-button"
					onClick={ () => ( setLoading( true ) ) }
				>
					{ __( 'Install Version ', 'rank-math' ) }
					{ selectedVersion }
				</Button>

				{
					loading &&
					<div className="alignright rollback-loading-indicator">
						<span className="loading-indicator-text">
							{ __( 'Reinstalling, please wait…', 'rank-math' ) }
						</span>
						<span className="spinner is-active" />
					</div>
				}
			</footer>
		</form>
	)
}
