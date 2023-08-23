/**
 * Internal dependencies
 */
import '../../scss/content-ai-score-bar.scss';

/**
 * WordPress dependencies
 */
import { RangeControl } from '@wordpress/components';

export default function ({
  value,
  className,
  ...rest
}) {
  const groupedClassNames = `content-ai-score-bar ${className}`;

  const rangeControlProps = {
    ...rest,
    value,
    className: groupedClassNames,
    min: 0,
    max: 100,
    withInputField: false,
  }

  return (
    <RangeControl
      {...rangeControlProps}
    />
  );
}

// renderTooltipContent