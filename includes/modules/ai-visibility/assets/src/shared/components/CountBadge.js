/**
 * CountBadge — 28×28 circular count pill.
 *
 * @since 1.0.273
 */

/**
 * Internal dependencies
 */
import './CountBadge.scss'

/**
 * CountBadge component.
 *
 * @param {Object}             props
 * @param {number|string|null} props.value     Numeric count, or null/'error'/'warning' for no data.
 * @param {string}             [props.variant] 'citations' | 'mentions' | 'neutral' (default).
 * @return {JSX.Element} Circular count badge.
 */
const CountBadge = ( { value, variant = 'neutral' } ) => {
	const isSpecial = value === null || value === undefined || value === 'error' || value === 'warning'
	const display = isSpecial ? '-' : value

	const ns = 'rank-math-ai-visibility-count-badge'

	return (
		<span className={ `${ ns } ${ ns }--${ variant }` }>
			{ display }
		</span>
	)
}

export default CountBadge
