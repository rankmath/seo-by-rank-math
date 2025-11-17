/**
 * External dependencies
 */
import { map } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { Button } from '@rank-math/components'
import getLink from '@helpers/getLink'
import getAdminURL from './getAdminURL'

const learnMoreLinks = [
	! rankMath.isPro
		? {
			icon: 'star-filled pro',
			href: getLink( 'pro', 'SW Ready Step Upgrade' ),
			text: (
				<strong className="pro-label">
					{ __( 'Know more about the PRO version', 'rank-math' ) }
				</strong>
			),
		}
		: {
			icon: 'video-alt3',
			href: getLink( 'yt-link', 'SW Ready Step Upgrade' ),
			text: __( 'Subscribe to Our YouTube Channel', 'rank-math' ),
		},
	{
		icon: 'facebook',
		href: getLink( 'fb-group', 'SW Ready Step Upgrade' ),
		text: __( 'Join FREE Facebook Group', 'rank-math' ),
	},
	{
		icon: 'welcome-learn-more',
		href: getLink( 'kb-seo-suite', 'SW Ready Step KB' ),
		text: __( 'Rank Math Knowledge Base', 'rank-math' ),
	},
	{
		icon: 'sos',
		href: getLink( 'support', 'SW Ready Step Support' ),
		text: __( 'Get 24x7 Support', 'rank-math' ),
	},
]

/**
 * Render learn more links and advanced option action.
 *
 * @param {Object}   props
 * @param {Object}   props.data
 * @param {Function} props.skipStep
 */
export default ( { data, skipStep } ) => {
	const { scoreImg, dashboardUrl, isWhitelabel, setup_mode: setupMode } = data
	const isAdvanced = setupMode === 'advanced'
	if ( isWhitelabel ) {
		return (
			<>
				<p>{ __( 'Your site is now optimized', 'rank-math' ) }</p>
				<footer className="form-footer wp-core-ui rank-math-ui">
					<Button href={ getAdminURL( 'options-general' ) } variant="primary">
						{ __( 'Proceed to Settings', 'rank-math' ) }
					</Button>
				</footer>
			</>
		)
	}

	return (
		<>
			<div className="wizard-next-steps wp-clearfix">
				<div className="score-100">
					<a
						target="_blank"
						rel="noreferrer"
						href={ getLink( 'score-100', 'SW Ready Score Image' ) }
					>
						<img src={ scoreImg } alt={ __( 'Score 100', 'rank-math' ) } />
					</a>
				</div>

				<div className="learn-more">
					<h2>{ __( 'Learn more', 'rank-math' ) }</h2>

					<ul>
						{ map( learnMoreLinks, ( { icon, href, text } ) => (
							<li key={ text }>
								<span className={ `dashicons dashicons-${ icon }` } />
								<a href={ href } target="_blank" rel="noreferrer">
									{ text }
								</a>
							</li>
						) ) }
					</ul>
				</div>
			</div>

			<footer className="form-footer wp-core-ui rank-math-ui">
				<Button
					variant={ isAdvanced ? 'secondary' : 'primary' }
					className={ isAdvanced ? 'rank-math-return-dashboard' : 'rank-math-advanced-option' }
					href={ dashboardUrl }
				>
					{ __( 'Return to dashboard', 'rank-math' ) }
				</Button>

				<Button
					variant="secondary"
					href={ getAdminURL( '', { view: 'help' } ) }
				>
					{ __( 'Proceed to Help Page', 'rank-math', 'rank-math' ) }
				</Button>

				{
					isAdvanced &&
					<Button
						variant="primary"
						className="rank-math-advanced-option"
						onClick={ skipStep }
					>
						{ __( 'Setup Advanced Options', 'rank-math' ) }
					</Button>
				}
			</footer>
		</>
	)
}
