/**
 * External dependencies
 */
import { map } from 'lodash'

/**
 * WordPress dependencies
 */
import {
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
	Disabled,
} from '@wordpress/components'
import { ReactNode } from '@wordpress/element'

/**
 * Internal dependencies
 */
import './scss/ToggleGroupControl.scss'

/**
 * Toggle Group Control component.
 *
 * @param {Object}    props           Component props.
 * @param {string}    props.value     The selected value.
 * @param {Array}     props.options   The selection options.
 * @param {Function}  props.onChange  Callback invoked when the selection changes.
 * @param {ReactNode} props.children  Additional Toggle Group Control Option.
 * @param {string}    props.className CSS class for additional styling.
 * @param {string}    props.width     Sets the width of the control.
 * @param {boolean}   props.disabled  Whether the control is disabled.
 */
export default ( {
	value,
	options,
	onChange,
	children,
	className = '',
	width = '100%',
	disabled = false,
	...additionalProps
} ) => {
	const props = {
		...additionalProps,
		value,
		onChange,
		isBlock: true,
		'aria-disabled': disabled,
		__nextHasNoMarginBottom: true,
		className: `rank-math-toggle-group-control ${ className }`,
	}

	return (
		<Disabled isDisabled={ disabled } style={ { width } }>
			<ToggleGroupControl { ...props }>
				{ map(
					options,
					( { label, value: optionValue } ) => (
						<ToggleGroupControlOption
							label={ label }
							value={ optionValue }
							key={ optionValue }
						/>
					)
				) }
				{ children }
			</ToggleGroupControl>
		</Disabled>
	)
}
