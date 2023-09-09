/**
 * WordPress dependencies
 */
import { TextareaControl } from '@wordpress/components';

export default ({
	onChange,
	value,
	rows,
	label,
	help,
	hideLabelFromVision,
	placeholder,
	disabled,
	...rest
}) => {
	const textControlProps = {
		...rest,
		onChange,
		value,
		rows,
		label,
		help,
		hideLabelFromVision,
		placeholder,
		disabled
	}

	return (
		<div className="text-control-container">
			<TextareaControl
				{...textControlProps}
			/>
		</div>
	);
};