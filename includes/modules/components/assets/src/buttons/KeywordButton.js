/**
 * Internal dependencies
 */
import '../../scss/keyword-button.scss'

/**
 * WordPress dependencies
 */
import { Button, Popover } from '@wordpress/components';
import { useState, useEffect, useRef } from '@wordpress/element';

export default function ({
  severity = 'good',
  keyword = 'Keyword',
  score = '94/100',
  className,
  ...rest
}) {
  const [isCopied, setIsCopied] = useState(false);
  const buttonRef = useRef(null);
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
  };

  const handleButtonBlur = () => {
    setIsCopied(false);
  };

  const buttonProps = {
    ...rest,
    className: groupedClassNames,
    onClick: handleCopyClick,
    variant: 'secondary',
    ref: buttonRef
  }

  useEffect(() => {
    const buttonElement = buttonRef.current;
    if (buttonElement) {
      buttonElement.addEventListener('blur', handleButtonBlur);
    }

    return () => {
      if (buttonElement) {
        buttonElement.removeEventListener('blur', handleButtonBlur);
      }
    };
  }, []);

  return (
    <div className='keyword-button'>
      <Button {...buttonProps}>
        <h1 className='keyword-button__keyword'>{keyword}</h1>
        <h6 className='keyword-button__score'>{score}</h6>
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
