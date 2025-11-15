// eslint-disable-next-line eslint-comments/disable-enable-pair
/* eslint-disable import/no-unresolved */
/**
 * External dependencies
 */
import { includes, isBoolean, isUndefined, endsWith, isArray, last, filter, isString, isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose'
import { withDispatch } from '@wordpress/data'
import { RawHTML } from '@wordpress/element'

const Field = ( props ) => {
	let { field, settingType, settings, value: fieldValue } = props
	const { id, type, content, Component, isDisabled, default: defaultValue, ...fieldProps } = field

	if ( isUndefined( fieldValue ) ) {
		fieldValue = isUndefined( settings ) || isUndefined( settings[ id ] ) ? defaultValue : settings[ id ]
	}

	if ( includes( [ 'disable_author_archives', 'opening_hours_format' ], id ) && isBoolean( fieldValue ) ) {
		props.updateSetting( id, false === fieldValue ? 'off' : 'on' )
	}

	if ( includes( [ 'disable_author_archives' ], id ) && '' === fieldValue ) {
		props.updateSetting( id, defaultValue )
	}

	if ( endsWith( id, '_bulk_editing' ) && ! isString( fieldValue ) ) {
		fieldValue = String( fieldValue )
	}

	if ( endsWith( id, '_default_rich_snippet' ) && ( fieldValue === '' || fieldValue === false ) ) {
		fieldValue = 'off'
	}

	const handleChange = ( newValue ) => {
		if (
			( endsWith( id, '_robots' ) || id === 'robots_global' ) &&
			isArray( newValue ) &&
			( includes( newValue, 'index' ) || includes( newValue, 'noindex' ) || includes( fieldValue || [], 'index' ) || includes( fieldValue || [], 'noindex' ) )
		) {
			const currentValue = fieldValue || []
			const hasIndex = includes( newValue, 'index' )
			const hasNoindex = includes( newValue, 'noindex' )
			const hadIndex = includes( currentValue, 'index' )
			const hadNoindex = includes( currentValue, 'noindex' )

			// Handle index/noindex mutual exclusivity
			if ( hadIndex && ! hasIndex ) {
				// Index was deselected, add noindex
				newValue = [ ...newValue, 'noindex' ]
			} else if ( hadNoindex && ! hasNoindex ) {
				// Noindex was deselected, add index
				newValue = [ ...newValue, 'index' ]
			} else if ( hasIndex && hasNoindex ) {
				// Both selected - remove the conflicting one based on last action
				const lastValue = last( newValue )
				newValue = filter( newValue, ( val ) =>
					lastValue === 'index' ? val !== 'noindex' : val !== 'index'
				)
			}
		}

		if ( type !== 'file' ) {
			props.updateSetting( id, newValue )
			return
		}

		if ( isEmpty( newValue ) ) {
			newValue = { url: '', id: '' }
		}

		props.updateSetting( id, newValue.url )
		props.updateSetting( id + '_id', newValue.id )
	}

	// Get field props based on type
	const getFieldProps = () => {
		const fieldStateMap = {
			toggle: 'checked',
			checkbox: 'checked',
		}

		const fieldState = fieldStateMap[ type ] || 'value'
		const passSettingTypeProp = includes( [ 'component', 'group' ], type )

		return {
			...fieldProps,
			id,
			[ fieldState ]: fieldProps.value || fieldValue,
			onChange: fieldProps.onChange || ( ! isDisabled && handleChange ),
			...( passSettingTypeProp && { settingType } ),
			...fieldProps.attributes,
		}
	}

	// @TODO: import these components using @rank-math/components.
	const components = {
		file: window.rankMathComponents.UploadFile,
		text: window.rankMathComponents.TextControl,
		select: window.rankMathComponents.SelectControl,
		toggle: window.rankMathComponents.ToggleControl,
		select_search: window.rankMathComponents.SelectWithSearch,
		multicheck: window.rankMathComponents.CheckboxList,
		multicheck_inline: window.rankMathComponents.CheckboxList,
		radio_inline: window.rankMathComponents.ToggleGroupControl,
		repeatable_group: window.rankMathComponents.RepeatableGroup,
		selectSearch: window.rankMathComponents.SelectWithSearch,
		searchPage: window.rankMathComponents.SearchPage,
		checkboxlist: window.rankMathComponents.CheckboxList,
		toggleGroup: window.rankMathComponents.ToggleGroupControl,
		repeatableGroup: window.rankMathComponents.RepeatableGroup,
		group: window.rankMathComponents.Group,
		checkbox: window.rankMathComponents.CheckboxControl,
		textarea: window.rankMathComponents.TextareaControl,
		notice: window.rankMathComponents.Notice,
		selectVariable: window.rankMathComponents.SelectVariable,
		button: window.rankMathComponents.Button,
	}

	const FieldComponent = components[ type ]

	const onChangeProps = type === 'group' ? { onChange: props.updateSetting } : {}
	if ( FieldComponent ) {
		return <FieldComponent { ...getFieldProps() } settings={ settings } { ...onChangeProps } />
	}

	if ( type === 'component' ) {
		return <Component { ...getFieldProps() } />
	}

	if ( type === 'raw' ) {
		return <RawHTML>{ content }</RawHTML>
	}

	return null
}

export default compose(
	withDispatch( ( dispatch, props ) => {
		const { settings } = props

		return {
			updateSetting( key, value ) {
				settings[ key ] = value
				dispatch( 'rank-math-settings' ).updateData( { ...settings } )
			},
		}
	} )
)( Field )
