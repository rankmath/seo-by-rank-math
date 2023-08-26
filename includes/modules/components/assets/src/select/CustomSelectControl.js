/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * Internal dependencies
 */
import '../../scss/custom-select-control.scss';

/**
 * WordPress dependencies
 */
import { CustomSelectControl, Disabled } from '@wordpress/components';

export default ({
  label,
  value,
  onChange,
  options,
  size,
  disabled = false,
  className,
  ...rest
}) => {
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
    __next36pxDefaultSize: true,
    __nextUnconstrainedWidth: true,
    ...rest
  }

  return (
    <Disabled isDisabled={disabled}>
      <CustomSelectControl
        {...selectControlProps}
      />
    </Disabled>
  );
};