/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * Internal dependencies
 */
import '../../../scss/search-select-control.scss';
import SearchSelectOption from './SearchSelectOption';

/**
 * WordPress dependencies
 */
import { CustomSelectControl, Disabled, SearchControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element'

export default ({
	disabled = false,
	className = 'search-select-control',
	label,
	value,
	onChange,
	options,
	size,
	...rest
}) => {
	const [searchValue, setSearchValue] = useState('');
	const filteredOptions = searchValue
		? options.filter((item) =>
			item.name.title.toLowerCase().includes(searchValue.toLowerCase())
		)
		: options;

	const finalOptions = filteredOptions.map(({ key, name }) => ({
		key,
		name: (
			<SearchSelectOption
				title={name.title}
				subTitle={name.subTitle}
				description={name.description}
			/>
		)
	}));

	const getSelectControlClasses = () => {
		return classNames(
			className,
			{
				'is-disabled': disabled,
			}
		);
	};

	const selectControlProps = {
		className: getSelectControlClasses(),
		options: finalOptions,
		label,
		value,
		onChange,
		disabled,
		size,
		__next36pxDefaultSize: true,
		__nextUnconstrainedWidth: true,
		...rest
	}

	useEffect(() => {
		const optionsContainer = document.querySelector('.components-custom-select-control__menu');

		if (optionsContainer) {
			ReactDOM.render(
				<SearchControl
					value={searchValue}
					onChange={setSearchValue}
				/>,
				optionsContainer
			);

			return () => {
				ReactDOM.unmountComponentAtNode(optionsContainer);
			};
		}
	}, [searchValue]);

	return (
		<Disabled isDisabled={disabled}>
			<CustomSelectControl
				{...selectControlProps}
			/>
		</Disabled>
	);
};

