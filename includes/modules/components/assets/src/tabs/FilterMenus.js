/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * Internal dependencies
 */
import '../../scss/FilterMenus.scss';

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
  onSelect,
  initialTabName,
  ...rest
}) {
  const getTabPanelClasses = () => {
    return classNames(
      className,
      'filter-menus',
      {
        'is-black': variant === 'black',
        'is-blue': variant === 'blue',
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