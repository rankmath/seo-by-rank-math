/**
 * UpgradePlanModal — shown when adding a brand would exceed the plan's
 * limit. Body hosts the Content AI checkout iframe by default; pass
 * `children` to override it.
 *
 * @since 1.0.281
 */

/**
 * External dependencies
 */
import { upperFirst } from 'lodash'

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { Modal, Icon } from '@wordpress/components'
import { close } from '@wordpress/icons'

/**
 * Internal dependencies
 */
import Button from '../components/Button'
import CheckoutIframe from './CheckoutIframe'
import './UpgradePlanModal.scss'

/**
 * UpgradePlanModal component.
 *
 * @param {Object}   props
 * @param {string}   props.plan              Current Content AI plan slug.
 * @param {boolean}  [props.isTopTier=false] Highest plan — show limit-reached copy.
 * @param {Function} props.onClose           Close handler.
 * @param {*}        [props.children]        Body override. Defaults to the checkout iframe.
 * @return {JSX.Element} Upgrade Plan modal.
 */
const UpgradePlanModal = ( { plan = '', isTopTier = false, onClose, children = null } ) => {
	const ns = 'rank-math-ai-visibility-upgrade-plan-modal'

	const subtitle = isTopTier
		? __( 'You\'ve reached the maximum number of brands available on your plan. Contact support if you need a higher limit.', 'seo-by-rank-math' )
		: sprintf(
			/* translators: %s: current plan name, e.g. "Starter". */
			__( 'You\'ve reached the brand limit for your current %s plan.', 'seo-by-rank-math' ),
			upperFirst( plan )
		)

	return (
		<Modal
			onRequestClose={ onClose }
			className={ ns }
			overlayClassName="rank-math-modal-overlay rank-math-ai-visibility-content-overlay"
			aria={ { labelledby: `${ ns }-title` } }
			__experimentalHideHeader
		>
			<div className={ `${ ns }__header` }>
				<i className={ `${ ns }__header-icon dashicons dashicons-warning` } aria-hidden="true" />

				<div className={ `${ ns }__header-text` }>
					<h1 id={ `${ ns }-title` } className={ `${ ns }__title` }>
						{ __( 'Upgrade Your Plan', 'seo-by-rank-math' ) }
					</h1>
					<p className={ `${ ns }__subtitle` }>
						{ subtitle }
					</p>
				</div>

				<Button
					variant=""
					onClick={ onClose }
					className={ `${ ns }__close` }
					label={ __( 'Close', 'seo-by-rank-math' ) }
				>
					<Icon icon={ close } size={ 20 } />
				</Button>
			</div>

			<div className={ `${ ns }__body` }>
				{ children || <CheckoutIframe /> }
			</div>

			<div className={ `${ ns }__footer` }>
				<p className={ `${ ns }__guarantee` }>
					{ __( 'All plans include a 30-day money-back guarantee.', 'seo-by-rank-math' ) }
				</p>

				<Button
					variant="link"
					onClick={ onClose }
					className={ `${ ns }__later` }
				>
					{ __( 'I\'ll do this later', 'seo-by-rank-math' ) }
				</Button>
			</div>
		</Modal>
	)
}

UpgradePlanModal.displayName = 'UpgradePlanModal'

export default UpgradePlanModal
