/**
 * External dependencies
 */
import {
	map,
	filter,
	lowerCase,
	includes,
	find,
	isEmpty,
	debounce,
} from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState, useEffect, forwardRef } from '@wordpress/element'
import apiFetch from '@wordpress/api-fetch'
import { SearchControl, Button, Icon } from '@wordpress/components'
import { closeSmall } from '@wordpress/icons'

/**
 * Internal dependencies
 */
import useClickOutside from '../hooks/useClickOutside'
import './scss/SelectWithSearch.scss'

/**
 * Select Control with an option to search the page.
 *
 * @param {Object}   props              Component props.
 * @param {string}   props.value        The current value selected.
 * @param {Object}   props.style        Inline style object for additional styling.
 * @param {Node}     props.label        The label associated with the control.
 * @param {Function} props.onChange     Callback invoked when the selection changes.
 * @param {string}   props.width        Sets the width of the control.
 * @param {string}   props.className    CSS class for additional styling.
 * @param {boolean}  props.disabled     Whether the control is disabled.
 * @param {boolean}  props.selectedPage Selected Page data.
 * @param {Object}   ref                Ref object for accessing an instance of the component.
 */
const SearchPage = (
	{
		value,
		style,
		label,
		onChange,
		width = '100%',
		className = '',
		disabled = false,
		selectedPage = null,
		...additionalProps
	},
	ref
) => {
	const [ searchValue, setSearchValue ] = useState( '' )
	const [ options, setOptions ] = useState( [] )
	const [ selected, setSelected ] = useState( selectedPage )
	const [ showMenu, setShowMenu, containerRef ] = useClickOutside()

	const selectRef = ref ?? containerRef

	// Fetch pages from server
	useEffect( () => {
		if ( searchValue.length < 2 ) {
			setOptions( [] )
			return
		}

		const fetchPages = debounce( ( term ) => {
			apiFetch( {
				path: `/rankmath/v1/searchPage?searchedTerm=${ encodeURIComponent( term ) }`,
			} )
				.then( ( response ) => {
					if ( response?.results ) {
						const fetched = map( response.results, ( { id, text, url } ) => ( {
							id: String( id ),
							name: text,
							url,
						} ) )
						setOptions( fetched )
					} else {
						setOptions( [] )
					}
				} )
				.catch( ( error ) => {
					if ( error.name !== 'AbortError' ) {
						console.error( __( 'Search fetch failed:', 'rank-math' ), error )
					}
				} )
		}, 300 )

		fetchPages( searchValue )

		// Cancel debounce on unmount or when searchValue changes
		return () => {
			fetchPages.cancel()
		}
	}, [ searchValue ] )

	const handleSelectedOption = ( id ) => {
		const option = find( options, { id } )
		if ( option ) {
			onChange( id )
			setSelected( option ) // <-- full object (with url)
		}

		setShowMenu( false )
		setSearchValue( '' )
		setOptions( [] )
	}

	const handleClearSelection = () => {
		onChange( '' )
		setSelected( null )
		setShowMenu( false )
		setSearchValue( '' )
		setOptions( [] )
	}

	const filteredOptions = filter( options, ( { name } ) =>
		includes( lowerCase( name ), lowerCase( searchValue ) )
	)

	const dropdownContent = () => {
		if ( searchValue.length < 2 ) {
			return (
				<span className="no-results">
					{ __( 'Please enter 2 or more characters', 'rank-math' ) }
				</span>
			)
		}

		if ( isEmpty( filteredOptions ) ) {
			return (
				<span className="no-results">
					{ __( 'No results found', 'rank-math' ) }
				</span>
			)
		}

		return (
			<ul
				tabIndex="-1"
				role="listbox"
				aria-hidden="false"
				aria-expanded={ showMenu }
			>
				{ map( filteredOptions, ( { id, name } ) => (
					<li
						key={ id }
						role="option"
						aria-selected={ id === value }
						onClick={ () => handleSelectedOption( id ) }
						onKeyDown={ undefined }
					>
						<span dangerouslySetInnerHTML={ { __html: name } } />
					</li>
				) ) }
			</ul>
		)
	}

	return (
		<div
			{ ...additionalProps }
			ref={ selectRef }
			style={ { width, ...style } }
			className={ `rank-math-select-with-searchbox ${ className }` }
		>
			{ label && <label htmlFor="select-menu">{ label }</label> }

			<Button
				className="select-toggle"
				variant="secondary"
				disabled={ disabled }
				aria-expanded={ showMenu }
				onClick={ () => setShowMenu( ( prev ) => ! prev ) }
			>
				<span
					className="select-label"
					dangerouslySetInnerHTML={ {
						__html: selected?.name || __( 'Select Page', 'rank-math' ),
					} }
				/>
				{ !! selected && (
					<span
						className="clear-selection-icon"
						role="button"
						aria-label={ __( 'Clear selection', 'rank-math' ) }
						tabIndex={ 0 }
						onMouseDown={ ( e ) => e.preventDefault() }
						onClick={ ( e ) => {
							e.stopPropagation()
							handleClearSelection()
						} }
						onKeyDown={ ( e ) => {
							if ( e.key === 'Enter' || e.key === ' ' ) {
								e.preventDefault()
								e.stopPropagation()
								handleClearSelection()
							}
						} }
					>
						<Icon icon={ closeSmall } size={ 4 } />
					</span>
				) }
			</Button>

			{ showMenu && (
				<div className="select-menu">
					<SearchControl
						value={ searchValue }
						onChange={ setSearchValue }
						placeholder={ __( 'Select New Page…', 'rank-math' ) }
						// eslint-disable-next-line jsx-a11y/no-autofocus
						autoFocus
					/>

					{ dropdownContent() }
				</div>
			) }
			{ selected?.url && (
				<div className="selected-page-link">
					<br />
					{ __( 'Selected Page:', 'rank-math' ) }{ ' ' }
					<a href={ selected.url } target="_blank" rel="noopener noreferrer">
						{ selected.url }
					</a>
				</div>
			) }
		</div>
	)
}

export default forwardRef( SearchPage )
