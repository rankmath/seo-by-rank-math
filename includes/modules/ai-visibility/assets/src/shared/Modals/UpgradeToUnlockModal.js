/**
 * UpgradeToUnlockModal — access gate for free plugin users on the Free
 * Content AI plan. The CTA links to the Content AI purchase page; the
 * module unlocks automatically on reload after purchase.
 *
 * @since 1.0.281
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Icon } from '@wordpress/components'
import { arrowRight } from '@wordpress/icons'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'
import Button from '../components/Button'
import AccessGateModal from './AccessGateModal'
import FeatureChecklist from './FeatureChecklist'

/**
 * UpgradeToUnlockModal component.
 *
 * @return {JSX.Element} Non-dismissible upgrade gate modal.
 */
const UpgradeToUnlockModal = () => {
	const ns = 'rank-math-ai-visibility-gate'

	return (
		<AccessGateModal
			icon="dashicons dashicons-lock"
			title={ __( 'Upgrade to Unlock AI Visibility', 'seo-by-rank-math' ) }
			className="rank-math-ai-visibility-unlock-modal"
		>
			<p className={ `${ ns }__intro` }>
				<strong>
					{ __( 'Your competitors are already tracking their AI presence. You should be too.', 'seo-by-rank-math' ) }
				</strong>
				{ ' ' }
				{ __( 'Upgrade to unlock AI Visibility module and see where your brand appears across ChatGPT, and other AI platforms, and take action before you fall further behind.', 'seo-by-rank-math' ) }
			</p>

			<FeatureChecklist />

			<div className={ `${ ns }__actions` }>
				<Button
					variant="primary"
					className={ `${ ns }__cta` }
					href={ getLink( 'content-ai-pricing-tables', 'AI Visibility Upgrade Modal' ) }
					target="_blank"
					rel="noreferrer"
					iconRight={ <Icon icon={ arrowRight } size={ 18 } /> }
				>
					{ __( 'Start 15-Day Free Trial', 'seo-by-rank-math' ) }
				</Button>
			</div>

			<p className={ `${ ns }__footnote` }>
				{ __( '30-Day Money-Back Guarantee', 'seo-by-rank-math' ) }
			</p>
		</AccessGateModal>
	)
}

UpgradeToUnlockModal.displayName = 'UpgradeToUnlockModal'

export default UpgradeToUnlockModal
