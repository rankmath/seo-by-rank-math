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
	const groupedClassNames = `page-tab-panel__content ${className}`;

	const tabPanelProps = {
		...rest,
		className: groupedClassNames,
		tabs,
		children,
		onSelect,
		initialTabName
	}

	return (
		<div className='page-tab-panel__container'>
			<TabPanel
				{...tabPanelProps}
			/>

			<button className='page-tab-panel__button'>
				<i className='rm-icon-info'></i>
			</button>
		</div>
	)
}