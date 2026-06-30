/**
 * StatusBadge — run status pill (success / error / partial).
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import './StatusBadge.scss'

/**
 * Get the human-readable label for a run status.
 *
 * @param {string} status 'success' | 'partial' | 'error'
 * @return {string} Translated status label.
 */
const getStatusLabel = ( status ) => {
	if ( status === 'success' ) {
		return __( 'Success', 'seo-by-rank-math' )
	}
	if ( status === 'partial' ) {
		return __( 'Partial', 'seo-by-rank-math' )
	}
	return __( 'Failed', 'seo-by-rank-math' )
}

/**
 * StatusBadge component.
 *
 * @param {Object} props
 * @param {string} [props.status] 'success' | 'error' | 'partial'
 * @return {JSX.Element|null} Coloured status pill with SVG icon.
 */
const StatusBadge = ( { status } ) => {
	if ( ! status ) {
		return null
	}

	const isSuccess = status === 'success'

	const ns = 'rank-math-ai-visibility-status-badge'

	return (
		<span className={ `${ ns } ${ ns }--${ status }` }>
			{ isSuccess ? <i className="dashicons dashicons-yes" aria-hidden="true" /> : <i className="dashicons dashicons-no" aria-hidden="true" /> }
			{ getStatusLabel( status ) }
		</span>
	)
}

export default StatusBadge
