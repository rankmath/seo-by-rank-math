/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import OptimizeSite from './OptimizeSite'
import { ToggleControl, SocialShare } from '@rank-math/components'

export default ( { data, updateData, saveData, skipStep } ) => {
	const { isWhitelabel } = data
	return (
		<>
			<header>
				<h1>
					<i className="dashicons dashicons-yes"></i>{ ' ' }
					{ __( 'Your site is ready! ', 'rank-math' ) }
					<SocialShare isWhitelabel={ isWhitelabel } />
				</h1>
			</header>

			<div className="rank-math-additional-options">
				<div className="rank-math-auto-update-wrapper">
					<h3>{ __( 'Enable auto update of the plugin', 'rank-math' ) }</h3>
					<ToggleControl
						checked={ data.enable_auto_update }
						onChange={ ( isChecked ) => {
							updateData( 'enable_auto_update', isChecked )
							saveData()
						} }
					/>
				</div>
			</div>

			<br className="clear" />

			<OptimizeSite data={ data } skipStep={ skipStep } />
		</>
	)
}
