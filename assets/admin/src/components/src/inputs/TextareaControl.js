/**
 * External dependencies
 */
import classNames from 'classnames'

/**
 * WordPress dependencies
 */
import { TextareaControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import './scss/TextareaControl.scss'

/**
 * Textarea Control component.
 *
 * @param {Object}   props             Component props.
 * @param {number}   props.rows        The number of rows the textarea should contain.
 * @param {number}   props.cols        The number of cols the textarea should contain.
 * @param {string}   props.help        Additional description for the control.
 * @param {string}   props.value       The current value of the input.
 * @param {string}   props.label       The label associated with the control.
 * @param {boolean}  props.disabled    Whether the control is disabled.
 * @param {string}   props.placeholder Placeholder text.
 * @param {string}   props.className   CSS class for additional styling.
 * @param {Function} props.onChange    Callback invoked when the input value changes.
 * @param {string}   props.variant     Specifies the input's style. Accepted values: 'code-box' or 'metabox'.
 */
export default ( {
	help,
	value,
	label,
	disabled,
	placeholder,
	className,
	onChange,
	variant,
	rows = 4,
	cols = 30,
	...additionalProps
} ) => {
	className = classNames( variant, className, 'rank-math-textarea-control' )

	const props = {
		...additionalProps,
		rows,
		cols,
		help,
		value,
		label,
		onChange,
		disabled,
		placeholder,
		className,
	}

	return <TextareaControl { ...props } />
}
