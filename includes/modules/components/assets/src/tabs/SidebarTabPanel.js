/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * Internal dependencies
 */
import '../../scss/SidebarTabPanel.scss';

/**
 * WordPress dependencies
 */
import { TabPanel } from '@wordpress/components';

export default function ({
  activeClass = 'is-active',
  orientation = 'horizontal',
  selectOnMove = true,
  tabs = [],
  children,
  onSelect,
  className,
  initialTabName,
  ...rest
}) {
  const tabPanelProps = {
    activeClass,
    orientation,
    selectOnMove,
    children,
    tabs,
    onSelect,
    className,
    initialTabName,
    ...rest
  }

  return (
    <TabPanel
      {...tabPanelProps}
    />
  )
}