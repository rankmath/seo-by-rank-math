/**
 * CountryFlag — locale tag showing a flag emoji and region code.
 *
 * Flag emoji is produced via the Unicode regional-indicator trick, which
 * works on macOS, iOS, Android, and Linux. Windows Chrome renders country
 * flags as letter-pair glyphs; if Windows fidelity becomes a requirement,
 * swap the emoji span for an <img> without changing the component API.
 *
 * @since 1.0.273
 */

/**
 * Internal dependencies
 */
import './CountryFlag.scss'

/**
 * Convert a 2-letter region code (e.g. "US", "EU") to a flag emoji string
 * using Unicode regional-indicator symbols (U+1F1E6 … U+1F1FF).
 *
 * @param {string} region Two-letter ISO 3166-1 alpha-2 or regional code.
 * @return {string}        Flag emoji, e.g. "🇺🇸".
 */
const toFlagEmoji = ( region ) =>
	[ ...region.toUpperCase() ]
		.map( ( char ) => String.fromCodePoint( 0x1f1e6 + char.charCodeAt( 0 ) - 65 ) )
		.join( '' )

/**
 * Extract the region code from a BCP 47 locale string or return the raw
 * value when it is already a plain code (e.g. "US", "EU").
 *
 * @param {string} locale e.g. "en-US" or "US".
 * @return {string}        Upper-cased region code, e.g. "US".
 */
const extractRegion = ( locale ) => {
	if ( ! locale ) {
		return ''
	}
	const parts = locale.split( '-' )
	return ( parts[ 1 ] || parts[ 0 ] ).toUpperCase()
}

/**
 * CountryFlag component.
 *
 * @param {Object} props
 * @param {string} props.locale BCP 47 locale ("en-US") or plain region code ("US").
 * @return {JSX.Element|null}     Null when locale is falsy.
 */
const CountryFlag = ( { locale } ) => {
	if ( ! locale ) {
		return null
	}

	const region = extractRegion( locale )
	const flag = toFlagEmoji( region )

	const ns = 'rank-math-ai-visibility-country-flag'

	return (
		<span
			className={ ns }
			aria-label={ region }
		>
			<span className={ `${ ns }__emoji` } aria-hidden="true">
				{ flag }
			</span>
			<span className={ `${ ns }__code` }>
				{ region }
			</span>
		</span>
	)
}

export default CountryFlag
