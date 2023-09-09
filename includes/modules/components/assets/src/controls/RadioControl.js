/**
 * WordPress dependencies
 */
import { RadioControl } from '@wordpress/components';

export default ({
	hideLabelFromVision = false,
	onChange = () => { },
	label,
	help,
	selected,
	options,
	...rest
}) => {
	const radioControlProps = {
		...rest,
		hideLabelFromVision,
		onChange,
		label,
		help,
		selected,
		options
	}

	return (
		<RadioControl
			{...radioControlProps}
		/>
	);
};