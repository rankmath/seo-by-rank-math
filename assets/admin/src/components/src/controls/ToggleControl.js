/**
 * WordPress dependencies
 */
import { ToggleControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import './scss/ToggleControl.scss'

/**
 * Toggle Control component.
 *
 * @param {Object}   props           Component props.
 * @param {string}   props.label     The label associated with the toggle control.
 * @param {Function} props.onChange  Callback invoked when the control is clicked.
 * @param {boolean}  props.disabled  Whether the toggle is disabled.
 * @param {boolean}  props.checked   Whether the toggle is checked.
 * @param {string}   props.className CSS class for additonal styling.
 */
export default ( {
	label,
	onChange,
	disabled,
	checked,
	className = '',
	...additionalProps
} ) => {
	const props = {
		...additionalProps,
		label,
		onChange,
		disabled,
		checked,
		__nextHasNoMarginBottom: true,
		className: `rank-math-toggle-control ${ className }`,
	}

	return <ToggleControl { ...props } />
}
