/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';

export default ({
	variant = 'primary',
	size = 'default',
	icon,
	children,
	disabled,
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
		children,
		disabled,
		...rest
	};

	return (
		<Button {...buttonProps} />
	)
}