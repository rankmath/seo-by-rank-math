/**
 * WordPress dependencies
 */
import { RangeControl, Popover } from '@wordpress/components';
import { useState } from '@wordpress/element';

export default ({
	value,
	className,
	...rest
}) => {
	const [currentValue, setCurrentValue] = useState(value)
	const popoverPointerPlacement = currentValue < 50 ? 'is-left' : currentValue > 60 ? 'is-right' : '';
	const groupedClassNames = `content-ai-score-bar ${className}`;

	const handleSliderChange = (value) => {
		setCurrentValue(value);
	};

	const rangeControlProps = {
		...rest,
		value: currentValue,
		onChange: handleSliderChange,
		className: groupedClassNames,
		min: 0,
		max: 100,
		withInputField: false,
		showTooltip: false,
	}

	return (
		<div className='content-ai-score-bar'>
			<RangeControl
				{...rangeControlProps}
			/>

			<Popover
				placement={'top'}
				noArrow={false}
				offset={4}

			>
				<h1 className='score-bar__title'>Score</h1>
				<h6 className='score-bar__value'>{currentValue}/100</h6>

				<div className={`popover-pointer ${popoverPointerPlacement}`} />
			</Popover>
		</div>
	);
}
