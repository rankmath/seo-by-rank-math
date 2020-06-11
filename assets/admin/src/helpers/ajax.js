/**
 * External dependencies
 */
import jQuery from 'jquery'

/**
 * AJAX Helper
 *
 * @param {string} action Action for ajax.
 * @param {Object} data   Data object.
 * @param {string} method Method "post" or "get".
 *
 * @return {jqXHR} The jsXHR object.
 */
export default function( action, data, method ) {
	return jQuery.ajax( {
		url: rankMath.ajaxurl,
		type: method || 'POST',
		dataType: 'json',
		data: jQuery.extend(
			true,
			{
				action: 'rank_math_' + action,
				security: rankMath.security,
			},
			data
		),
	} )
}
