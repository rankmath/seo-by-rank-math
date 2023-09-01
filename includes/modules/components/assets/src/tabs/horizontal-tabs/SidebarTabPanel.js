/**
 * WordPress dependencies
 */
import { TabPanel } from '@wordpress/components';

export default ({
	tabs = [],
	children = () => { },
	className,
	onSelect,
	initialTabName,
	...rest
}) => {
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