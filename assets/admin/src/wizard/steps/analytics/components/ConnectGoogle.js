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
import getLink from '@helpers/getLink'
import { Button, PrivacyBox } from '@rank-math/components'

export default ( data ) => {
	const { authUrl, isAuthorized } = data

	const benefits = [
		__(
			'Verify site ownership on Google Search Console in a single click',
			'rank-math'
		),
		__(
			'Track page and keyword rankings with the Advanced Analytics module',
			'rank-math'
		),
		__(
			'Easily set up Google Analytics without using another 3rd party plugin',
			'rank-math'
		),
		__(
			'Automatically submit sitemaps to the Google Search Console',
			'rank-math'
		),
		<a
			key="help-analytics"
			target="_blank"
			rel="noreferrer"
			href={ getLink( 'help-analytics', 'SW Analytics Step Benefits' ) }
		>
			{ __(
				'Learn more about the benefits of connecting your account here.',
				'rank-math'
			) }
		</a>,
	]

	return (
		<>
			<div className="connect-wrap">
				{ ! isAuthorized ? (
					<Button
						href={ authUrl }
						variant="animate"
						className="rank-math-authorize-account"
					>
						{ __( 'Connect Google Services', 'rank-math' ) }
					</Button>
				) : (
					<Button variant="primary">
						{ __( 'Disconnect Account', 'rank-math' ) }
					</Button>
				) }
			</div>

			<div id="rank-math-pro-cta" className="analytics">
				<div className="rank-math-cta-box width-100 no-shadow no-padding no-border">
					<h3>{ __( 'Benefits of Connecting Google Account', 'rank-math' ) }</h3>

					<ul>
						{ map( benefits, ( item, index ) => (
							<li key={ index }>{ item }</li>
						) ) }
					</ul>
				</div>
			</div>

			<PrivacyBox className={ rankMath.isSettingsPage ? 'width-100' : '' } />
		</>
	)
}
