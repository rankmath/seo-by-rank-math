/**
 * External dependencies
 */
import classNames from 'classnames'

/**
 * WordPress dependencies
 */
import { Button, Popover } from '@wordpress/components';
import { useState } from '@wordpress/element';

export default ({
	keyword,
	score,
	className,
	...rest
}) => {
	const [isCopied, setIsCopied] = useState(false);

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

	const handleCopyClick = () => {
		const textToCopy = keyword;

		// Creates range to select text
		const range = document.createRange();
		const textElement = document.createElement('div');
		textElement.innerText = textToCopy;
		document.body.appendChild(textElement);
		range.selectNode(textElement);

		// Select and copy text
		window.getSelection().removeAllRanges();
		window.getSelection().addRange(range);
		document.execCommand('copy');

		// Clean up
		window.getSelection().removeAllRanges();
		document.body.removeChild(textElement);

		setIsCopied(true);

		setTimeout(() => {
			setIsCopied(false);
		}, 2000);
	};

	const buttonProps = {
		...rest,
		className: getButtonClasses(),
		onClick: handleCopyClick,
		variant: 'secondary',
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
