/**
 * Internal dependencies
 */
import '../../scss/content-ai-score-bar.scss';

/**
 * WordPress dependencies
 */
import { RangeControl, Popover } from '@wordpress/components';
import { useState } from '@wordpress/element';

export default function ({
  value,
  className,
  ...rest
}) {
  const [currentValue, setCurrentValue] = useState(value)
  const groupedClassNames = `content-ai-score-bar ${className}`;

  const handleValueChange = (newValue) => {
    setCurrentValue(newValue);
  };

  const rangeControlProps = {
    ...rest,
    value: currentValue,
    onChange: handleValueChange,
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
        placement='top'
        noArrow={false}
      >
        <h1 className='score-bar__title'>Score</h1>
        <h6 className='score-bar__value'>{currentValue}/100</h6>
      </Popover>
    </div>
  );
}
