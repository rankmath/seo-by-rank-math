/**
 * Internal dependencies
 */
import '../../../scss/SidebarTabPanel.scss';

/**
 * WordPress dependencies
 */
import { TabPanel } from '@wordpress/components';

export default function ({
  tabs = [],
  children = () => { },
  className,
  onSelect,
  initialTabName,
  ...rest
}) {
  const tabPanelProps = {
    className: `sidebar-tab-panel ${className}`,
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