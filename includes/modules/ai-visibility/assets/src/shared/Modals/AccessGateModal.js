/**
 * AccessGateModal — shared gradient-header shell for the access-gate modals
 * (Free Users, Paid Users trial, trial success). Non-dismissible by default.
 *
 * @since 1.0.281
 */

/**
 * External dependencies
 */
import { compact, join } from 'lodash'

/**
 * WordPress dependencies
 */
import { Modal } from '@wordpress/components'

/**
 * Internal dependencies
 */
import './AccessGateModal.scss'
import './ContentOverlay.scss'

const noop = () => {}

/**
 * AccessGateModal component.
 *
 * @param {Object}   props
 * @param {string}   [props.icon]                Header icon class (e.g. 'dashicons dashicons-lock').
 * @param {string}   props.title                 Header title.
 * @param {string}   [props.className]           Extra class for per-modal overrides.
 * @param {boolean}  [props.isDismissible=false] Allow closing via ESC / outside click.
 * @param {Function} [props.onRequestClose]      Close handler. Defaults to a no-op.
 * @param {*}        props.children              Body content.
 * @return {JSX.Element} Modal with gradient header.
 */
const AccessGateModal = ( {
	icon = '',
	title,
	className = '',
	isDismissible = false,
	onRequestClose = noop,
	children,
} ) => {
	const ns = 'rank-math-ai-visibility-access-gate-modal'

	return (
		<Modal
			onRequestClose={ onRequestClose }
			className={ join( compact( [ ns, className ] ), ' ' ) }
			overlayClassName="rank-math-modal-overlay rank-math-ai-visibility-content-overlay"
			isDismissible={ isDismissible }
			shouldCloseOnEsc={ isDismissible }
			shouldCloseOnClickOutside={ isDismissible }
			aria={ { labelledby: `${ ns }-title` } }
			__experimentalHideHeader
		>
			<div className={ `${ ns }__header` }>
				{ icon && (
					<i className={ `${ ns }__header-icon ${ icon }` } aria-hidden="true" />
				) }
				<h1 id={ `${ ns }-title` } className={ `${ ns }__title` }>
					{ title }
				</h1>
			</div>

			<div className={ `${ ns }__body` }>
				{ children }
			</div>
		</Modal>
	)
}

AccessGateModal.displayName = 'AccessGateModal'

export default AccessGateModal
