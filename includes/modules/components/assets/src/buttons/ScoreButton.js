/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';

export default ({
	company = 'Rank Math',
	severity = 'good',
	className,
	...rest
}) => {
	const getScoreButtonClasses = () => {
		return classNames(
			'score-button',
			className,
			severity,
			{
				'rank-math': company === 'Rank Math',
				'content-ai': company === 'Content AI',
			}
		);
	};

	const companyIconMap = {
		'Content AI': 'rm-icon-rank-math',
		'Rank Math': 'rm-icon-rank-math',
	};

	const iconName = companyIconMap[company] || '';

	const buttonProps = {
		...rest,
		className: getScoreButtonClasses(),
		variant: 'secondary',
		icon: <i className={iconName}></i>
	}

	return (
		<Button
			{...buttonProps}
		/>
	)
};