/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * WordPress dependencies
 */
import { RangeControl } from '@wordpress/components';

export default ({
	value,
	className,
	...rest
}) => {
	const getButtonClasses = () => {
		return classNames(
			'editor-score-bar',
			className,
			{
				'bad-score': value < 50,
				'average-score': value > 50 && value <= 70,
				'good-score': value > 70
			}
		);
	};

	const rangeControlProps = {
		...rest,
		value,
		className: getButtonClasses(),
		min: 0,
		max: 100,
		withInputField: false,
		showTooltip: false,
		label: `${value} / 100`
	}

	return (
		<RangeControl
			{...rangeControlProps}
		/>
	);
}