/**
 * Internal dependencies
 */
import classNames from 'classnames';

/**
 * WordPress dependencies
 */
import { __experimentalNumberControl as NumberControl } from '@wordpress/components';

export default ({
	disabled = false,
	className,
	value,
	onChange,
	label,
	placeholder,
	min,
	max,
	step,
	...rest
}) => {
	const getNumberControlClasses = () => {
		return classNames(
			className,
			'number-control-container',
			{
				'is-disabled': disabled
			}
		);
	};

	const numberControlProps = {
		...rest,
		spinControls: 'custom',
		className: getNumberControlClasses(),
		value,
		onChange,
		label,
		placeholder,
		disabled,
		min,
		max,
		step,
	}

	return (
		<NumberControl
			{...numberControlProps}
		/>
	)
}