/**
 * ActivateTrialModal — 15-day trial gate for PRO users on the Free plan.
 * Flow: idle / error → activating → success (logic in useTrialActivation).
 *
 * @since 1.0.281
 */

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { useState, useEffect } from '@wordpress/element'
import { Modal, Spinner, Icon } from '@wordpress/components'
import { arrowRight } from '@wordpress/icons'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'
import Button from '../components/Button'
import AccessGateModal from './AccessGateModal'
import FeatureChecklist from './FeatureChecklist'
import useTrialActivation, { ACTIVATION_STATUS } from '../hooks/useTrialActivation'
import './ActivateTrialModal.scss'

const noop = () => {}

/**
 * Cosmetic provisioning steps — advance on a timer, not backend progress.
 */
const ACTIVATION_STEPS = [
	__( 'Authenticating credentials…', 'seo-by-rank-math' ),
	__( 'Provisioning AI tracking workspace…', 'seo-by-rank-math' ),
	__( 'Connecting to AI search models…', 'seo-by-rank-math' ),
]

/** How often the cosmetic step list advances (ms). */
const STEP_ADVANCE_MS = 1200

/**
 * Trial length in days — keep in sync with the backend trial duration.
 */
const TRIAL_DAYS = 15

/**
 * @param {Object}      props
 * @param {string|null} props.error      Error message from a failed attempt.
 * @param {Function}    props.onActivate Start/Retry handler.
 * @return {JSX.Element} Trial pitch or error state.
 */
const TrialPitch = ( { error, onActivate } ) => {
	const ns = 'rank-math-ai-visibility-gate'

	const renewDate = new Date( Date.now() + ( TRIAL_DAYS * 24 * 60 * 60 * 1000 ) )
		.toLocaleDateString( undefined, { year: 'numeric', month: 'long', day: 'numeric' } )

	return (
		<AccessGateModal
			icon="dashicons dashicons-unlock"
			title={ __( 'Activate Your AI Visibility Trial', 'seo-by-rank-math' ) }
			className="rank-math-ai-visibility-trial-modal"
		>
			<p className={ `${ ns }__intro` }>
				{ sprintf(
					/* translators: %d: trial length in days. */
					__( 'Start your free trial and see exactly where AI mentions your brand. Your %d-day trial gives you full access to track, analyze, and grow your presence, before your competitors do.', 'seo-by-rank-math' ),
					TRIAL_DAYS
				) }
			</p>

			<FeatureChecklist />

			<p className={ `${ ns }__notice` }>
				{ sprintf(
					/* translators: %s: localized auto-renew date, e.g. "June 26, 2026". */
					__( 'Your trial begins immediately. Auto-renews on %s unless cancelled.', 'seo-by-rank-math' ),
					renewDate
				) }
			</p>

			{ error && (
				<div className={ `${ ns }__error` } role="alert">
					{ error }
				</div>
			) }

			<div className={ `${ ns }__actions` }>
				<Button
					variant="primary"
					className={ `${ ns }__cta` }
					onClick={ onActivate }
					iconRight={ <Icon icon={ arrowRight } size={ 18 } /> }
				>
					{ error
						? __( 'Retry Activation', 'seo-by-rank-math' )
						: __( 'Start 15-Day Free Trial', 'seo-by-rank-math' )
					}
				</Button>
			</div>

			<p className={ `${ ns }__footnote` }>
				{ __( '30-Day Money-Back Guarantee', 'seo-by-rank-math' ) }
			</p>
		</AccessGateModal>
	)
}

/**
 * Visual state of a provisioning step row.
 *
 * @param {number} index      Step index.
 * @param {number} activeStep Currently active step index.
 * @return {string} 'done' | 'active' | 'pending'.
 */
const getStepState = ( index, activeStep ) => {
	if ( index < activeStep ) {
		return 'done'
	}
	return index === activeStep ? 'active' : 'pending'
}

/**
 * Activating state — spinner, headline, and cosmetic provisioning steps.
 *
 * @return {JSX.Element} Activating progress modal.
 */
const ActivatingState = () => {
	const ns = 'rank-math-ai-visibility-activating-modal'
	const [ activeStep, setActiveStep ] = useState( 0 )

	// Advance the steps on a timer; hold on the last one until the hook resolves.
	useEffect( () => {
		const intervalId = setInterval( () => {
			setActiveStep( ( step ) => Math.min( step + 1, ACTIVATION_STEPS.length - 1 ) )
		}, STEP_ADVANCE_MS )

		return () => clearInterval( intervalId )
	}, [] )

	return (
		<Modal
			onRequestClose={ noop }
			className={ ns }
			overlayClassName="rank-math-modal-overlay rank-math-ai-visibility-content-overlay"
			isDismissible={ false }
			shouldCloseOnEsc={ false }
			shouldCloseOnClickOutside={ false }
			aria={ { labelledby: `${ ns }-title` } }
			__experimentalHideHeader
		>
			<div className={ `${ ns }__spinner` }>
				<Spinner />
			</div>

			<h1 id={ `${ ns }-title` } className={ `${ ns }__title` }>
				{ __( 'Activating Your AI Visibility Trial…', 'seo-by-rank-math' ) }
			</h1>

			<p className={ `${ ns }__subtitle` }>
				{ __( "We're setting up your workspace and connecting AI models. This will take just a moment.", 'seo-by-rank-math' ) }
			</p>

			<ul className={ `${ ns }__steps` }>
				{ ACTIVATION_STEPS.map( ( step, index ) => {
					const state = getStepState( index, activeStep )
					return (
						<li key={ step } className={ `${ ns }__step ${ ns }__step--${ state }` }>
							<span className={ `${ ns }__step-icon` } aria-hidden="true">
								{ 'done' === state
									? <i className="rm-icon rm-icon-tick" />
									: <span className={ `${ ns }__step-circle` } />
								}
							</span>
							{ step }
						</li>
					)
				} ) }
			</ul>
		</Modal>
	)
}

