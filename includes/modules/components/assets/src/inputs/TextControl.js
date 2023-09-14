/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * WordPress dependencies
 */
import { TextControl } from '@wordpress/components';

export default ({
	type = 'text',
	className = '',
	placeholder,
	onChange,
	value,
	help,
	hideLabelFromVision,
	label,
	disabled,
	isSuccess,
	isError,
	...rest
}) => {
	const getTextControlClasses = () => {
		return classNames(
			className,
			{
				'is-success': isSuccess && !isError,
				'is-error': isError && !isSuccess
			}
		);
	};

	const textControlProps = {
		...rest,
		className: getTextControlClasses(),
		type,
		onChange,
		value,
		help,
		hideLabelFromVision,
		label,
		placeholder,
		disabled
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
		</div>
	)
}