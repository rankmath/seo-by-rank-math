/**
 * AI Visibility — shared date formatting utilities.
 *
 * @since 1.0.273
 */

/**
 * @param {string} iso ISO date string.
 * @return {Date|null} Parsed Date, or null on empty/invalid input.
 */
const parseDate = ( iso ) => {
	if ( ! iso ) {
		return null
	}
	const d = new Date( iso )
	return isNaN( d.getTime() ) ? null : d
}

/**
 * "Mar 10, 2026" — short month + day + year.
 *
 * @param {string} iso ISO date string.
 * @return {string} Formatted date or '—'.
 */
export const formatShortDate = ( iso ) => {
	const d = parseDate( iso )
	if ( ! d ) {
		return '—'
	}
	return d.toLocaleDateString( 'en-US', { month: 'short', day: 'numeric', year: 'numeric' } )
}

/**
 * "Mar 10, 2026 at 2:15 PM" — short date + 12-hour time.
 *
 * @param {string} iso       ISO date string.
 * @param {string} separator Between date and time. Pass ' · ' for dot-separated format.
 * @return {string} Formatted date+time or '—'.
 */
export const formatDateTime = ( iso, separator = ' at ' ) => {
	const d = parseDate( iso )
	if ( ! d ) {
		return '—'
	}
	const date = d.toLocaleDateString( 'en-US', { month: 'short', day: 'numeric', year: 'numeric' } )
	const time = d.toLocaleTimeString( 'en-US', { hour: 'numeric', minute: '2-digit', hour12: true } )
	return `${ date }${ separator }${ time }`
}

/**
 * "10 March 2026" — day + long month + year (en-GB).
 *
 * @param {string} iso ISO date string.
 * @return {string} Formatted date or '—'.
 */
export const formatLongDate = ( iso ) => {
	const d = parseDate( iso )
	if ( ! d ) {
		return '—'
	}
	return d.toLocaleDateString( 'en-GB', { day: 'numeric', month: 'long', year: 'numeric' } )
}
