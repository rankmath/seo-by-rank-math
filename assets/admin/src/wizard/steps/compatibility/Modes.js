/**
 * External dependencies
 */
import { map } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { createElement } from '@wordpress/element'
import { applyFilters } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'

/**
 * Setup Wizard Modes.
 *
 * @param {Object}   props          Component props.
 * @param {string}   props.value    The current value selected.
 * @param {Function} props.onChange Callback invoked when an option is changed.
 */
export default ( { value, onChange } ) => {
	const modes = applyFilters(
		'rank_math_wizard_modes',
		[
			{
				mode: 'easy',
				title: __( 'Easy', 'rank-math' ),
				desc: __(
					'For websites where you only want to change the basics and let Rank Math do most of the heavy lifting. Most settings are set to default as per industry best practices. One just has to set it and forget it.',
					'rank-math'
				),
			},
			{
				mode: 'advanced',
				title: __( 'Advanced', 'rank-math' ),
				desc: __(
					'For the advanced users who want to control every SEO aspect of the website. You are offered options to change everything and have full control over the website’s SEO.',
					'rank-math'
				),
			},
			{
				mode: 'custom',
				title: __( 'Custom Mode', 'rank-math' ),
				desc: __(
					'Select this if you have a custom Rank Math settings file you want to use.',
					'rank-math'
				),
				isFree: true,
			},
		]
	)

	const handleRedirectToPricingPlans = ( event ) => {
		event.preventDefault()

		window.open( getLink( 'pro', 'Setup Wizard Custom Mode' ) )
	}

	return (
		<>
			<ul>
				{ map( modes, ( { mode, title, desc, children, isFree } ) => {
					const checked = mode === value

					const getProPlanProps = mode === 'custom' && isFree && {
						onClick: handleRedirectToPricingPlans,
						'aria-hidden': true,
					}

					return (
						<li key={ mode } { ...getProPlanProps }>
							<div className="metabox rank-math-radio-control components-radio-control">
								<input
									type="radio"
									name="setup_mode"
									id={ mode }
									value={ mode }
									checked={ checked }
									onChange={ () => onChange( mode ) }
									className="components-radio-control__input"
								/>
							</div>

							<label
								htmlFor={ mode }
								className={ checked ? 'is-checked' : '' }
							>
								<div className="rank-math-mode-title">{ title }</div>

								{ children ? (
									createElement( children, { checked, desc } )
								) : (
									<p>{ desc }</p>
								) }
							</label>
						</li>
					)
				} ) }
			</ul>

			<p>
				<strong className="note">{ __( 'Note', 'rank-math' ) }</strong>

				{ __( 'You can easily switch between modes at any point.', 'rank-math' ) }
			</p>
		</>
	)
}
