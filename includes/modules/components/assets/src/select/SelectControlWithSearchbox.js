/**
 * Internal dependencies
 */
import SelectControlSearchOption from './SelectControlSearchOption';

/**
 * WordPress dependencies
 */
import { SearchControl, Button } from '@wordpress/components';
import { useState, useMemo } from '@wordpress/element';
import useClickOutside from '../others/hooks/useClickOutside';

export default ({
	options,
	value,
	onChange,
	disabled = false
}) => {
	const [searchValue, setSearchValue] = useState('');
	const [menuRef, isMenuOpen, setIsMenuOpen] = useClickOutside();

	const filteredItems = useMemo(() => {
		return options.filter((item) =>
			item.title.toLowerCase().includes(searchValue.toLowerCase())
		);
	}, [searchValue, options]);

	const toggleDropdown = () => {
		setIsMenuOpen(!isMenuOpen);
	};

	const handleSelectedOption = (value) => {
		onChange(value);
		setIsMenuOpen(false);
		setSearchValue('');
	};

	return (
		<div
			className="select-with-search"
			aria-disabled={disabled ? true : false}
		>
			<Button
				onClick={toggleDropdown}
				variant='secondary'
				className="select-with-search__button"
				aria-expanded={isMenuOpen ? true : false}
			>
				<span>{value}</span>

				<span className='select-with-search__button-icon'></span>
			</Button>

			{isMenuOpen && (
				<ul
					className='select-with-search__menu'
					ref={menuRef}
				>
					<SearchControl
						value={searchValue}
						onChange={setSearchValue}
						className="select-with-search__menu-search"
					/>
					{filteredItems.map(({ title, subTitle, description }, index) => (
						<li
							key={`${index}-${title}`}
							data-value={title}
							onClick={() => handleSelectedOption(title)}
							className="select-with-search__menu-item"
						>
							<SelectControlSearchOption
								title={title}
								subTitle={subTitle}
								description={description}
							/>
						</li>
					))}
				</ul>
			)}
		</div>
	);
}
