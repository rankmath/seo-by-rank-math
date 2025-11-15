/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Local SEO Opening hours fields
 */
export default () => (
	[
		{
			id: 'day',
			type: 'select',
			options: {
				Monday: __( 'Monday', 'rank-math-pro' ),
				Tuesday: __( 'Tuesday', 'rank-math-pro' ),
				Wednesday: __( 'Wednesday', 'rank-math-pro' ),
				Thursday: __( 'Thursday', 'rank-math-pro' ),
				Friday: __( 'Friday', 'rank-math-pro' ),
				Saturday: __( 'Saturday', 'rank-math-pro' ),
				Sunday: __( 'Sunday', 'rank-math-pro' ),
			},
		},
		{
			id: 'time',
			type: 'text',
			placeholder: __( 'e.g. 09:00-17:00', 'rank-math-pro' ),
		},
	]
)