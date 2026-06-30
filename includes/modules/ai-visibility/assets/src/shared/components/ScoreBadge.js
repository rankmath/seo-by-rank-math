/**
 * ScoreBadge — AI Visibility Score pill (X/100).
 *
 * @since 1.0.273
 */

/**
 * Internal dependencies
 */
import './ScoreBadge.scss'

/**
 * Resolve the BEM modifier from a numeric score.
 *
 * @param {number|null|undefined} score AI Visibility score 0–100.
 * @return {string} BEM modifier: 'good' | 'average' | 'poor' | 'neutral'.
 */
const getVariant = ( score ) => {
	if ( score === null || score === undefined ) {
		return 'neutral'
	}
	const n = Number( score )
	if ( n >= 70 ) {
		return 'good'
	}
	if ( n >= 40 ) {
		return 'average'
	}
	return 'poor'
}

/**
 * ScoreBadge component.
 *
 * @param {Object}      props
 * @param {number|null} props.score            AI Visibility score 0–100, or null for no data.
 * @param {boolean}     [props.showOutOf=true] Append "/100" after the score.
 * @return {JSX.Element} Coloured score pill.
 */
const ScoreBadge = ( { score, showOutOf = true } ) => {
	const variant = getVariant( score )
	const hasValue = score !== null && score !== undefined
	let label = '-/-'
	if ( hasValue ) {
		label = showOutOf ? `${ score } / 100` : String( score )
	}

	const ns = 'rank-math-ai-visibility-score-badge'

	return (
		<span className={ `${ ns } ${ ns }--${ variant }` }>
			{ label }
		</span>
	)
}

export default ScoreBadge
