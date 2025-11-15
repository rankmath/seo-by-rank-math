/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal Dependencies
 */
import getLink from '@helpers/getLink'

export default () => {
	if ( rankMath.isPro ) {
		return
	}

	return (
		<div
			className="rank-math-unlock-pro-notice"
			id="rank-math-unlock-pro-notice"
		>
			<a
				href={ getLink( 'pro', 'Unlock PRO Options Panel Notice' ) }
				target="_blank"
				className="pro-link"
				rel="noreferrer"
			>
				<p>
					{ __( 'Take your SEO to the Next Level! ', 'rank-math' ) }
					<strong>{ __( 'Get Rank Math PRO! ', 'rank-math' ) }</strong>
					<span>
						{ __(
							'Click here to see all the exciting features.',
							'rank-math'
						) }
					</span>
				</p>
				<div className="close-notice">
					<span className="dashicons dashicons-dismiss"></span>
				</div>
			</a>
		</div>
	)
}
