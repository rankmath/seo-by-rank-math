/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';

export default ({
	severity = 'good',
	href = '',
	target = '_blank',
	rel = 'noopener noreferrer',
	className,
	children,
	...rest
}) => {
	const groupedClassNames = `anchor-tag-status ${className} ${severity}`;

	const buttonProps = {
		...rest,
		href,
		children,
		target,
		rel,
		className: groupedClassNames,
		variant: 'secondary',
	}

	return (
		<Button
			{...buttonProps}
		/>
	)
};
