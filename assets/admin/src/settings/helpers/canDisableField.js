/**
 * External dependency
 */
import { some } from 'lodash'

/**
 * Determines whether to disable a dependent field.
 *
 * @param {Array}  dependency The IDs of master fields.
 * @param {Object} settings   Group of settings where the master field value is located.
 * @return {boolean} Returns true, if the the dependent field should be disabled.
 */
export default ( dependency, settings ) => {
	return some( dependency, ( [ id ] ) => settings[ id ] )
}
