/**
 * External dependencies
 */
import $ from 'jquery'
import { isNull, isUndefined, includes } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Modal } from '@wordpress/components'
import { createRoot } from '@wordpress/element'
import getLink from './getLink'

export default () => {
	if ( ! isNull( document.getElementById( 'rank-math-onsite-checkout-wrapper' ) ) ) {
		$( '.components-modal__screen-overlay' ).show()
		return false
	}

	$( 'body' ).append( '<div id="rank-math-onsite-checkout-wrapper"></div>' )
	setTimeout( () => {
		createRoot( document.getElementById( 'rank-math-onsite-checkout-wrapper' ) ).render(
			<Modal
				className="rank-math-onsite-checkout-modal"
				onRequestClose={ ( event ) => {
					if ( ! isUndefined( event ) && includes( event.target.classList, 'rank-math-onsite-checkout-modal' ) ) {
						return false
					}

					$( '.components-modal__screen-overlay' ).hide()
					$( 'body' ).removeClass( 'modal-open' )
				} }
				shouldCloseOnClickOutside={ true }
			>
				<iframe title={ __( 'OnSite Checkout', 'rank-math' ) } width="100%" height="100%" src={ getLink( 'site-checkout' ) } />
			</Modal>
		)
	}, 100 )
}
