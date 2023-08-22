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
  tabs = [],
  children = () => { },
  variant = 'black',
  className,
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
    tabs,
    children,
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