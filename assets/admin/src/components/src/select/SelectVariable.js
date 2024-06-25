/**
 * External dependencies
 */
import classNames from 'classnames'
import { filter, map, lowerCase, includes } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { SearchControl, Icon } from '@wordpress/components'
import { useMemo, useState, ReactNode, Ref, RawHTML, forwardRef } from '@wordpress/element'

/**
 * Internal dependencies
 */
import Button from '../buttons/Button'
import TextControl from '../inputs/TextControl'
import TextareaControl from '../inputs/TextareaControl'
import useClickOutside from './hooks/useClickOutside'
import './scss/SelectVariable.scss'

/**
 * Select Variable component.
 *
 * @param {Object}    props           Component props.
 * @param {string}    props.value     The current value selected.
 * @param {ReactNode} props.label     The label associated with the control.
 * @param {Object}    props.style     Inline style object for additional styling.
 * @param {Array}     props.options   The dropdown options.
 * @param {Function}  props.onChange  Callback invoked when the selection changes.
 * @param {string}    props.width     Sets the width of the control.
 * @param {string}    props.className CSS class for additional styling.
 * @param {boolean}   props.disabled  Whether the control is disabled.
 * @param {string}    props.as        Whether to render the input or textarea variant. Accepted value: 'textarea'.
 * @param {Ref}       ref             Ref object for accessing an instance of the component.
 */
const SelectVariable = ( {
	as,
	value,
	label,
	style,
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

	const menuClasses = classNames( 'select-menu', {
		'is-textarea-menu': as === 'textarea',
	} )

	// Filter the options based on the search value
	const filteredItems = useMemo( () => {
		const matchValue = lowerCase( searchValue )

		return filter(
			options,
			( { name, variable, description } ) =>
				includes( lowerCase( name ), matchValue ) ||
				includes( lowerCase( variable ), matchValue ) ||
				includes( lowerCase( description ), matchValue )
		)
	}, [ searchValue, options ] )

	// Handle the selection of an option
	const handleSelectedOption = ( selectedValue ) => {
		onChange( `${ value } ${ selectedValue }` )
		setIsMenuOpen( false )
		setSearchValue( '' )
	}

	const sharedInputProps = {
		value,
		onChange,
		disabled,
	}

	const props = {
		...additionalProps,
		ref: selectRef,
		style: { width, ...style },
		'aria-disabled': disabled,
		className: `rank-math-select-variable ${ className }`,
	}

	return (
		<div { ...props }>
			<div className="select-input" aria-expanded={ isMenuOpen }>
				{ as === 'textarea' ? (
					<TextareaControl
						rows={ 2 }
						variant="metabox"
						{ ...sharedInputProps }
					/>
				) : (
					<TextControl
						variant="regular-text"
						{ ...sharedInputProps }
					/>
				) }

				<Button
					variant="secondary"
					disabled={ disabled }
					icon={ <Icon icon="arrow-down-alt2" /> }
					onClick={ () => setIsMenuOpen( ( prev ) => ! prev ) }
				/>
			</div>

			{ isMenuOpen && (
				<div className={ menuClasses }>
					<SearchControl
						value={ searchValue }
						onChange={ setSearchValue }
						placeholder={ __( 'Search …', 'rank-math' ) }
						autoFocus
					/>

					{ filteredItems.length > 0 ? (
						<ul
							tabIndex="-1"
							role="listbox"
							aria-hidden="false"
							aria-expanded={ isMenuOpen }
						>
							{ map(
								filteredItems,
								( { name, variable, description } ) => (
									<li
										role="option"
										key={ variable }
										aria-hidden="true"
										data-value={ variable }
										onClick={ () =>
											handleSelectedOption( variable )
										}
									>
										<div>
											<h1>{ name }</h1>
											<p>{ variable }</p>
										</div>

										<RawHTML className="description">
											{ description }
										</RawHTML>
									</li>
								)
							) }
						</ul>
					) : (
						<span className="no-results" />
					) }
				</div>
			) }
		</div>
	)
}

export default forwardRef( SelectVariable )
