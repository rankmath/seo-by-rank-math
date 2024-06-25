/**
 * External dependencies
 */
import classNames from 'classnames'

/**
 * WordPress dependencies
 */
import { CustomSelectControl, Disabled } from '@wordpress/components'

/**
 * Internal dependencies
 */
import './scss/SelectControl.scss'

/**
 * Select Control component.
 *
 * @param {Object}   props           Component props.
 * @param {string}   props.size      Adjusts the size of the select.
 * @param {Object}   props.value     The current value selected.
 * @param {string}   props.label     The label associated with the control.
 * @param {Array}    props.options   The dropdown options.
 * @param {Function} props.onChange  Callback invoked when the selection changes.
 * @param {string}   props.className CSS class for additional styling.
 * @param {boolean}  props.disabled  Whether the control is disabled.
 */
export default ( {
	size,
	label,
	value,
	options,
	onChange,
	className,
	disabled = false,
	...additionalProps
} ) => {
	className = classNames(
		className,
		'rank-math-select-control',
		{
			'with-label': label,
			'is-disabled': disabled,
		}
	)

	const props = {
		...additionalProps,
		size,
		label,
		value,
		options,
		onChange,
		disabled,
		className,
		__nextUnconstrainedWidth: true,
		__next36pxDefaultSize: true,
	}

	return (
		<Disabled isDisabled={ disabled }>
			<CustomSelectControl { ...props } />
		</Disabled>
	)
}
