/**
 * RankBadge — coloured pill showing a brand's rank position.
 *
 * @since 1.0.273
 */

/**
 * Internal dependencies
 */
import './RankBadge.scss'

/**
 * Resolve the BEM modifier from a numeric rank value.
 *
 * @param {number|null|undefined} rank
 * @return {string} BEM modifier: 'rank-1', 'rank-2', 'rank-3', or 'neutral'.
 */
const getRankModifier = ( rank ) => {
	if ( rank === null || rank === undefined ) {
		return 'neutral'
	}

	const n = parseInt( rank, 10 )

	if ( n === 1 ) {
		return 'rank-1'
	}
	if ( n === 2 ) {
		return 'rank-2'
	}

	// Rank 3 and anything beyond → red badge.
	return 'rank-3'
}

/**
 * RankBadge component.
 *
 * @param {Object}      props
 * @param {number|null} props.rank Numeric rank (1, 2, 3 …) or null for no data.
 * @return {JSX.Element} Coloured rank circle badge.
 */
const RankBadge = ( { rank } ) => {
	const modifier = getRankModifier( rank )
	const label = ( rank !== null && rank !== undefined )
		? `${ rank }`
		: '\u2212' // "−" (Unicode minus sign)

	const ns = 'rank-math-ai-visibility-rank-badge'

	return (
		<span className={ `${ ns } ${ ns }--${ modifier }` }>
			{ label }
		</span>
	)
}

export default RankBadge
