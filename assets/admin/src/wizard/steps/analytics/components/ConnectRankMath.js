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
import {
	Button,
	PrivacyBox,
	InvalidSiteUrlNotice,
} from '@rank-math/components'
import getLink from '@helpers/getLink'

export default ( data ) => {
	const { isSiteUrlValid, activateUrl } = data

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
		__( 'Free keyword suggestions when entering a focus keyword', 'rank-math' ),
		__(
			'Use our revolutionary SEO Analyzer to scan your website for SEO errors',
			'rank-math'
		),
		<a
			key="learn-more"
			target="_blank"
			rel="noreferrer"
			href={ getLink( 'free-account-benefits', 'SW Analytics Step' ) }
		>
			{ __(
				'Learn more about the benefits of connecting your account here.',
				'rank-math'
			) }
		</a>,
	]

	return (
		<>
			<InvalidSiteUrlNotice isSiteUrlValid={ isSiteUrlValid } />

			<div className="wp-core-ui rank-math-ui connect-wrap">
				<Button href={ activateUrl } variant="animate" disabled={ ! isSiteUrlValid }>
					{ __( 'Connect Your Rank Math Account', 'rank-math' ) }
				</Button>
			</div>

			<div id="rank-math-pro-cta" className="analytics">
				<div className="rank-math-cta-box width-100 no-shadow no-padding no-border">
					<h3>{ __( 'Benefits of Connecting Rank Math Account', 'rank-math' ) }</h3>

					<ul>
						{
							map( benefits, ( item, index ) => (
								<li key={ index }>{ item }</li>
							) )
						}
					</ul>
				</div>
			</div>

			<PrivacyBox />
		</>
	)
}
