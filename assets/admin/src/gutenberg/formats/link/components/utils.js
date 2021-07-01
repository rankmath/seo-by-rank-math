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
 * @param {boolean} options.noFollow         Whether this link will have nofollow rel.
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
	noFollow,
	sponsored,
	text,
	type,
	id,
} ) {
	const format = {
		type: 'rankmath/link',
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

	if ( noFollow ) {
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
