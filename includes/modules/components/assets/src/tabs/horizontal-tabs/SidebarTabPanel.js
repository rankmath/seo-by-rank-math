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
	const groupedClassNames = `sidebar-tab-panel ${className}`;

	const tabPanelProps = {
		...rest,
		className: groupedClassNames,
		tabs,
		children,
		onSelect,
		initialTabName
	}

	return (
		<TabPanel
			{...tabPanelProps}
		/>
	)
}