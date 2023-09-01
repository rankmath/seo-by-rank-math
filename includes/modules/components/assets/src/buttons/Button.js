/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';

export default ({
	children,
	variant = 'primary',
	size = 'default',
	disabled = false,
	describedBy = '',
	focus = false,
	isBusy = false,
	isDestructive = false,
	isPressed = false,
	label = '',
	showTooltip = false,
	shortcut,
	tooltipPosition,
	href,
	target,
	text,
	icon,
	iconPosition,
	iconSize,
	className,
	...rest
}) => {
	const variantClassMap = {
		'primary-outline': 'is-primary-outline',
		'secondary-grey': 'is-secondary-grey',
		'tertiary-outline': 'is-tertiary-outline',
	};

	const getButtonClasses = () => {
		return classNames(
			className,
			{
				'is-large': size === 'large',
				[variantClassMap[variant]]: variantClassMap[variant],
				tertiary: variant === 'tertiary'
			}
		);
	};

	const buttonProps = {
		variant: variantClassMap[variant] ? 'secondary' : variant,
		'aria-disabled': disabled,
		className: getButtonClasses(),
		...(icon ? { icon: <i className={icon}></i> } : {}),
		size,
		disabled,
		describedBy,
		focus,
		isBusy,
		isDestructive,
		isPressed,
		label,
		showTooltip,
		shortcut,
		href,
		target,
		text,
		tooltipPosition,
		iconPosition,
		iconSize,
		children,
		...rest
	};

	return (
		<Button {...buttonProps} />
	)
}