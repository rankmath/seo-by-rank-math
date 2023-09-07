/**
 * Internal dependencies
 */
import SelectControlSearchOption from './SelectControlSearchOption';

/**
 * WordPress dependencies
 */
import { SearchControl, Disabled, Button } from '@wordpress/components';
import { useState, useEffect, useRef } from '@wordpress/element';

export default ({ options, value, onChange, disabled = false }) => {
	const [isMenuOpen, setIsMenuOpen] = useState(false);
	const [searchValue, setSearchValue] = useState('');
	const dropdownRef = useRef(null);

	const toggleDropdown = () => {
		setIsMenuOpen(!isMenuOpen);
	};

	const handleSelectedOption = (value) => {
		onChange(value);
		setIsMenuOpen(false);
		setSearchValue('');
	};

	const filteredItems = () => {
		return options.filter((item) =>
			item.title.toLowerCase().includes(searchValue.toLowerCase())
		);
	};

	const closeIfClickedOutside = (e) => {
		if (
			dropdownRef.current &&
			!dropdownRef.current.contains(e.target) &&
			isMenuOpen
		) {
			setIsMenuOpen(false);
		}
	};

	useEffect(() => {
		document.addEventListener('click', closeIfClickedOutside);

		return () => {
			document.removeEventListener('click', closeIfClickedOutside);
		};
	}, []);

	return (
		<Disabled isDisabled={disabled}>
			<div className="search-select">
				<Button
					onClick={toggleDropdown}
					variant='secondary'
					className="search-select__button"
					aria-expanded={isMenuOpen ? 'true' : 'false'}
				>
					<span>{value}</span>

					<span className='search-select__button-icon'></span>
				</Button>

				{isMenuOpen && (
					<ul className='search-select__menu' ref={dropdownRef}>
						<SearchControl
							value={searchValue}
							onChange={setSearchValue}
							className="search-select__menu-search"
						/>
						{filteredItems().map(({ title, subTitle, description }) => (
							<li
								key={title}
								data-value={title}
								onClick={() => handleSelectedOption(title)}
								className="search-select__menu-item"
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
		</Disabled>
	);
}
