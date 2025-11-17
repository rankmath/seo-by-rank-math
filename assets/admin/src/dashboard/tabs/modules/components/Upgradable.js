/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { Tooltip } from '@rank-math/components'
import getLink from '../../../../helpers/getLink'

export default ( { upgradeable, probadge } ) => {
	const isProActive = rankMath.isPro
	if ( upgradeable && ! isProActive ) {
		return (
			<Tooltip
				text={ __(
					'More powerful options are available in the PRO version.',
					'rank-math'
				) }
			>
				<span className="is-upgradeable">
					<a href={ getLink( 'pro', 'Content AI Module Upgradable Icon' ) } target="_blank" rel="noreferrer">
						<div>&#171;</div>
					</a>
				</span>
			</Tooltip>
		)
	}

	if ( upgradeable || ( probadge && isProActive ) ) {
		return (
			<Tooltip
				text={ __( 'PRO options are enabled.', 'rank-math' ) }
			>
				<span className="is-upgradeable">
					<div className="upgraded">&#171;</div>
				</span>
			</Tooltip>
		)
	}

	return null
}
