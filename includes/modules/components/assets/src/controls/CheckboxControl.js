/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * WordPress dependencies
 */
import { CheckboxControl } from '@wordpress/components';

export default ({
	__nextHasNoMarginBottom = false,
	onChange = () => { },
	indeterminate = false,
	isIndeterminate = false,
	label,
	help,
	checked,
	className,
	disabled,
	...rest
}) => {
	const getCheckboxControlClasses = () => {
		return classNames(
			className,
			{
				'is-disabled': disabled,
				'is-indeterminate': isIndeterminate,
			}
		);
	};

	const checkboxControlProps = {
		...rest,
		className: getCheckboxControlClasses(),
		__nextHasNoMarginBottom,
		onChange,
		indeterminate,
		label,
		help,
		checked,
		disabled
	}

	return (
		<CheckboxControl
			{...checkboxControlProps}
		/>
	);
};