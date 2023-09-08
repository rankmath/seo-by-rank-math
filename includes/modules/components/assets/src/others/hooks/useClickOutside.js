/**
 * WordPress dependencies
 */
import { useRef, useEffect, useState } from '@wordpress/element';

export default () => {
	const [isMenuOpen, setIsMenuOpen] = useState(false);
	const menuRef = useRef(null);

	useEffect(() => {
		const handleClickOutside = (event) => {
			if (menuRef.current && !menuRef.current.contains(event.target)) {
				setIsMenuOpen(false);
			}
		};

		if (isMenuOpen) {
			document.addEventListener('click', handleClickOutside);
		} else {
			document.removeEventListener('click', handleClickOutside);
		}

		return () => {
			document.removeEventListener('click', handleClickOutside);
		};
	}, [isMenuOpen]);

	return [menuRef, isMenuOpen, setIsMenuOpen];
}
