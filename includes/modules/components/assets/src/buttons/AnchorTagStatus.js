/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';

export default ({
	severity = 'good',
	href = '',
	className,
	children,
	...rest
}) => {
	const groupedClassNames = `anchor-tag-status ${className} ${severity}`;

	const buttonProps = {
		...rest,
		target: '_blank',
		rel: 'noopener noreferrer',
		className: groupedClassNames,
		variant: 'secondary',
		href,
		children,
	}

	return (
		<Button
			{...buttonProps}
		/>
	)
};
