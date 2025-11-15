/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal Dependencies
 */
import { ToggleControl } from '@rank-math/components'
import Footer from './Footer'
import Header from './Header'

/**
 * Beta Opt-in view in Version Control Tab.
 *
 * @param {Object}   props                Component props.
 * @param {Object}   props.data           Version Control data
 * @param {Function} props.updateViewData Function to update the data in Redux store.
 */
export default ( { data, updateViewData } ) => {
	const { betaOptin, isPluginUpdateDisabled } = data

	return (
		<div className="rank-math-beta-optin-form field-form rank-math-box">
			<Header
				title={ __( 'Beta Opt-in', 'rank-math' ) }
				description={
					isPluginUpdateDisabled
						? __( 'You cannot turn on the Beta Tester feature because site wide plugins auto-update option is disabled on your site.', 'rank-math' )
						: __( 'You can take part in shaping Rank Math by test-driving the newest features and letting us know what you think. Turn on the Beta Tester feature to get notified about new beta releases. The beta version will not install automatically and you always have the option to ignore it.', 'rank-math' )
				}
				warning={ isPluginUpdateDisabled ? '' : __( 'It is not recommended to use the beta version on live production sites.', 'rank-math' ) }
			/>
			{
				! isPluginUpdateDisabled &&
				<>
					<table className="form-table">
						<tbody>
							<tr className="field-row field-type-switch">
								<th scope="row">
									<label htmlFor="beta_tester">
										{ __( 'Beta Tester', 'rank-math' ) }
									</label>
								</th>

								<td>
									<ToggleControl
										id="beta_tester"
										checked={ betaOptin }
										onChange={ ( value ) => {
											data.betaOptin = value
											updateViewData( data )
										} }
									/>
								</td>
							</tr>
						</tbody>
					</table>

					<Footer panel="beta_optin" { ...data } />
				</>

			}
		</div>
	)
}
