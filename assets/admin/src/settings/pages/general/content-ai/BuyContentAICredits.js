/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { RawHTML } from '@wordpress/element'
import { useState } from '@wordpress/element'

/**
 * Internal dependencies
 */
import ContentAICredits from '@components/ContentAICredits'
import createLink from '../../../helpers/createLink'

/**
 * Buy more Content AI Credits
 */
export default () => {
	const [ credits, setCredits ] = useState( rankMath.credits )
	const date = new Date( rankMath.refreshDate * 1000 )
	const contentAIRefreshDate = date.toLocaleDateString( 'en-CA' )
	const contentAIRefreshTime = date.toLocaleTimeString( 'en-US', {
		hour: '2-digit',
		minute: '2-digit',
		hour12: true,
	} ).toLowerCase()

	return (
		<div className="field-row buy-more-credits rank-math-exclude-from-search">
			<ContentAICredits
				classes="rank-math-button update-credit"
				callback={
					( response ) => ( setCredits( response ) )
				}
			/>

			<RawHTML>
				{ sprintf(
				// translators: 1. Credits left 2. Buy more credits link
					__(
						'%1$s credits left this month. Credits will renew on %2$s or you can upgrade to get more credits %3$s.',
						'rank-math'
					),
					`<strong>${ credits }</strong>`,
					contentAIRefreshDate + ' ' + contentAIRefreshTime,
					createLink(
						'content-ai-pricing-tables',
						'Buy CAI Credits Options Panel',
						__( 'here', 'rank-math' )
					)
				) }
			</RawHTML>
		</div>
	)
}
