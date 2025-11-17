/**
 * External dependencies
 */
import classNames from 'classnames'

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components'
import { forwardRef } from '@wordpress/element'

/**
 * Internal dependencies
 */
import './scss/Button.scss'

/**
 * Button component.
 *
 * @param {Object}   props           Component props.
 * @param {*}        props.icon      If provided, renders an icon inside the button.
 * @param {Function} props.onClick   Callback invoked when the button is clicked.
 * @param {string}   props.children  Child elements to be rendered inside the button.
 * @param {boolean}  props.disabled  Whether the button is disabled.
 * @param {string}   props.className CSS class for additional styling.
 * @param {string}   props.size      The size of the button.
 * @param {string}   props.variant   Specifies the button's style. Accepted values: 'primary', 'secondary', 'primary-outline', 'remove-group', 'green' or 'start-new-chat'.
 * @param {Object}   ref             Ref object for accessing an instance of the component.
 */
const RankMathButton = ( {
	icon,
	variant,
	onClick,
	children,
	disabled,
	className,
	size = 'default',
	...additionalProps
}, ref ) => {
	className = classNames(
		'button',
		className,
		'rank-math-button',
		variant ? `button-${ variant }` : '',
		{
			'is-xlarge': size === 'xlarge',
			'is-large': size === 'large',
			'button-secondary': variant === 'remove-group',
			'button-primary': variant === 'start-new-chat',
		}
	)

	const props = {
		...additionalProps,
		ref,
		size,
		icon,
		variant,
		onClick,
		children,
		disabled,
		className,
		'aria-disabled': disabled,
	}

	return <Button { ...props } />
}

export default forwardRef( RankMathButton )
