/**
 * External dependencies
 */
import classNames from 'classnames'
import { filter, map, lowerCase, includes, forEach } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { SearchControl, Icon } from '@wordpress/components'
import { useState, useMemo, RawHTML, forwardRef } from '@wordpress/element'

/**
 * Internal dependencies
 */
import Button from '../buttons/Button'
import TextControl from '../inputs/TextControl'
import TextareaControl from '../inputs/TextareaControl'
import useClickOutside from '../hooks/useClickOutside'
import './scss/SelectVariable.scss'

const replaceVariables = ( text ) => {
	forEach( rankMath.variables, ( val, tag ) => {
		if ( ! val.example ) {
			return
		}

		const re = new RegExp( '\\([a-z]+\\)', 'g' )
		tag = tag.replace( re, '\\(.*?\\)' )
		text = text.replace(
			new RegExp( '%+' + tag + '%+', 'g' ),
			val.example
		)
	} )

	return text
}

const variablePreview = ( value, fieldId ) => {
	if ( ! value ) {
		return
	}

	value = replaceVariables( value )

	if (
		60 < value.length &&
		0 <= fieldId.indexOf( 'title' )
	) {
		value = value.substring( 0, 60 ) + '...'
	} else if (
		160 < value.length &&
		0 <= fieldId.indexOf( 'description' )
	) {
		value = value.substring( 0, 160 ) + '...'
	}

	return (
		<div className="rank-math-variables-preview" data-title="Example">{ value }</div>
	)
}

/**
 * Select Variable component.
 *
 * @param {Object}   props             Component props.
 * @param {string}   props.value       The current value selected.
 * @param {Node}     props.label       The label associated with the control.
 * @param {Object}   props.style       Inline style object for additional styling.
 * @param {Array}    props.options     The dropdown options.
 * @param {Function} props.onChange    Callback invoked when the selection changes.
 * @param {string}   props.width       Sets the width of the control.
 * @param {string}   props.className   CSS class for additional styling.
 * @param {boolean}  props.disabled    Whether the control is disabled.
 * @param {boolean}  props.showPreview Whether to show the preview below the Search variable dropdown.
 * @param {string}   props.as          Whether to render the input or textarea variant. Accepted value: 'textarea'.
 * @param {Object}   props.inputProps  Custom props to be passed to the input element.
 * @param {Object}   ref               Ref object for accessing an instance of the component.
 */
const SelectVariable = ( {
	as,
	value = '',
	label,
	style,
	options,
	onChange,
	inputProps,
	className = '',
	disabled = false,
	showPreview = true,
	...additionalProps
}, ref ) => {
	const [ searchValue, setSearchValue ] = useState( '' )
	const [ showMenu, setShowMenu, containerRef ] = useClickOutside()
	const selectRef = ref ?? containerRef
	const excludeVariables = additionalProps.exclude ?? []

	// Memoize selectMenu for performance
	const selectMenu = useMemo( () => {
		const matchValue = lowerCase( searchValue )
		return filter(
			rankMath.variables,
			( { name, variable, description } ) => (
				! includes( excludeVariables, variable ) &&
				(
					includes( lowerCase( name ), matchValue ) ||
					includes( lowerCase( variable ), matchValue ) ||
					includes( lowerCase( description ), matchValue )
				)
			)
		)
	}, [ searchValue, excludeVariables ] )

	const handleSelectedOption = ( newValue ) => {
		onChange( `${ value } ${ newValue }` )
		setShowMenu( false )
		setSearchValue( '' )
	}

	const sharedInputProps = {
		value,
		onChange,
		disabled,
		...inputProps,
	}

	const props = {
		...additionalProps,
		ref: selectRef,
		'aria-disabled': disabled,
		className: `rank-math-select-variable ${ className }`,
	}

	return (
		<div { ...props }>
			<div className="select-input" aria-expanded={ showMenu }>
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
					onClick={ () => setShowMenu( ( prev ) => ! prev ) }
				/>
			</div>

			{ showMenu && (
				<div
					className={ classNames( 'select-menu', {
						'is-textarea-menu': as === 'textarea',
					} ) }
				>
					<SearchControl
						value={ searchValue }
						onChange={ setSearchValue }
						placeholder={ __( 'Search …', 'rank-math' ) }
					/>

					{ selectMenu.length > 0 ? (
						<ul
							tabIndex="-1"
							role="listbox"
							aria-hidden="false"
							aria-expanded={ showMenu }
						>
							{ map(
								selectMenu,
								( { name, variable, description } ) => (
									<li
										role="option"
										key={ variable }
										data-value={ variable }
										tabIndex={ 0 }
										onClick={ () =>
											handleSelectedOption( `%${ variable }%` )
										}
										onKeyDown={ undefined }
									>
										<div>
											<strong>{ name }</strong>
											<p>%{ variable }%</p>
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

			{ showPreview && variablePreview( value, additionalProps.id ) }
		</div>
	)
}

export default forwardRef( SelectVariable )
