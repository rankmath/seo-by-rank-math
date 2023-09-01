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
	iconOnly = false,
	className,
	onSelect,
	initialTabName,
	...rest
}) => {
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