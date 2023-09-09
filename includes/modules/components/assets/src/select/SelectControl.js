/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * Internal dependencies
 */
import SelectControlSearchOption from './SelectControlSearchOption';

/**
 * WordPress dependencies
 */
import { CustomSelectControl, Disabled, SearchControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';

export default ({
	disabled = false,
	className,
	options,
	value,
	onChange,
	label,
	size,
	withSearch,
	...rest
}) => {
	const [searchValue, setSearchValue] = useState('');
	const filterOptions = withSearch && options.filter((item) =>
		item.name.title.toLowerCase().includes(searchValue.toLowerCase())
	);
	const filteredOptionsResult = searchValue ? filterOptions : options;

	const finalFilteredOptions = filteredOptionsResult.map(({ key, name }) => ({
		key,
		name: (
			<SelectControlSearchOption
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
				'with-label': label,
				'select-with-search': withSearch,
			}
		);
	};

	useEffect(() => {
		const searchSelectControl = document.querySelector('.select-with-search');

		if (searchSelectControl) {
			const optionsContainer = searchSelectControl.querySelector('.components-custom-select-control__menu');

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
		}
	}, [searchValue]);

	const selectControlProps = {
		...rest,
		className: getSelectControlClasses(),
		options: withSearch ? finalFilteredOptions : options,
		__next36pxDefaultSize: true,
		__nextUnconstrainedWidth: true,
		label,
		value,
		onChange,
		disabled,
		size,
	}

	return (
		<Disabled isDisabled={disabled}>
			<CustomSelectControl
				{...selectControlProps}
			/>
		</Disabled>
	);
};