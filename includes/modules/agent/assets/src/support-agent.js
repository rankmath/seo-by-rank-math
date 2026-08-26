/**
 * Help & Support (Support Agent) — entry point.
 *
 * @since 1.0.277
 */

/**
 * WordPress dependencies
 */
import { createRoot } from '@wordpress/element'

/**
 * Internal dependencies
 */
import { DashboardHeader } from '@rank-math/components'

document.addEventListener( 'DOMContentLoaded', () => {
	const container = document.getElementById( 'rank-math-support-agent-header' )
	if ( ! container ) {
		return
	}

	createRoot( container ).render( <DashboardHeader page={ 'support' } /> )
} )
