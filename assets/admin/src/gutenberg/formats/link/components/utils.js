/**
 * External dependencies
 */
import { startsWith, isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import {
	getProtocol,
	isValidProtocol,
	getAuthority,
	isValidAuthority,
	getPath,
	isValidPath,
	getQueryString,
	isValidQueryString,
	getFragment,
	isValidFragment,
} from '@wordpress/url'

/**
 * Check for issues with the provided href.
 *
 * @copyright Copyright (C) 2008-2019, Yoast BV
 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
 *
 * @param {string} href The href.
 *
 * @return {boolean} Is the href invalid?
 */
export function isValidHref( href ) {
	if ( ! href ) {
		return false
	}

	const trimmedHref = href.trim()

	if ( ! trimmedHref ) {
		return false
	}

	// Does the `href` start with something that looks like a URL protocol?
	if ( /^\S+:/.test( trimmedHref ) ) {
		const protocol = getProtocol( trimmedHref )
		if ( ! isValidProtocol( protocol ) ) {
			return false
		}

		// Add some extra checks for http(s) URIs, since these are the most common use-case.
		// This ensures URIs with an http protocol have exactly two forward slashes following the protocol.
		if (
			startsWith( protocol, 'http' ) &&
			! /^https?:\/\/[^\/\s]/i.test( trimmedHref )
		) {
			return false
		}

		const authority = getAuthority( trimmedHref )
		if ( ! isValidAuthority( authority ) ) {
			return false
		}

		const path = getPath( trimmedHref )
		if ( path && ! isValidPath( path ) ) {
			return false
		}

		const queryString = getQueryString( trimmedHref )
		if ( queryString && ! isValidQueryString( queryString ) ) {
			return false
		}

		const fragment = getFragment( trimmedHref )
		if ( fragment && ! isValidFragment( fragment ) ) {
			return false
		}
	}

	// Validate anchor links.
	if ( startsWith( trimmedHref, '#' ) && ! isValidFragment( trimmedHref ) ) {
		return false
	}

	return true
}

/**
 * Generates the format object that will be applied to the link text.
 *
 * @param {Object}  options                  Hold options.
 * @param {string}  options.url              The href of the link.
 * @param {boolean} options.opensInNewWindow Whether this link will open in a new window.
 * @param {boolean} options.nofollow         Whether this link will have nofollow rel.
 * @param {boolean} options.sponsored        Whether this link will have sponsored rel.
 * @param {Object}  options.text             The text that is being hyperlinked.
 * @param {Object}  options.type             The link type.
 * @param {Object}  options.id               The referenced link ID.
 *
 * @return {Object} The final format object.
 */
export function createLinkFormat( {
	url,
	opensInNewWindow,
	nofollow,
	sponsored,
	text,
	type,
	id,
} ) {
	const format = {
		type: 'core/link',
		attributes: {
			url,
		},
	}

	const relAttributes = []

	if ( opensInNewWindow ) {
		format.attributes.target = '_blank'

		if ( ! isUndefined( text ) ) {
			const label = sprintf(
				// translators: accessibility label for external links, where the argument is the link text
				__( '%s (opens in a new tab)', 'rank-math' ),
				text
			)

			format.attributes[ 'aria-label' ] = label
		}

		relAttributes.push( 'noreferrer noopener' )
	}

	if ( type ) {
		format.attributes.type = type
	}

	if ( id ) {
		format.attributes.id = id
	}

	if ( nofollow ) {
		relAttributes.push( 'nofollow' )
	}

	if ( sponsored ) {
		relAttributes.push( 'sponsored' )
	}

	if ( relAttributes.length > 0 ) {
		format.attributes.rel = relAttributes.join( ' ' )
	}

	return format
}

/**
 * Walks forwards/backwards towards the boundary of a given format within an
 * array of format objects. Returns the index of the boundary.
 *
 * @param {Array}  formats         the formats to search for the given format type.
 * @param {number} initialIndex    the starting index from which to walk.
 * @param {Object} targetFormatRef a reference to the format type object being sought.
 * @param {number} formatIndex     the index at which we expect the target format object to be.
 * @param {string} direction       either 'forwards' or 'backwards' to indicate the direction.
 * @return {number} the index of the boundary of the given format.
 */
function walkToBoundary(
	formats,
	initialIndex,
	targetFormatRef,
	formatIndex,
	direction
) {
	let index = initialIndex

	const directions = {
		forwards: 1,
		backwards: -1,
	}

	const directionIncrement = directions[ direction ] || 1 // invalid direction arg default to forwards
	const inverseDirectionIncrement = directionIncrement * -1

	while (
		formats[ index ] &&
		formats[ index ][ formatIndex ] === targetFormatRef
	) {
		// Increment/decrement in the direction of operation.
		index = index + directionIncrement
	}

	// Restore by one in inverse direction of operation
	// to avoid out of bounds.
	index = index + inverseDirectionIncrement

	return index
}

export function getFormatBoundary(
	value,
	format,
	startIndex = value.start,
	endIndex = value.end
) {
	const EMPTY_BOUNDARIES = {
		start: null,
		end: null,
	}

	const { formats } = value
	let targetFormat
	let initialIndex

	if ( ! formats?.length ) {
		return EMPTY_BOUNDARIES
	}

	// Clone formats to avoid modifying source formats.
	const newFormats = formats.slice()

	const formatAtStart = newFormats[ startIndex ]?.find(
		( { type } ) => type === format.type
	)

	const formatAtEnd = newFormats[ endIndex ]?.find(
		( { type } ) => type === format.type
	)

	const formatAtEndMinusOne = newFormats[ endIndex - 1 ]?.find(
		( { type } ) => type === format.type
	)

	if ( !! formatAtStart ) {
		// Set values to conform to "start"
		targetFormat = formatAtStart
		initialIndex = startIndex
	} else if ( !! formatAtEnd ) {
		// Set values to conform to "end"
		targetFormat = formatAtEnd
		initialIndex = endIndex
	} else if ( !! formatAtEndMinusOne ) {
		// This is an edge case which will occur if you create a format, then place
		// the caret just before the format and hit the back ARROW key. The resulting
		// value object will have start and end +1 beyond the edge of the format boundary.
		targetFormat = formatAtEndMinusOne
		initialIndex = endIndex - 1
	} else {
		return EMPTY_BOUNDARIES
	}

	const index = newFormats[ initialIndex ].indexOf( targetFormat )

	const walkingArgs = [ newFormats, initialIndex, targetFormat, index ]

	// Walk the startIndex "backwards" to the leading "edge" of the matching format.
	startIndex = walkToStart( ...walkingArgs )

	// Walk the endIndex "forwards" until the trailing "edge" of the matching format.
	endIndex = walkToEnd( ...walkingArgs )

	// Safe guard: start index cannot be less than 0.
	startIndex = startIndex < 0 ? 0 : startIndex

	// // Return the indices of the "edges" as the boundaries.
	return {
		start: startIndex,
		end: endIndex,
	}
}

const partialRight =
	( fn, ...partialArgs ) =>
	( ...args ) =>
		fn( ...args, ...partialArgs )

const walkToStart = partialRight( walkToBoundary, 'backwards' )

const walkToEnd = partialRight( walkToBoundary, 'forwards' )