/**
 * External dependencies
 */
import { some, every, isArray, entries, includes } from 'lodash'

/**
 * Determines whether to display a dependent field.
 *
 * @param {Object} dependency The IDs and values of master fields.
 * @param {Object} settings   Settings data.
 * @return {boolean} Returns true, if the the dependent field should be displayed.
 */
export default ( dependency, settings = null ) => {
	const { relation = 'or', compare = '', ...deps } = dependency

	const checkDependency = ( [ id, value ] ) => {
		const fieldValue = settings[ id ]

		if ( compare === '!=' ) {
			return isArray( value ) ? ! includes( value, fieldValue ) : value !== fieldValue
		}

		return isArray( value ) ? includes( value, fieldValue ) : value === fieldValue
	}

	return relation === 'and'
		? every( entries( deps ), checkDependency )
		: some( entries( deps ), checkDependency )
}
