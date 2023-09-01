/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * WordPress dependencies
 */
import { TabPanel } from '@wordpress/components';

export default ({
	tabs = [],
	children = () => { },
	variant = 'black',
	className,
	onSelect,
	initialTabName,
	...rest
}) => {
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