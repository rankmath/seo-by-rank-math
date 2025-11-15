/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

export default ( { betabadge, probadge } ) => {
	if ( betabadge ) {
		return <span className="rank-math-pro-badge beta">{ __( 'NEW!', 'rank-math' ) }</span>
	}

	if ( probadge && ! rankMath.isPro ) {
		return <span className="rank-math-pro-badge">{ __( 'PRO', 'rank-math' ) }</span>
	}

	return null
}
