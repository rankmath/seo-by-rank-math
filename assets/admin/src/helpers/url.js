/**
 * External dependencies
 */
import urlMethods from 'url'
import { includes } from 'lodash'

/**
 * WordPress dependencies
 */
import { applyFilters } from '@wordpress/hooks'

const urlFromAnchorRegex = /href=(["'])([^"']+)\1/i

/**
 * Determines whether the link is a relative fragment URL.
 *
 * @param {string} url Link to check.
 *
 * @return {boolean} Whether link is relative or not.
 */
function isRelativeFragmentURL( url ) {
	return '#' === url[ 0 ]
}

/**
 * Returns the protocol of a URL.
 *
 * @param {string} url Link to extract from.
 *
 * @return {string} Protocol.
 */
function getProtocol( url ) {
	return urlMethods.parse( url ).protocol
}

/**
 * Retrieves the URL from an anchor tag.
 *
 * @param {string} anchorTag Anchor tag string.
 *
 * @return {string} Url.
 */
function getFromAnchorTag( anchorTag ) {
	const urlMatch = urlFromAnchorRegex.exec( anchorTag )

	return ( null === urlMatch ) ? '' : urlMatch[ 2 ]
}

/**
 * Checks whether the protocol is either HTTP: or HTTPS:.
 *
 * @param {string} protocol Protol string to validate.
 *
 * @return {boolean} Has valid protocol or not.
 */
function isHttpScheme( protocol ) {
	if ( ! protocol ) {
		return true
	}

	return ( 'http:' === protocol || 'https:' === protocol )
}

/**
 * Check if external link exists in nofllow/excludeDomains
 *
 * @param {string} url Link to check.
 *
 * @return {boolean} Whether link is dofollow or not.
 */
function couldBeDoFollow( url ) {
	let isDoFollow = true
	const parsedUrl = urlMethods.parse( url, false, true )

	// Check if domain is in nofollow list.
	if ( rankMath.noFollowDomains.length ) {
		rankMath.noFollowDomains.forEach( ( domain ) => {
			if ( includes( parsedUrl.host, domain ) ) {
				isDoFollow = false
			}
		} )

		return isDoFollow
	}

	// Check if domains is NOT in list.
	if ( ! rankMath.noFollowExcludeDomains.length ) {
		return false
	}
	isDoFollow = false
	rankMath.noFollowExcludeDomains.forEach( ( domain ) => {
		if ( includes( parsedUrl.host, domain ) ) {
			isDoFollow = true
		}
	} )

	return isDoFollow
}

/**
 * Determine whether a URL is internal.
 *
 * @param {string} url  Url to check.
 * @param {string} host Site url.
 *
 * @return {boolean} Whether internal or not.
 */
function isInternalLink( url, host ) {

	// Short-circuit if filter returns non-null.
	let filtered = applyFilters( 'rankMath_analysis_isInternalLink', null, url, host );
	if ( filtered !== null ) {
		return filtered
	}

	// Check if the URL starts with a single slash.
	if ( ! includes( url, '//' ) && '/' === url[ 0 ] ) {
		return true
	}

	// Check if the URL starts with a # indicating a fragment.
	if ( isRelativeFragmentURL( url ) ) {
		return false
	}

	const parsedUrl = urlMethods.parse( url, false, true )

	// No host indicates an internal link.
	if ( ! parsedUrl.host ) {
		return true
	}

	return includes( parsedUrl.host, host )
}

/**
 * Determines the type of link.
 *
 * @param {string} text Text to get links from.
 * @param {string} url  Base link.
 *
 * @return {string} Link type.
 */
export function getLinkType( text, url ) {
	const anchorUrl = getFromAnchorTag( text )

	/**
	 * A link is "Other" if:
	 * - The protocol is neither null, nor http, nor https.
	 * - The link is a relative fragment URL (starts with #), because it won't navigate to another page.
	 */
	if ( ! isHttpScheme( getProtocol( anchorUrl ) ) || isRelativeFragmentURL( anchorUrl ) ) {
		return 'other'
	}

	if ( isInternalLink( anchorUrl, url ) ) {
		return 'internal'
	}

	return 'external'
}

/**
 * Checks if a link has a `rel` attribute with a `nofollow` value. If it has, returns Nofollow, otherwise Dofollow.
 *
 * @param {string} anchorHTML Anchor tag html.
 * @param {string} linkType   Link type
 *
 * @return {string} Nofollow or Dofollow.
 */
export function checkNofollow( anchorHTML, linkType ) {
	anchorHTML = anchorHTML.toLowerCase()
	if ( includes( anchorHTML, 'dofollow' ) ) {
		return 'Dofollow'
	}

	if ( 'internal' !== linkType && rankMath.noFollowExternalLinks && ! includes( anchorHTML, 'nofollow' ) ) {
		return couldBeDoFollow( getFromAnchorTag( anchorHTML ) ) ? 'Dofollow' : 'Nofollow'
	}

	if ( ! includes( anchorHTML, '<a' ) || ! includes( anchorHTML, 'rel=' ) ) {
		return 'Dofollow'
	}

	return includes( anchorHTML, 'nofollow' ) ? 'Nofollow' : 'Dofollow'
}

/**
 * Get anchor tags from text.
 *
 * @param {string} text Text to parse for anchor tags.
 *
 * @return {Array} Found anchor tags.
 */
export function getLinks( text ) {
	return text.match( /<a [^>]*href=([\"'])[a-z/]([^\"']+)[^>]*>/gi ) || []
}
