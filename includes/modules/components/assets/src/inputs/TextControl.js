/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * Internal dependencies
 */
import '../../scss/text-control.scss';

/**
 * WordPress dependencies
 */
import { TextControl, Disabled } from '@wordpress/components';
import { useRef } from '@wordpress/element';

export default ({
	type = 'text',
	placeholder,
	onChange,
	value,
	help,
	hideLabelFromVision,
	label,
	className = '',
	disabled,
	isSuccess,
	isError,
	...rest
}) => {
	const inputRef = useRef(null);

	const handleIncrement = () => {
		if (inputRef.current) {
			inputRef.current.stepUp();
		}
	};

	const handleDecrement = () => {
		if (inputRef.current) {
			inputRef.current.stepDown();
		}
	};

	const getTextControlClasses = () => {
		return classNames(
			className,
			{
				'is-success': isSuccess && !isError,
				'is-error': isError && !isSuccess,
				'hide-default-number-controls': type === 'number'
			}
		);
	};

	const textControlProps = {
		className: getTextControlClasses(),
		ref: inputRef,
		type,
		onChange,
		value,
		help,
		hideLabelFromVision,
		label,
		placeholder,
		disabled,
		...rest
	}

	return (
		<div className='text-control-container'>
			<TextControl
				{...textControlProps}
			/>

			{(isSuccess && !isError) &&
				<div className='text-control-icon'>
					<i className="rm-icon-tick text-control-icon__validation is-success"></i>
				</div>
			}

			{(isError && !isSuccess) &&
				<div className='text-control-icon'>
					<i className="rm-icon-trash text-control-icon__validation is-error"></i>
				</div>
			}

			{(type === 'number') && (
				<div className='text-control-icon custom-number-control'>
					<i
						onClick={handleIncrement}
						className={`rm-icon-plus custom-number-control__icon ${disabled ? 'is-disabled' : ''}`}>
					</i>

					<i
						onClick={handleDecrement}
						className={`rm-icon-trash custom-number-control__icon ${disabled ? 'is-disabled' : ''}`}>
					</i>
				</div>
			)}
		</div>
	)
}