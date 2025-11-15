/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'

/**
 * Create anchor tag in string format.
 *
 * @param {string} id     getLink id.
 * @param {string} medium getLink medium.
 * @param {string} text   Text for the anchor tag.
 */
export default ( id, medium, text ) =>
	`<a href="${ getLink( id, medium ) }" target="_blank" rel="noopener noreferrer">${ text }</a>`
