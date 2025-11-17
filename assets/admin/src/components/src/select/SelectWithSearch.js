/**
 * External dependencies
 */
import { filter, map, lowerCase, includes, entries } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState, useEffect, forwardRef } from '@wordpress/element'
import { SearchControl, Button } from '@wordpress/components'

/**
 * Internal dependencies
 */
import useClickOutside from '../hooks/useClickOutside'
import './scss/SelectWithSearch.scss'

/**
 * Select Control With Searchbox component.
 *
 * @param {Object}   props           Component props.
 * @param {string}   props.value     The current value selected.
 * @param {Object}   props.style     Inline style object for additional styling.
 * @param {Node}     props.label     The label associated with the control.
 * @param {Array}    props.options   The dropdown options.
 * @param {Function} props.onChange  Callback invoked when the selection changes.
 * @param {string}   props.width     Sets the width of the control.
 * @param {string}   props.className CSS class for additional styling.
 * @param {boolean}  props.disabled  Whether the control is disabled.
 * @param {Object}   ref             Ref object for accessing an instance of the component.
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
	const [ showMenu, setShowMenu, containerRef ] = useClickOutside()

	const selectRef = ref ?? containerRef
	const selectedValue = options[ value ]

	const selectOptions = map( entries( options ), ( [ key, name ] ) => ( { key, name } ) )
	const selectMenu = filter( selectOptions, ( { name } ) =>
		includes( lowerCase( name ), lowerCase( searchValue ) )
	)

	/**
	 * Callback executed when an option is selected.
	 *
	 * @param {string} newValue The newly selected option.
	 */
	const handleSelectedOption = ( newValue ) => {
		onChange( newValue )
		setShowMenu( false )
		setSearchValue( '' )
	}

	const props = {
		...additionalProps,
		ref: selectRef,
		style: { width, ...style },
		className: `rank-math-select-with-searchbox ${ className }`,
	}

	/**
	 * Scroll the selected option into view
	 */
	useEffect( () => {
		if ( showMenu && selectRef.current ) {
			const selectedOption = selectRef.current.querySelector(
				'.select-menu ul li[aria-selected="true"]'
			)

			if ( selectedOption ) {
				selectedOption.scrollIntoView( {
					behavior: 'auto',
					block: 'nearest',
				} )
			}
		}
	}, [ showMenu ] )

	return (
		<div { ...props }>
			{ label && <label htmlFor="select-menu">{ label }</label> }

			<Button
				variant="secondary"
				disabled={ disabled }
				aria-expanded={ showMenu }
				onClick={ () => setShowMenu( ( prev ) => ! prev ) }
			>
				<span dangerouslySetInnerHTML={ { __html: selectedValue } } />
			</Button>

			{ showMenu && (
				<div className="select-menu">
					<SearchControl
						value={ searchValue }
						onChange={ setSearchValue }
						placeholder={ null }
						// eslint-disable-next-line jsx-a11y/no-autofocus
						autoFocus
						__nextHasNoMarginBottom
					/>

					{ selectMenu.length > 0 ? (
						<ul
							tabIndex="-1"
							role="listbox"
							aria-hidden="false"
							aria-expanded={ showMenu }
						>
							{ map( selectMenu, ( { key, name } ) => (
								<li
									key={ key }
									role="option"
									aria-hidden="true"
									aria-selected={ key === value }
									onClick={ () => handleSelectedOption( key ) }
								>
									<span dangerouslySetInnerHTML={ { __html: name } } />
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
