/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * Internal dependencies
 */
import '../../scss/CustomSelectControl.scss';

/**
 * WordPress dependencies
 */
import { CustomSelectControl } from '@wordpress/components';

export default function ({
  label,
  value,
  onChange,
  options,
  size,
  disabled,
  className,
  __nextUnconstrainedWidth = true,
  ...rest
}) {
  const getSelectControlClasses = () => {
    return classNames(
      className,
      {
        'is-disabled': disabled,
        'with-label': label
      }
    );
  };

  const selectControlProps = {
    className: getSelectControlClasses(),
    label,
    value,
    onChange,
    options,
    disabled,
    size,
    __nextUnconstrainedWidth,
    ...rest
  }

  return (
    <CustomSelectControl
      {...selectControlProps}
    />
  );
};