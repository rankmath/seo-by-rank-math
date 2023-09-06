/**
 * WordPress dependencies
 */
import { RangeControl } from '@wordpress/components';
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

	const popoverStyle = {};
	popoverStyle.left = `${currentValue}%`;
	if (currentValue > 60) {
		popoverStyle.transform = 'translateX(-100%)';
	} else if (currentValue >= 50) {
		popoverStyle.transform = 'translateX(-50%)';
	}

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

			<div
				style={popoverStyle}
				role='tooltip'
				aria-hidden='true'
				className='content-ai-score-bar__tooltip'
			>
				<h1 className='content-ai-score-bar__tooltip-title'>Score</h1>
				<h6 className='content-ai-score-bar__tooltip-value'>{currentValue}/100</h6>

				<div className={`content-ai-score-bar__tooltip-pointer ${popoverPointerPlacement}`} />
			</div>
		</div>
	);
}
