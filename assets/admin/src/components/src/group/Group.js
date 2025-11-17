/**
 * External dependencies
 */
import { isUndefined, map } from 'lodash'

/**
 * Internal dependencies
 */
import Field from '@rank-math-settings/components/Field'

/**
 * Group component.
 *
 * @param {Object} props             The component props.
 * @param {Array}  props.fields      Array of form field objects.
 * @param {string} props.settingType The type of setting to check within the app data.
 */
export default ( props ) => {
	const { id, fields, settings, settingType, onChange } = props
	const data = settings[ id ] ?? {}
	return map( fields, ( field, index ) => {
		const fieldId = field.id
		const value = ! isUndefined( data ) && ! isUndefined( data[ fieldId ] ) ? data[ fieldId ] : undefined
		field.onChange = ( val ) => {
			data[ fieldId ] = val
			onChange( id, data )
		}
		return (
			<div key={ index } className="rank-math-group-field">
				<Field
					settingType={ settingType }
					field={ field }
					settings={ settings }
					value={ value }
				/>
			</div>
		)
	} )
}
