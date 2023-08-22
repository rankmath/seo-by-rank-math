/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * Internal dependencies
 */
import '../../scss/keyword-suggestion.scss'

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { useState } from '@wordpress/element';

export default function ({
  severity = 'good',
  keyword = 'Keyword',
  score = '94/100',
  className,
  ...rest
}) {
  const [isCopied, setIsCopied] = useState(true);

  const getButtonClasses = () => {
    return classNames(
      'keyword-suggestion-button',
      className,
      severity,
    );
  };

  const keywordElement = <h1 className='keyword-suggestion-button__keyword'>{keyword}</h1>;
  const scoreElement = <h6 className='keyword-suggestion-button__score'>{score}</h6>;

  const handleCopyClick = () => {
    if (!isCopied) {
      const combinedText = `${keyword} ${score}`;

      if (navigator.clipboard) {
        navigator.clipboard.writeText(combinedText)
          .catch(err => {
            console.error('Failed to copy text:', err);
          });
      } else {
        const tempElement = document.createElement('textarea');
        tempElement.value = combinedText;
        document.body.appendChild(tempElement);
        tempElement.select();
        document.execCommand('copy');
        document.body.removeChild(tempElement);
      }
    }
  };

  const buttonProps = {
    ...rest,
    className: getButtonClasses(),
    onClick: handleCopyClick,
    variant: 'secondary',
    children: (
      <>
        {keywordElement}
        {scoreElement}
      </>
    ),
    ...(isCopied
      ? {
        label: 'Copied!',
        showTooltip: true,
        tooltipPosition: "top center",
      }
      : {})
  }

  return (
    <Button
      {...buttonProps}
    />
  )
};
