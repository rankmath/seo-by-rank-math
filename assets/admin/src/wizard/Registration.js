/**
 * External dependencies
 */
import classNames from 'classnames'

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { RawHTML, useState } from '@wordpress/element'
import apiFetch from '@wordpress/api-fetch'

/**
 * Internal dependencies
 */
import { TextControl, Button, InvalidSiteUrlNotice, ToggleControl } from '@rank-math/components'
import getLink from '@helpers/getLink'
import Header from './components/Header'

export default () => {
	const { adminUrl, registerNonce, isSiteUrlValid } = rankMath
	const [ action, setAction ] = useState( 'rank_math_save_registration' )
	const [ enableTracking, setEnableTracking ] = useState( false )
	const [ isUpdatingTracking, setIsUpdatingTracking ] = useState( false )

	const buttonClassName = classNames(
		'button button-primary button-connect',
		{
			'button-animated': isSiteUrlValid,
			disabled: ! isSiteUrlValid,
		}
	)

	const handleTrackingChange = async ( newValue ) => {
		setEnableTracking( newValue )
		setIsUpdatingTracking( true )

		try {
			await apiFetch( {
				method: 'POST',
				path: '/rankmath/v1/setupWizard/updateTrackingOptin',
				data: {
					enable_tracking: newValue ? 'on' : 'off',
				},
			} )
		} catch ( error ) {
			console.error( 'Failed to update tracking opt-in:', error )
		} finally {
			setIsUpdatingTracking( false )
		}
	}

	return (
		<div className="wrapper">
			<form method="post" action={ adminUrl }>
				<TextControl
					type="hidden"
					name="action"
					value={ action }
				/>
				<TextControl
					type="hidden"
					name="step"
					value="register"
				/>
				<TextControl
					type="hidden"
					name="security"
					value={ registerNonce }
				/>
				<div className="main-content wizard-content--register">
					<Header
						heading={ __( 'Connect FREE Account', 'rank-math' ) }
						description={ __( 'By connecting your free account, you get keyword suggestions directly from Google when entering the focus keywords. Not only that, get access to our revolutionary Content AI, SEO Analyzer inside WordPress that scans your website for SEO errors and suggest improvements.', 'rank-math' ) }
						link={ getLink( 'free-account-benefits', 'SW Connect Free Account' ) }
						linkText={ __( 'Read more by following this link.', 'rank-math' ) }
						className="rank-math-gray-box"
					/>
					<InvalidSiteUrlNotice isSiteUrlValid={ isSiteUrlValid } />
					<div className="rank-math-tracking-optin">
						<ToggleControl
							label={
								<RawHTML>
									{
										sprintf(
											// Translators: %s is the KB link to Usage tracking article.
											__(
												'Enable Usage Tracking. %1$s Share anonymous usage data to help us improve Rank Math. No personal info is collected. %2$s',
												'rank-math'
											),
											'<br />',
											'<a href="' + getLink( 'usage-policy', 'SW Connect Free Account' ) + '" target="_blank">' + __( 'Learn more about what data is and isn\'t tracked.', 'rank-math' ) + '</a>',
										)
									}
								</RawHTML>
							}
							checked={ enableTracking }
							onChange={ handleTrackingChange }
							disabled={ isUpdatingTracking }
						/>
					</div>
					<div className="text-center wp-core-ui rank-math-ui" style={ { marginTop: '30px' } }>
						<button
							type="submit"
							className={ buttonClassName }
							name="rank_math_activate"
						>
							{ __( 'Connect Your Account', 'rank-math' ) }
						</button>
					</div>
				</div>
				<footer className="form-footer wp-core-ui rank-math-ui">
					<Button
						variant="secondary"
						className="button-skip"
						type="submit"
						onClick={ () => {
							setAction( 'rank_math_skip_wizard' )
						} }
					>
						{ __( 'Skip Step', 'rank-math' ) }
					</Button>
				</footer>
			</form>
		</div>
	)
}
