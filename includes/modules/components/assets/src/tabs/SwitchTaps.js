/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * Internal dependencies
 */
import '../../scss/SwitchTaps.scss';

/**
 * WordPress dependencies
 */
import { TabPanel } from '@wordpress/components';

export default function ({
  activeClass = 'is-active',
  orientation = 'horizontal',
  selectOnMove = true,
  tabs = [],
  children = () => { },
  className,
  variant = 'black',
  iconOnly = false,
  onSelect,
  initialTabName,
  ...rest
}) {
  const getTabPanelClasses = () => {
    return classNames(
      className,
      'switch-taps',
      {
        'is-black': variant === 'black',
        'is-blue': variant === 'blue',
        'icon-only': iconOnly
      }
    );
  };

  const tabPanelProps = {
    className: getTabPanelClasses(),
    activeClass,
    orientation,
    selectOnMove,
    children,
    tabs,
    onSelect,
    initialTabName,
    ...rest
  }

  return (
    <TabPanel
      {...tabPanelProps}
    />
  )
}