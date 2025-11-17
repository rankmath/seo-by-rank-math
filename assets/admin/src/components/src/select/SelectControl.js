/**
 * External dependencies
 */
import { entries, includes, map } from 'lodash'
import classNames from 'classnames'

/**
 * WordPress dependencies
 */
import { SelectControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import './scss/SelectControl.scss'

/**
 * Select Control component.
 *
 * @param {Object}   props           Component props.
 * @param {Object}   props.value     The current value selected.
 * @param {Array}    props.options   The dropdown options.
 * @param {Function} props.onChange  Callback invoked when the selection changes.
 * @param {boolean}  props.disabled  Whether the control is disabled.
 * @param {string}   props.className CSS class for additional styling.
 * @param {string}   props.variant   Specifies the style of the select input. The accepted values are: 'default' or 'metabox'.
 */
export default ( {
	value,
	options,
	onChange,
	className,
	disabled = false,
	variant = 'metabox',
	disabledOptions = [],
	...additionalProps
} ) => {
	className = classNames( className, 'rank-math-select-control', {
		'is-disabled': disabled,
		'is-metabox': variant === 'metabox',
	} )

	const selectOptions = map( entries( options ), ( [ key, label ] ) => ( {
		value: key,
		label,
		disabled: includes( disabledOptions, key ),
	} ) )

	const props = {
		...additionalProps,
		value,
		onChange,
		disabled,
		className,
		options: selectOptions,
		__nextUnconstrainedWidth: true,
		__next36pxDefasultSize: true,
		__next40pxDefaultSize: true,
		__nextHasNoMarginBottom: true,
	}

	return <SelectControl { ...props } />
}
