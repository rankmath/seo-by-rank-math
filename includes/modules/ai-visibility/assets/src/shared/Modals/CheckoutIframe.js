/**
 * CheckoutIframe — authenticated Content AI checkout/upgrade iframe.
 *
 * Fetches a fresh single-use checkout URL on mount (token expires in 15 min
 * and is consumed on first load) and renders it. A new URL is requested per
 * mount, so the parent must remount this component for each session rather
 * than reuse a stale URL.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState, useEffect, useRef } from '@wordpress/element'
import { Spinner } from '@wordpress/components'

/**
 * Internal dependencies
 */
import Button from '../components/Button'
import { getCheckoutUrl } from '../services/api/aiVisibilityApi'
import './CheckoutIframe.scss'

/**
 * CheckoutIframe component.
 *
 * @return {JSX.Element} Loading, error, or iframe view.
 */
const CheckoutIframe = () => {
	const ns = 'rank-math-ai-visibility-checkout-iframe'

	const [ url, setUrl ] = useState( '' )
	const [ error, setError ] = useState( null )
	const [ loading, setLoading ] = useState( true )

	const requested = useRef( false )

	const fetchUrl = () => {
		setLoading( true )
		setError( null )

		getCheckoutUrl()
			.then( ( data ) => {
				// A missing URL means an error envelope slipped through with a
				// 2xx status (apiFetch only rejects on non-2xx), so surface its
				// message instead of rendering a blank iframe.
				if ( ! data?.url ) {
					throw new Error( data?.message || __( 'Could not start the checkout session. Please try again.', 'seo-by-rank-math' ) )
				}
				setUrl( data.url )
			} )
			.catch( ( err ) => setError( err?.message || __( 'Could not start the checkout session. Please try again.', 'seo-by-rank-math' ) ) )
			.finally( () => setLoading( false ) )
	}

	useEffect( () => {
		if ( requested.current ) {
			return
		}
		requested.current = true
		fetchUrl()
	}, [] )

	const retry = () => {
		requested.current = true
		fetchUrl()
	}

	if ( loading ) {
		return (
			<div className={ `${ ns }__state` }>
				<Spinner />
			</div>
		)
	}

	if ( error ) {
		return (
			<div className={ `${ ns }__state` }>
				<p className={ `${ ns }__error` }>{ error }</p>
				<Button variant="secondary" onClick={ retry }>
					{ __( 'Try Again', 'seo-by-rank-math' ) }
				</Button>
			</div>
		)
	}

	return (
		<iframe
			className={ ns }
			src={ url }
			title={ __( 'Upgrade your Content AI plan', 'seo-by-rank-math' ) }
		/>
	)
}

CheckoutIframe.displayName = 'CheckoutIframe'

export default CheckoutIframe
