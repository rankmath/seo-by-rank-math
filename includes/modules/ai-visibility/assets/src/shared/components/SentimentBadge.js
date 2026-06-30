/**
 * SentimentBadge — coloured pill showing an average sentiment score.
 *
 * @since 1.0.273
 */

/**
 * External dependencies
 */
import { round } from 'lodash'

/**
 * Internal dependencies
 */
import './SentimentBadge.scss'

/**
 * Simple smiley face SVG icon — coloured via `currentColor` (inherits from badge).
 *
 * @param {Object} props          Component props.
 * @param {string} props.modifier BEM modifier for the badge variant (e.g. 'positive', 'negative').
 * @return {JSX.Element} 12×12 smiley face SVG.
 */
const SmileyIcon = ( { modifier } ) => {
	if ( modifier === 'positive' ) {
		return (
			<svg
				width="12"
				height="12"
				viewBox="0 0 12 12"
				style={ { fill: 'none', flexShrink: 0 } }
				aria-hidden="true"
			>
				<circle cx="6" cy="6" r="5" stroke="currentColor" strokeWidth="1" />
				<circle cx="4" cy="4.8" r="0.8" fill="currentColor" />
				<circle cx="8" cy="4.8" r="0.8" fill="currentColor" />
				<path
					d="M3.5 7 Q6 9 8.5 7"
					stroke="currentColor"
					strokeWidth="1"
					strokeLinecap="round"
				/>
			</svg>
		)
	}

	if ( modifier === 'negative' ) {
		return (
			<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M5.83335 5.38542C5.83335 4.98271 5.50687 4.65625 5.10417 4.65625C4.70146 4.65625 4.375 4.98271 4.375 5.38542C4.375 5.78812 4.70146 6.11467 5.10417 6.11467C5.50687 6.11467 5.83335 5.78812 5.83335 5.38542Z" fill="#F66276" />
				<path d="M8.89567 4.65625C9.2984 4.65625 9.62484 4.98271 9.62484 5.38542C9.62484 5.78812 9.2984 6.11467 8.89567 6.11467C8.493 6.11467 8.1665 5.78818 8.1665 5.38548C8.1665 4.98277 8.493 4.65625 8.89567 4.65625Z" fill="#F66276" />
				<path d="M5.38427 9.48806C5.23378 9.67712 4.95854 9.70833 4.76951 9.55783C4.58048 9.40733 4.54924 9.13211 4.69974 8.94305C5.23746 8.26767 6.06821 7.8335 7.00003 7.8335C7.9319 7.8335 8.76263 8.26767 9.30034 8.94305C9.45084 9.13211 9.41963 9.40733 9.23058 9.55783C9.04158 9.70833 8.7663 9.67712 8.6158 9.48806C8.23699 9.01224 7.65412 8.7085 7.00003 8.7085C6.34599 8.7085 5.76313 9.01224 5.38427 9.48806Z" fill="#F66276" />
				<path fillRule="evenodd" clipRule="evenodd" d="M6.99984 1.1665C3.77817 1.1665 1.1665 3.77817 1.1665 6.99984C1.1665 10.2215 3.77817 12.8332 6.99984 12.8332C10.2215 12.8332 12.8332 10.2215 12.8332 6.99984C12.8332 3.77817 10.2215 1.1665 6.99984 1.1665ZM2.0415 6.99984C2.0415 4.26143 4.26143 2.0415 6.99984 2.0415C9.73824 2.0415 11.9582 4.26143 11.9582 6.99984C11.9582 9.73824 9.73824 11.9582 6.99984 11.9582C4.26143 11.9582 2.0415 9.73824 2.0415 6.99984Z" fill="#F66276" />
			</svg>
		)
	}

	return (
		<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M5.63997 5.38542C5.63997 4.98271 5.31352 4.65625 4.91081 4.65625C4.5081 4.65625 4.18164 4.98271 4.18164 5.38542C4.18164 5.78812 4.5081 6.11467 4.91081 6.11467C5.31352 6.11467 5.63997 5.78812 5.63997 5.38542Z" fill="#E8A623" />
			<path d="M4.7207 8.30566C4.47908 8.30566 4.2832 8.50155 4.2832 8.74316C4.2832 8.98478 4.47908 9.18066 4.7207 9.18066H9.27886C9.52047 9.18066 9.71636 8.98478 9.71636 8.74316C9.71636 8.50155 9.52047 8.30566 9.27886 8.30566H4.7207Z" fill="#E8A623" />
			<path d="M9.08903 4.65625C9.49176 4.65625 9.8182 4.98271 9.8182 5.38542C9.8182 5.78812 9.49176 6.11467 9.08903 6.11467C8.6863 6.11467 8.35986 5.78818 8.35986 5.38548C8.35986 4.98277 8.6863 4.65625 9.08903 4.65625Z" fill="#E8A623" />
			<path fillRule="evenodd" clipRule="evenodd" d="M6.99984 1.1665C3.77817 1.1665 1.1665 3.77817 1.1665 6.99984C1.1665 10.2215 3.77817 12.8332 6.99984 12.8332C10.2215 12.8332 12.8332 10.2215 12.8332 6.99984C12.8332 3.77817 10.2215 1.1665 6.99984 1.1665ZM2.0415 6.99984C2.0415 4.26143 4.26143 2.0415 6.99984 2.0415C9.73824 2.0415 11.9582 4.26143 11.9582 6.99984C11.9582 9.73824 9.73824 11.9582 6.99984 11.9582C4.26143 11.9582 2.0415 9.73824 2.0415 6.99984Z" fill="#E8A623" />
		</svg>
	)
}

/**
 * Resolve BEM modifier and badge hex colour from a numeric sentiment value.
 *
 * @param {number|null|undefined} value Sentiment 0–100, or null.
 * @return {{ modifier: string }} BEM modifier for the badge.
 */
const resolveVariant = ( value ) => {
	if ( value === null || value === undefined ) {
		return { modifier: 'neutral' }
	}

	const n = Number( value )

	if ( n >= 70 ) {
		return { modifier: 'positive' }
	}
	if ( n >= 50 ) {
		return { modifier: 'warning' }
	}

	return { modifier: 'negative' }
}

/**
 * SentimentBadge component.
 *
 * @param {Object}      props
 * @param {number|null} props.score Sentiment score 0–100, or null for no data.
 * @return {JSX.Element} Coloured sentiment pill with smiley icon and score percentage.
 */
const SentimentBadge = ( { score } ) => {
	const { modifier } = resolveVariant( score )
	const hasValue = score !== null && score !== undefined

	const ns = 'rank-math-ai-visibility-sentiment-badge'

	return (
		<span
			className={ `${ ns } ${ ns }--${ modifier }` }
		>
			{ hasValue && (
				<span className={ `${ ns }__icon` }>
					<SmileyIcon modifier={ modifier } />
				</span>
			) }
			<span className={ `${ ns }__label` }>
				{ hasValue ? `${ round( score ) }%` : '−' }
			</span>
		</span>
	)
}

export default SentimentBadge
