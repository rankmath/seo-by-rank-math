/**
 * ConfirmModal — generic confirmation dialog for destructive actions.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Modal } from '@wordpress/components'

/**
 * Internal dependencies
 */
import Button from '../components/Button'
import LoadingButton from '../components/LoadingButton'
import './ConfirmModal.scss'

/**
 * ConfirmModal component.
 *
 * @param {Object}      props
 * @param {string}      props.title                 Dialog heading (passed to WP Modal).
 * @param {string}      props.message               Body copy — explains the action.
 * @param {string}      [props.confirmLabel]        Confirm button label. Default "Confirm".
 * @param {string}      [props.cancelLabel]         Cancel button label. Default "Cancel".
 * @param {boolean}     [props.isDestructive=false] Renders confirm button in red.
 * @param {boolean}     [props.isProcessing=false]  Disables + spins both buttons.
 * @param {string|null} [props.error=null]          Error message shown inside the modal on failure.
 * @param {Function}    [props.onConfirm]           Handler for Confirm click.
 * @param {Function}    [props.onCancel]            Handler for Cancel / close.
 * @return {JSX.Element} Confirmation dialog with Cancel and Confirm buttons.
 */
const ConfirmModal = ( {
	title,
	message,
	confirmLabel = __( 'Confirm', 'seo-by-rank-math' ),
	cancelLabel = __( 'Cancel', 'seo-by-rank-math' ),
	isDestructive = false,
	isProcessing = false,
	error = null,
	onConfirm,
	onCancel,
} ) => {
	const ns = 'rank-math-ai-visibility-confirm-modal'
	return (
		<Modal
			title={ title }
			onRequestClose={ onCancel }
			className={ ns }
		>
			<p className={ `${ ns }__message` }>
				{ message }
			</p>

			{ error && (
				<p className={ `${ ns }__error` }>
					{ error }
				</p>
			) }

			<div className={ `${ ns }__footer` }>
				<Button
					variant="secondary"
					onClick={ onCancel }
					disabled={ isProcessing }
				>
					{ cancelLabel }
				</Button>

				<LoadingButton
					variant="primary"
					isDestructive={ isDestructive }
					onClick={ onConfirm }
					isLoading={ isProcessing }
					loadingLabel={ `${ confirmLabel }…` }
				>
					{ confirmLabel }
				</LoadingButton>
			</div>
		</Modal>
	)
}

ConfirmModal.displayName = 'ConfirmModal'

export default ConfirmModal