/**
 * Success state — "Welcome to AI Visibility" with the Go to Dashboard CTA.
 *
 * @return {JSX.Element} Trial success modal.
 */
// Placeholder - swap this URL for the real quick-start guide video when ready.
const QUICK_START_VIDEO_URL = 'https://www.youtube.com/embed/aqz-KE-bpKQ?autoplay=1'

const SuccessState = () => {
	const ns = 'rank-math-ai-visibility-trial-success'
	const [ isPlaying, setIsPlaying ] = useState( false )

	// Reload re-localizes the refreshed plan from PHP, dropping the gate.
	const goToDashboard = () => window.location.reload()

	return (
		<AccessGateModal
			icon="dashicons dashicons-yes-alt"
			title={ __( 'Welcome to AI Visibility', 'seo-by-rank-math' ) }
			className={ ns }
		>
			<p className="rank-math-ai-visibility-gate__intro">
				{ __( "Your 15-day free trial is now active. We've sent a confirmation email with your trial details and next steps.", 'seo-by-rank-math' ) }
			</p>

			{ /* Quick Start Guide — click to play. */ }
			{ isPlaying ? (
				<div className={ `${ ns }__video ${ ns }__video--playing` }>
					<iframe
						className={ `${ ns }__video-iframe` }
						src={ QUICK_START_VIDEO_URL }
						title={ __( 'Quick Start Guide', 'seo-by-rank-math' ) }
						allow="autoplay; fullscreen"
						allowFullScreen
					/>
				</div>
			) : (
				<div
					className={ `${ ns }__video ${ ns }__video--idle` }
					role="button"
					tabIndex={ 0 }
					onClick={ () => setIsPlaying( true ) }
					onKeyDown={ ( e ) => ( e.key === 'Enter' || e.key === ' ' ) && setIsPlaying( true ) }
					aria-label={ __( 'Play Quick Start Guide', 'seo-by-rank-math' ) }
				>
					<i className={ `${ ns }__video-play dashicons dashicons-controls-play` } aria-hidden="true" />
					<span className={ `${ ns }__video-caption` }>
						{ __( 'Quick Start Guide', 'seo-by-rank-math' ) }
					</span>
				</div>
			) }

			<div className={ `${ ns }__links` }>
				<a
					href={ getLink( 'knowledgebase', 'AI Visibility Welcome Modal' ) }
					target="_blank"
					rel="noreferrer"
					className={ `${ ns }__link` }
				>
					<i className="rm-icon rm-icon-book" aria-hidden="true" />
					{ __( 'Knowledge Base', 'seo-by-rank-math' ) }
				</a>
				<a
					href={ getLink( 'support', 'AI Visibility Welcome Modal' ) }
					target="_blank"
					rel="noreferrer"
					className={ `${ ns }__link` }
				>
					<i className="rm-icon rm-icon-support" aria-hidden="true" />
					{ __( 'Contact Support', 'seo-by-rank-math' ) }
				</a>
			</div>

			<div className="rank-math-ai-visibility-gate__actions">
				<Button
					variant="primary"
					className="rank-math-ai-visibility-gate__cta"
					onClick={ goToDashboard }
					iconRight={ <Icon icon={ arrowRight } size={ 18 } /> }
				>
					{ __( 'Go to Dashboard', 'seo-by-rank-math' ) }
				</Button>
			</div>
		</AccessGateModal>
	)
}

/**
 * ActivateTrialModal component.
 *
 * @return {JSX.Element} Trial activation flow modal.
 */
const ActivateTrialModal = () => {
	const { status, error, activate } = useTrialActivation()

	if ( ACTIVATION_STATUS.ACTIVATING === status ) {
		return <ActivatingState />
	}

	if ( ACTIVATION_STATUS.SUCCESS === status ) {
		return <SuccessState />
	}

	// idle + error share the pitch layout; error adds the notice + Retry CTA.
	return <TrialPitch error={ error } onActivate={ activate } />
}

ActivateTrialModal.displayName = 'ActivateTrialModal'

export default ActivateTrialModal
