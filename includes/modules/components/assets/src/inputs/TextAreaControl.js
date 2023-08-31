/**
 * Internal dependencies
 */
import '../../scss/text-control.scss';

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
		onChange,
		value,
		help,
		hideLabelFromVision,
		label,
		placeholder,
		disabled,
		rows,
		...rest
	}

	return (
		<div className="text-control-container">
			<TextareaControl
				{...textControlProps}
			/>
		</div>
	);
};