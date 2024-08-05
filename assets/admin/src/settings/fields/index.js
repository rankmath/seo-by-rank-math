// eslint-disable-next-line eslint-comments/disable-enable-pair
/* eslint-disable import/no-unresolved */
/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose'
import { withSelect, withDispatch } from '@wordpress/data'

/**
 * Internal dependencies
 */
import CheckboxList from '@rank-math/components/controls/CheckboxList'

const Field = ( props ) => {
	const { field, settingType, settings } = props
	const { id, type, ...remainingFieldProps } = field

	const fieldValue = settings[ settingType ]?.[ id ]
	const handleChange = ( newValue ) => props.updateSetting( id, newValue )

	// Get field props based on type
	const getFieldProps = () => {
		let fieldState = 'value'

		switch ( type ) {
			case 'toggle':
				fieldState = 'checked'
				break
			case 'multicheck_inline':
				fieldState = 'selected'
				break
			default:
				break
		}

		return {
			...remainingFieldProps,
			[ fieldState ]: fieldValue,
			onChange: handleChange,
		}
	}

	const components = {
		multicheck: CheckboxList,
		multicheck_inline: CheckboxList,
	}

	const FieldComponent = components[ type ]

	return <FieldComponent { ...getFieldProps() } />
}

export default compose(
	withSelect( ( select, props ) => {
		const settings = select( 'rank-math-settings' ).getSettings()
		const roleCapabilities = select( 'rank-math-settings' ).getRoleCapabilities()

		return {
			field: props.field,
			settingType: props.settingType,
			settings: { ...settings, roleCapabilities },
		}
	} ),
	withDispatch( ( dispatch, props ) => {
		const { settings, settingType } = props
		return {
			updateSetting( key, value ) {
				settings[ settingType ][ key ] = value
				dispatch( 'rank-math-settings' ).updateSettings( settings )
			},
		}
	} )
)( Field )
