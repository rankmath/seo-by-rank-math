/**
 * WordPress dependencies
 */
import { ToggleControl } from '@wordpress/components';


export default ({
	onChange = () => { },
	__nextHasNoMarginBottom = true,
	label,
	checked,
	disabled,
	help,
	className,
	...rest
}) => {
	const toggleControlProps = {
		...rest,
		onChange,
		label,
		checked,
		disabled,
		help,
		className,
		__nextHasNoMarginBottom,
	}

	return (
		<ToggleControl
			{...toggleControlProps}
		/>
	);
};