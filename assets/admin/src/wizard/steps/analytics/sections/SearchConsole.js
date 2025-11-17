/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import { ToggleControl, SelectWithSearch } from '@rank-math/components'
import getLink from '@helpers/getLink'

export default ( { data, updateData } ) => {
	const { searchConsole } = data
	return (
		<>
			<div className="field-row field-type-select">
				<div className="field-row-col">
					<SelectWithSearch
						value={ searchConsole.profile }
						width={ 268 }
						options={ searchConsole.sites ?? {} }
						onChange={ ( selectedProfile ) => {
							searchConsole.profile = selectedProfile
							updateData( 'searchConsole', searchConsole )
						} }
						label={ __( 'Site', 'rank-math' ) }
						className="site-console-profile notrack"
						disabled={ rankMath.isConsoleConnected }
					/>
				</div>

				{ applyFilters( 'rank_math_analytics_options_console', '', data, updateData ) }
			</div>

			<div className="field-row field-type-toggle">
				<div className="field-td">
					<ToggleControl
						checked={ searchConsole.enable_index_status }
						disabled={ ! searchConsole.profile }
						onChange={ ( isChecked ) => {
							searchConsole.enable_index_status = isChecked
							updateData( 'searchConsole', searchConsole )
						} }
						className="regular-text notrack"
						label={ __( 'Enable the Index Status tab', 'rank-math' ) }
					/>

					<div className="field-description">
						{ __(
							'Enable this option to show the Index Status tab in the Analytics module. ',
							'rank-math'
						) }

						<a
							target="_blank"
							rel="noreferrer"
							href={ getLink(
								'url-inspection-api',
								'SW Analytics Index Status Option'
							) }
						>
							{ __( 'Learn more.', 'rank-math' ) }
						</a>
					</div>
				</div>
			</div>
		</>
	)
}
