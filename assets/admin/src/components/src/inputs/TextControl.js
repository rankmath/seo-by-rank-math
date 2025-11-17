/**
 * External dependencies
 */
import classNames from 'classnames'

/**
 * WordPress dependencies
 */
import { TextControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import './scss/TextControl.scss'

/**
 * Text Control component.
 *
 * @param {Object}   props           Component props.
 * @param {string}   props.value     The current value of the input.
 * @param {Function} props.onChange  Callback invoked when the input value changes.
 * @param {string}   props.className CSS class for addtional styling.
 * @param {string}   props.variant   Specifies the input's style to render. Accepted value: 'regular-text' or 'default'.
 * @param {string}   props.type      Type of the input element to render. Defaults to "text".
 */
export default ( {
	value,
	onChange,
	className,
	type = 'text',
	variant = 'regular-text',
	...additionalProps
} ) => {
	className = classNames( variant, className, 'rank-math-text-control', {
		'no-value': ! value?.toString()?.trim(),
	} )

	const props = {
		...additionalProps,
		type,
		value,
		onChange,
		className,
		__nextHasNoMarginBottom: true,
		__next40pxDefaultSize: true,
	}

	return <TextControl { ...props } />
}
