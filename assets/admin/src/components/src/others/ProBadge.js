/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Pro Badge component
 *
 * @param {Object} props      Component props.
 * @param {string} props.href Link destination.
 */
export default ( { href } ) => (
	<span className="rank-math-pro-badge">
		<a href={ href } target="_blank" rel="noopener noreferrer">
			{ __( 'PRO', 'rank-math' ) }
		</a>
	</span>
)
