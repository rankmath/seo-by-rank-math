/**
 * Internal dependencies
 */
import '../../scss/keyword-button.scss'

/**
 * WordPress dependencies
 */
import { Button, Popover } from '@wordpress/components';
import { useState } from '@wordpress/element';

export default ({
	severity = 'good',
	keyword = 'Keyword',
	score = '94/100',
	className,
	...rest
}) => {
	const [isCopied, setIsCopied] = useState(false);
	const groupedClassNames = `${className} ${severity}`;

	const handleCopyClick = () => {
		const textToCopy = keyword;

		// Creates a range to select the text
		const range = document.createRange();
		const textElement = document.createElement('div');
		textElement.innerText = textToCopy;
		document.body.appendChild(textElement);
		range.selectNode(textElement);

		// Selects the text and copies it
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
		className: groupedClassNames,
		onClick: handleCopyClick,
		variant: 'secondary',
	}

	return (
		<div className='keyword-button'>
			<Button {...buttonProps}>
				<h1 className='keyword-button__keyword'>{keyword}</h1>
				<h6 className='keyword-button__score'>{score}</h6>
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
