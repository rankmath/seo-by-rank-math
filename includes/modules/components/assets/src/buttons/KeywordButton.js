/**
 * External dependencies
 */
import classNames from 'classnames'

/**
 * WordPress dependencies
 */
import { Button, Popover } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { useCopyToClipboard } from '@wordpress/compose';

export default ({
	keyword,
	score,
	className,
	...rest
}) => {
	const [isCopied, setIsCopied] = useState(false);
	const onSuccess = () => {
		setIsCopied(true);

		setTimeout(() => {
			setIsCopied(false);
		}, 2000);
	};

	const copyToClipboardRef = useCopyToClipboard(keyword, onSuccess);

	const getButtonClasses = () => {
		return classNames(
			className,
			{
				'good': score >= 70,
				'neutral': score >= 50 && score < 70,
				'bad': score <= 30
			}
		);
	};

	const buttonProps = {
		...rest,
		className: getButtonClasses(),
		ref: copyToClipboardRef,
		variant: 'secondary'
	}

	return (
		<div className='keyword-button'>
			<Button {...buttonProps}>
				<h1 className='keyword-button__keyword'>{keyword}</h1>
				<h6 className='keyword-button__score'>{score}/100</h6>
			</Button>

			{isCopied && (
				<Popover
					placement="top"
					noArrow={false}
					offset={11}
				>
					Copied!
				</Popover>
			)}
		</div>
	)
};
