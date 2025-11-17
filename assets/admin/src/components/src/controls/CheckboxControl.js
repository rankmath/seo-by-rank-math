/**
 * External dependencies
 */
import classNames from 'classnames'

/**
 * WordPress dependencies
 */
import { CheckboxControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import './scss/CheckboxControl.scss'

/**
 * Checkbox Control component.
 *
 * @param {Object}   props               Component props.
 * @param {string}   props.label         The label associated with the checkbox.
 * @param {string}   props.variant       Specifies the checkbox's style. Accepted value: 'metabox'.
 * @param {boolean}  props.checked       Whether the checkbox is checked or unchecked.
 * @param {boolean}  props.disabled      Whether the checkbox is disabled.
 * @param {Function} props.onChange      Callback invoked when the checkbox selection changes.
 * @param {string}   props.className     CSS class for additional styling.
 * @param {boolean}  props.indeterminate Whether the checkbox should appear as indeterminate.
 */
export default ( {
	label,
	checked,
	variant,
	disabled,
	onChange,
	className,
	indeterminate,
	...additionalProps
} ) => {
	className = classNames(
		variant,
		className,
		'rank-math-checkbox-control',
		{
			'is-indeterminate': indeterminate,
			'is-disabled': disabled,
		}
	)

	const props = {
		...additionalProps,
		label,
		checked,
		disabled,
		onChange,
		className,
		indeterminate,
		__nextHasNoMarginBottom: true,
	}

	return <CheckboxControl { ...props } />
}
