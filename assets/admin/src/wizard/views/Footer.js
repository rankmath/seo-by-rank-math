/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

export default ( { searchParams } ) => {
	if ( searchParams.get( 'step' ) === 'ready' ) {
		return
	}

	return (
		<div className="return-to-dashboard">
			<a href={ rankMath.adminurl + '?page=rank-math&view=modules' }>
				{ __( 'Return to dashboard', 'rank-math' ) }
			</a>
		</div>
	)
}
