/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Get category label by slug.
 *
 * @param {string} category Current category slug.
 */
export default ( category ) => {
	const categoryMap = {
		priority: __( 'Priority', 'rank-math' ),
		advanced: __( 'Advanced SEO', 'rank-math' ),
		basic: __( 'Basic SEO', 'rank-math' ),
		performance: __( 'Performance', 'rank-math' ),
		security: __( 'Security', 'rank-math' ),
	}

	return categoryMap[ category ] || ''
}
