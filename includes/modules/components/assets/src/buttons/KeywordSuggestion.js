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
import { Button, Popover } from '@wordpress/components';
import { useState } from '@wordpress/element';

export default function ({
  severity = 'good',
  keyword = 'Keyword',
  score = '94/100',
  className,
  ...rest
}) {
  const [isCopied, setIsCopied] = useState(false);

  const getButtonClasses = () => {
    return classNames(className, severity,);
  };

  const handleCopyClick = () => {
    setIsCopied(true);
  };

  const buttonProps = {
    ...rest,
    className: getButtonClasses(),
    onClick: handleCopyClick,
    variant: 'secondary',
  }

  return (
    <div className='keyword-suggestion-button'>
      <Button {...buttonProps}>
        <h1 className='keyword-suggestion-button__keyword'>{keyword}</h1>
        <h6 className='keyword-suggestion-button__score'>{score}</h6>
      </Button>

      {isCopied && (
        <Popover
          placement="top"
          onClose={() => setIsCopied(false)}
          noArrow={false}
          offset={11}
        >
          Copied!
        </Popover>
      )}
    </div>
  )
};
