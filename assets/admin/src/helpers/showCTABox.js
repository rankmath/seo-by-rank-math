/*!
 * Rank Math
 *
 * @version 1.0.218
 * @author  RankMath
 */

/**
 * External Dependencies
 */
import jQuery from 'jquery'
import { isNull, isUndefined } from 'lodash'

/**
 * WordPress Dependencies
 */
import { render } from '@wordpress/element'
import { Modal } from '@wordpress/components'

/**
 * Internal Dependencies
 */
import ErrorCTA from '@components/ErrorCTA'

export default ( { showProNotice = false, isBulkEdit = false, creditsRequired = 0 } ) => {
	if ( isNull( document.getElementById( 'rank-math-content-ai-modal-wrapper' ) ) ) {
		jQuery( '#wpwrap' ).append( '<div id="rank-math-content-ai-modal-wrapper"></div>' )
	}

	setTimeout( () => {
		render(
			<Modal
				className="rank-math-contentai-modal rank-math-modal rank-math-error-modal"
				shouldCloseOnClickOutside={ true }
				onRequestClose={ ( e ) => {
					if ( isUndefined( e ) ) {
						return
					}

					jQuery( '.components-modal__screen-overlay' ).remove()
					document.getElementById( 'rank-math-content-ai-modal-wrapper' ).remove()
					document.body.classList.remove( 'modal-open' )
				} }
			>
				<ErrorCTA width={ 100 } showProNotice={ showProNotice } isBulkEdit={ isBulkEdit } creditsRequired={ creditsRequired } />
			</Modal>,
			document.getElementById( 'rank-math-content-ai-modal-wrapper' )
		)
	}, 100 )
}
