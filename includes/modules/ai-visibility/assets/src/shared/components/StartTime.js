/**
 * StartTime — formatted start-time cell for the analyses table.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { memo } from '@wordpress/element'

/**
 * Internal dependencies
 */
import { formatDateTime } from '../../utils/formatDate'
import './StartTime.scss'

/**
 * @param {Object} props
 * @param {string} [props.value] ISO date string from the API.
 * @return {JSX.Element} Rendered component.
 */
const StartTime = memo( ( { value = '-' } ) => {
	const ns = 'rank-math-ai-visibility-processed-start-time'

	if ( value && value !== '-' ) {
		value = formatDateTime( value ) || value
	}

	return (
		<div className={ ns }>
			{ value }
		</div>
	)
} )

StartTime.displayName = 'StartTime'

export default StartTime
