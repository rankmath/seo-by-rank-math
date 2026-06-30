/**
 * useTrialActivation — trial activation state machine.
 *
 * idle ──activate()──▶ activating ──▶ success / error (retry via activate()).
 *
 * @since 1.0.281
 */

/**
 * WordPress dependencies
 */
import { useState, useCallback } from '@wordpress/element'
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { activateTrial } from '../services/api/aiVisibilityApi'

/**
 * Activation flow statuses.
 */
export const ACTIVATION_STATUS = {
	IDLE: 'idle',
	ACTIVATING: 'activating',
	SUCCESS: 'success',
	ERROR: 'error',
}

// Keep the loader visible long enough to avoid a flash on fast responses.
const MIN_ACTIVATING_MS = 3500

/**
 * Trial activation hook.
 *
 * @return {Object} { status, error, activate } Activation state and handler.
 */
const useTrialActivation = () => {
	const [ status, setStatus ] = useState( ACTIVATION_STATUS.IDLE )
	const [ error, setError ] = useState( null )

	const activate = useCallback( async () => {
		setStatus( ACTIVATION_STATUS.ACTIVATING )
		setError( null )

		const startedAt = Date.now()

		try {
			await activateTrial()

			// Keep the loader visible for the minimum duration.
			const remaining = MIN_ACTIVATING_MS - ( Date.now() - startedAt )
			if ( remaining > 0 ) {
				await new Promise( ( done ) => setTimeout( done, remaining ) )
			}

			setStatus( ACTIVATION_STATUS.SUCCESS )
		} catch ( err ) {
			setStatus( ACTIVATION_STATUS.ERROR )
			setError(
				err?.message || __( 'Something went wrong while activating your trial. Please try again.', 'seo-by-rank-math' )
			)
		}
	}, [] )

	return { status, error, activate }
}

export default useTrialActivation
