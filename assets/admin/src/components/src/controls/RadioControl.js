/**
 * External dependencies
 */
import classNames from 'classnames'

/**
 * WordPress dependencies
 */
import { RadioControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import './scss/RadioControl.scss'

/**
 * Radio Control component.
 *
 * @param {Object}   props           Component props.
 * @param {string}   props.label     The label associated with the radio control.
 * @param {Array}    props.options   Array of available options for selection.
 * @param {Function} props.onChange  Callback invoked when the radio selection changes.
 * @param {string}   props.selected  The currently selected option.
 * @param {string}   props.className CSS class for additional styling.
 * @param {boolean}  props.disabled  Whether the radio is disabled.
 * @param {string}   props.variant   Specifies the checkbox's style. Accepted value: 'metabox'.
 */
export default ( {
	label,
	variant,
	options,
	selected,
	disabled,
	onChange,
	className = '',
	...additionalProps
} ) => {
	className = classNames( className, variant, 'rank-math-radio-control' )

	const props = {
		...additionalProps,
		label,
		options,
		selected,
		onChange,
		disabled,
		className,
	}

	return <RadioControl { ...props } />
}
