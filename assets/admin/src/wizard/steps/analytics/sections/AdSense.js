/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import { ProBadge, SelectControl } from '@rank-math/components'
import getLink from '@helpers/getLink'

export default ( { data, updateData } ) => {
	const isPro = rankMath.isPro

	return (
		<>
			<div className="field-row field-type-select">
				<div className="field-row-col">
					{
						applyFilters(
							'rank_math_analytics_adsense',
							<SelectControl
								value=""
								options={
									{
										'': __( 'Select Account', 'rank-math' ),
									}
								}
								label={ __( 'Account', 'rank-math' ) }
								className="site-adsense-account notrack"
								disabled={ true }
							/>,
							data,
							updateData
						)
					}
				</div>
			</div>

			{ ! isPro && (
				<div id="rank-math-pro-cta" className="no-margin">
					<div className="rank-math-cta-text">
						<ProBadge href={ getLink( 'pro', 'AdSense Toggle' ) } />
						{ __(
							"Google AdSense support is only available in Rank Math Pro's Advanced Analytics module.",
							'rank-math'
						) }
					</div>
				</div>
			) }
		</>
	)
}
