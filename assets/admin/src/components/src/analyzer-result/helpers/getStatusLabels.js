/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Retrieve the corresponding status label based on the resuult status.
 *
 * @param {string} status
 */
export default ( status ) => {
	const labelsMap = {
		ok: __( 'Passed', 'rank-math' ),
		fail: __( 'Failed', 'rank-math' ),
		warning: __( 'Warning', 'rank-math' ),
		info: __( 'Info', 'rank-math' ),
	}

	return labelsMap[ status ] || ''
}
