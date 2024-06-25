/**
 * External dependencies
 */
import { filter, map, lowerCase, includes, find } from 'lodash'

/**
 * WordPress dependencies
 */
import {
	useMemo,
	useState,
	useEffect,
	ReactNode,
	Ref,
	forwardRef,
} from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { SearchControl, Button } from '@wordpress/components'

/**
 * Internal dependencies
 */
import useClickOutside from './hooks/useClickOutside'
import './scss/SelectWithSearch.scss'

/**
 * Select Control With Searchbox component.
 *
 * @param {Object}    props           Component props.
 * @param {string}    props.value     The current value selected.
 * @param {Object}    props.style     Inline style object for additional styling.
 * @param {ReactNode} props.label     The label associated with the control.
 * @param {Array}     props.options   The dropdown options.
 * @param {Function}  props.onChange  Callback invoked when the selection changes.
 * @param {string}    props.width     Sets the width of the control.
 * @param {string}    props.className CSS class for additional styling.
 * @param {boolean}   props.disabled  Whether the control is disabled.
 * @param {Ref}       ref             Ref object for accessing an instance of the component.
 */
const SelectWithSearch = ( {
	value,
	style,
	label,
	options,
	onChange,
	width = '100%',
	className = '',
	disabled = false,
	...additionalProps
}, ref ) => {
	const [ searchValue, setSearchValue ] = useState( '' )
	const [ isMenuOpen, setIsMenuOpen, containerRef ] = useClickOutside()
	const selectRef = ref ?? containerRef

	// Find the current selected option based on the provided value
	const currentValue = find( options, ( { key } ) => key === value )

	// Filter the options based on the search value
	const filteredItems = useMemo( () => {
		return filter( options, ( { name } ) =>
			includes( lowerCase( name ), lowerCase( searchValue ) )
		)
	}, [ searchValue, options ] )

	// Handle the selection of an option
	const handleSelectedOption = ( selectedValue ) => {
		onChange( selectedValue )
		setIsMenuOpen( false )
		setSearchValue( '' )
	}

	const props = {
		...additionalProps,
		ref: selectRef,
		style: { width, ...style },
		className: `rank-math-select-with-searchbox ${ className }`,
	}

	useEffect( () => {
		if ( isMenuOpen && selectRef.current ) {
			const selectedOption = selectRef.current.querySelector(
				'.select-menu ul li[aria-selected="true"]'
			)

			if ( selectedOption ) {
				// Scroll the selected option into view
				selectedOption.scrollIntoView( {
					behavior: 'auto',
					block: 'nearest',
				} )
			}
		}
	}, [ isMenuOpen ] )

	return (
		<div { ...props }>
			{ label && <label htmlFor="select-menu">{ label }</label> }

			<Button
				variant="secondary"
				disabled={ disabled }
				aria-expanded={ isMenuOpen }
				onClick={ () => setIsMenuOpen( ( prev ) => ! prev ) }
			>
				{ currentValue?.name }
			</Button>

			{ isMenuOpen && (
				<div className="select-menu">
					<SearchControl
						value={ searchValue }
						onChange={ setSearchValue }
						placeholder={ null }
						autoFocus
					/>

					{ filteredItems.length > 0 ? (
						<ul
							tabIndex="-1"
							role="listbox"
							aria-hidden="false"
							aria-expanded={ isMenuOpen }
						>
							{ map( filteredItems, ( { key, name } ) => (
								<li
									key={ key }
									role="option"
									aria-hidden="true"
									aria-selected={ currentValue.name === name }
									onClick={ () => handleSelectedOption( key ) }
								>
									{ name }
								</li>
							) ) }
						</ul>
					) : (
						<span className="no-results">
							{ __( 'No results found', 'rank-math' ) }
						</span>
					) }
				</div>
			) }
		</div>
	)
}

export default forwardRef( SelectWithSearch )
