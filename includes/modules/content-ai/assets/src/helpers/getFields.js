/**
 * External dependencies
 */
import { map, remove, isEmpty, isUndefined, isArray } from 'lodash'
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import {
	TextControl,
	TextareaControl,
	ToolbarButton,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalToggleGroupControl as ToggleGroupControl,
} from '@wordpress/components'
import { createRef } from '@wordpress/element'

/**
 * WordPress dependencies
 */
import TagifyField from '@components/TagifyField'
import getAttributes from '@helpers/getAttributes'
import getParams from './getParams'
import Label from '../components/Label'

const getDefaultValue = ( param, defaultValue ) => {
	return ! isUndefined( param.default ) ? param.default : defaultValue
}

/**
 * Function to get fields by type.
 *
 * @param {Object}   params     Fields data.
 * @param {Object}   attributes Data attributes.
 * @param {string}   endpoint   Current endpoint.
 * @param {Function} onChange   Function to run when field value is changed.
 */
export default ( params, attributes, endpoint, onChange ) => {
	const storedAttributes = wp.data.select( 'rank-math-content-ai' ).getContentAiAttributes()
	return (
		<form className="rank-math-ai-tools">
			{
				map(
					params,
					( param, key ) => {
						const value = getParams( key )
						value.placeholder = ! isEmpty( param.placeholder ) ? param.placeholder : value.placeholder
						value.label = ! isEmpty( param.label ) ? param.label : value.label
						const isRequired = param.isRequired
						const fieldValue = isArray( attributes[ key ] ) ? attributes[ key ].join( ' ' ) : attributes[ key ]

						const wrapperClass = classnames( 'form-field', {
							'is-required': isRequired,
							'limit-reached': ! isUndefined( value.maxlength ) && ! isUndefined( fieldValue ) && fieldValue.length > value.maxlength,
						} )

						const defaultValue = ! isUndefined( storedAttributes[ key ] ) ? storedAttributes[ key ] : getDefaultValue( param, value.default )
						if ( ! isEmpty( value.options ) && value.type === 'button' ) {
							return (
								<div className={ wrapperClass } key={ key }>
									<Label id={ key } data={ value } value="" endpoint={ endpoint } />
									<ToggleGroupControl value={ defaultValue }>
										{
											map( value.options, ( name, index ) => {
												return (
													<ToolbarButton
														key={ index }
														value={ name.value }
														isPressed={ name.value === defaultValue }
														onClick={ () => ( onChange( key, name.value ) ) }
													>
														{ ! isEmpty( name.label ) ? name.label : name.value }
													</ToolbarButton>
												)
											} )
										}
									</ToggleGroupControl>
								</div>
							)
						}

						if ( ! isUndefined( value.options ) ) {
							const tagifyField = createRef()

							const callbacks = {
								add: ( event ) => {
									let newValue = isArray( attributes[ key ] ) ? [ event.detail.data.value ] : event.detail.data.value
									if ( ! isUndefined( attributes[ key ] ) && isArray( attributes[ key ] ) ) {
										newValue = attributes[ key ]
										newValue.push( event.detail.data.value )
									}

									onChange( key, newValue )
								},
								remove: ( event ) => {
									if ( ! isArray( attributes[ key ] ) ) {
										onChange( key, '' )
										return false
									}

									const newValue = remove( attributes[ key ], ( val ) => {
										return val !== event.detail.data.value
									} )
									onChange( key, newValue )

									return false
								},
							}

							const settings = {
								addTagOnBlur: true,
								maxTags: value.maxTags ? value.maxTags : '100',
								whitelist: value.options,
								focusableTags: true,
								transformTag: ( tagData ) => {
									tagData.value = tagData.value.replaceAll( ',', '' )
								},
								templates: {
									tag: ( tagData ) => {
										const tagIcon = ! isUndefined( tagData.icon ) ? tagData.icon : ''

										try {
											return `<tag ${ getAttributes( tagData ) } title='${ tagData.value }' contenteditable='false' spellcheck="false" class='tagify__tag'>
													<x title='remove tag' class='tagify__tag__removeBtn'></x>
													<div>
														${ tagIcon }
														<span class='tagify__tag-text'>${ tagData.value }</span>
													</div>
												</tag>`
										} catch ( err ) {}
									},
									dropdownItem: ( tagData ) => {
										const tagIcon = ! isUndefined( tagData.icon ) ? tagData.icon : ''
										try {
											return `<div ${ getAttributes( tagData ) } class='tagify__dropdown__item' >
														${ tagIcon }
														<span>${ tagData.value }</span>
													</div>`
										} catch ( err ) {
											console.error( err )
										}
									},
								},
								dropdown: {
									enabled: 0,
									maxItems: 100,
									closeOnSelect: true,
								},
								callbacks,
							}
							return (
								<div className={ wrapperClass + ' content-ai-tagify rank-math-focus-keyword' } key={ key }>
									<Label id={ key } data={ value } value={ attributes[ key ] } endpoint={ endpoint } />
									<TagifyField
										id={ key }
										ref={ tagifyField }
										mode="input"
										settings={ settings }
										placeholder={ value.placeholder }
										initialValue={ defaultValue }
									/>
								</div>
							)
						}

						if ( ! isEmpty( value.type ) && value.type === 'textarea' ) {
							return (
								<div className={ wrapperClass } key={ key }>
									<Label id={ key } data={ value } value={ attributes[ key ] } endpoint={ endpoint } />
									<TextareaControl
										id={ key }
										onChange={ ( newValue ) => ( onChange( key, newValue ) ) }
										placeholder={ value.placeholder }
										className={ isRequired ? 'is-required' : '' }
										rows={ value.rows ? value.rows : '5' }
										required={ isRequired ? 'required' : '' }
										value={ defaultValue }
									/>
								</div>
							)
						}

						return (
							<div className={ wrapperClass } key={ key }>
								<Label id={ key } data={ value } value={ defaultValue } endpoint={ endpoint } />
								<TextControl
									id={ key }
									onChange={ ( newValue ) => ( onChange( key, newValue ) ) }
									placeholder={ value.placeholder }
									className={ isRequired ? 'is-required' : '' }
									required={ isRequired ? 'required' : '' }
									value={ defaultValue }
								/>
							</div>
						)
					}
				)
			}
		</form>
	)
}
