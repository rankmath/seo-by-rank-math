/**
 * External dependencies
 */
import { size, isArray, unescape } from 'lodash'

/**
 * Internal dependencies
 */
import generateHelpLink from '../helpers/generateHelpLink'

/**
 * Add Label with Tooltip.
 *
 * @param {Object} props          Component props.
 * @param {number} props.id       Label ID.
 * @param {Object} props.data     Data containing Tooltip text and maximum length.
 * @param {string} props.value    Field text.
 * @param {string} props.endpoint Current endpoint.
 */
export default ( { id, data, value, endpoint = '' } ) => {
	const helpLink = generateHelpLink( endpoint )
	return (
		<label htmlFor={ id }>
			{ unescape( data.label ) }
			{
				helpLink &&
				<a href={ helpLink + '#' + id } rel="noreferrer" target="_blank" title={ data.tooltip }>
					<em className="dashicons-before dashicons-editor-help rank-math-tooltip"></em>
				</a>
			}
			{
				data.maxlength &&
				<span className="limit">
					<span className="count">{ size( ! isArray( value ) ? value : value.join( ' ' ) ) }</span>
					/{ data.maxlength }
				</span>
			}
		</label>
	)
}
