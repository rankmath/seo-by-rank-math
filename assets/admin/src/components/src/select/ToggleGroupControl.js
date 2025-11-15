/**
 * External dependencies
 */
import { entries, map, keys, includes } from 'lodash'

/**
 * WordPress dependencies
 */
import {
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
	Disabled,
} from '@wordpress/components'

/**
 * Internal dependencies
 */
import CustomToggleGroupControlOption from './CustomToggleGroupControlOption'
import './scss/ToggleGroupControl.scss'

/**
 * Toggle Group Control component.
 *
 * @param {Object}   props           Component props.
 * @param {string}   props.value     The selected value.
 * @param {Array}    props.options   The selection options.
 * @param {Function} props.onChange  Callback invoked when the selection changes.
 * @param {Node}     props.children  Additional Toggle Group Control Option.
 * @param {string}   props.className CSS class for additional styling.
 * @param {string}   props.width     Sets the width of the control.
 * @param {boolean}  props.disabled  Whether the control is disabled.
 * @param {boolean}  props.addCustom Whether the add the custom textbox.
 */
export default ( {
	value,
	options,
	onChange,
	children,
	className = '',
	width = '100%',
	disabled = false,
	addCustom = false,
	...additionalProps
} ) => {
	const props = {
		...additionalProps,
		value,
		onChange,
		'aria-disabled': disabled,
		__nextHasNoMarginBottom: true,
		__next40pxDefaultSize: true,
		className: `rank-math-toggle-group-control ${ className }`,
	}

	const customValue = addCustom && ! includes( keys( options ), value ) ? value : ''
	return (
		<Disabled isDisabled={ disabled } style={ { width } }>
			<ToggleGroupControl { ...props }>
				{ map(
					entries( options ),
					( [ key, label ] ) => (
						<ToggleGroupControlOption
							label={ label }
							value={ key }
							key={ key }
						/>
					)
				) }
				{ addCustom && <CustomToggleGroupControlOption key="custom" value={ customValue } onChange={ onChange } /> }
				{ children }
			</ToggleGroupControl>
		</Disabled>
	)
}
