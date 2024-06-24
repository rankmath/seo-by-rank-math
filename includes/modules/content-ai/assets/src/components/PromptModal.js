/**
 * External dependencies
 */
import jQuery from 'jquery'
import { map, lowerCase, isEmpty, isUndefined, find, compact, reverse } from 'lodash'
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Modal, TextControl, TextareaControl, BaseControl, Button } from '@wordpress/components'
import { useState, useEffect } from '@wordpress/element'
import apiFetch from '@wordpress/api-fetch'

/**
 * Internal dependencies
 */
import SearchField from '../components/SearchField'
import ErrorCTA from '@components/ErrorCTA'

// Create dummy constant with json data.
const promptCategories = {
	all: __( 'All', 'rank-math' ),
	recent: __( 'Recent', 'rank-math' ),
	custom: __( 'Custom +', 'rank-math' ),
	seo: __( 'SEO', 'rank-math' ),
	blog: __( 'Blog', 'rank-math' ),
	'marketing-sales': __( 'Marketing & Sales', 'rank-math' ),
	ecommerce: __( 'eCommerce', 'rank-math' ),
	misc: __( 'Misc', 'rank-math' ),
}

/**
 * Prompt Modal Component.
 *
 * @param {Object} props Component props.
 *
 */
export default ( props ) => {
	const data = wp.data.select( 'rank-math-content-ai' ).getData()
	const { isOpen, toggleModal, setMessage, updateData } = props
	const [ search, setSearch ] = useState()
	const [ selectedPrompt, setPrompt ] = useState( 'custom' )
	const [ selectedCategory, setCategory ] = useState( 'all' )
	const [ prompts, setPrompts ] = useState( data.prompts )
	const [ deleting, setDeleting ] = useState( false )
	const [ saving, setSaving ] = useState( false )
	const [ updatingLibrary, setUpdating ] = useState( false )
	const [ isError, setError ] = useState( false )
	const [ customPrompt, setCustomPrompt ] = useState(
		{
			prompt_name: '',
			prompt: '',
			prompt_category: 'custom',
		}
	)

	// Filter array to show only Recent prompts.
	useEffect( () => {
		if ( selectedCategory === 'recent' && ! isEmpty( data.recentPrompts ) ) {
			const recentPrompts = map( data.recentPrompts, ( value ) => {
				return find( data.prompts, ( val ) => {
					return val.PromptName === value
				} )
			} )

			setPrompts( recentPrompts )

			return
		}

		if ( 'all' === selectedCategory ) {
			setPrompts( data.prompts )
			return
		}

		const newPrompts = compact(
			map( data.prompts, ( prompt ) => {
				if ( prompt.PromptCategory !== selectedCategory || isUndefined( prompt.Prompt ) ) {
					return false
				}

				return prompt
			} )
		)

		setPrompts( 'custom' === selectedCategory ? reverse( newPrompts ) : newPrompts )
	}, [ selectedCategory, deleting ] )

	useEffect( () => {
		setTimeout( () => {
			setError( false )
		}, 5000 )
	}, [ isError ] )

	const blurredClass = 'free' === data.plan ? 'blurred' : ''
	return isOpen && (
		<Modal
			title={ __( 'Prompts Library', 'rank-math' ) }
			closeButtonLabel={ __( 'Close', 'rank-math' ) }
			shouldCloseOnClickOutside={ false }
			shouldCloseOnEsc={ true }
			onRequestClose={ () => ( toggleModal( false ) ) }
			className="rank-math-contentai-modal rank-math-prompt-modal rank-math-modal"
			overlayClassName="rank-math-modal-overlay rank-math-contentai-modal-overlay"
		>
			<div className={ blurredClass }>
				<div className="content-ai-filter">
					<div>
						{
							map( promptCategories, ( category, key ) => {
								const className = classnames( key, {
									active: key === selectedCategory,
								} )
								return (
									<Button
										className={ className }
										key={ key }
										onClick={ () => {
											setCategory( key )
											setPrompt( 0 )
										} }
									>
										{ category }
									</Button>
								)
							} )
						}
					</div>
					<Button
						className="update-library is-secondary is-small"
						key="update-library"
						onClick={ () => {
							setUpdating( true )
							jQuery.ajax(
								{
									url: data.url + 'default_prompts',
									type: 'GET',
									success: ( result ) => {
										setUpdating( false )
										if ( isEmpty( result ) ) {
											console.log( 'No data found' )
											return
										}

										apiFetch( {
											method: 'POST',
											path: '/rankmath/v1/ca/savePrompts',
											data: { prompts: result },
										} )
											.then( ( response ) => {
												updateData( 'prompts', response )
												setPrompts( response )
												setPrompt( 0 )
											} )
											.catch( ( error ) => {
												// eslint-disable-next-line no-console
												console.log( error )
											} )
									},
									error: () => {
										console.error( 'Failed, please try again later' )
										setUpdating( false )
									},
								}
							)
						} }
					>
						{ updatingLibrary ? __( 'Updating…', 'rank-math' ) : __( 'Update Library', 'rank-math' ) }
					</Button>
				</div>
				<div className="grid">
					<div className="column column-first">
						<h3>{ __( 'Prompt List', 'rank-math' ) }</h3>
						{
							! isEmpty( prompts ) &&
							<>
								<SearchField search={ search } setSearch={ setSearch } />
								<div className="prompt-list">
									{
										selectedCategory === 'all' &&
										<Button
											className={ selectedPrompt === 'custom' ? 'prompt-item active' : 'prompt-item' }
											key={ 'add-custom' }
											onClick={ () => ( setPrompt( 'custom' ) ) }
										>
											<i>🧪</i>
											<span>{ __( 'Add Custom Prompt +', 'rank-math' ) }</span>
										</Button>
									}
									{
										// Map PromptArray
										map( prompts, ( prompt, index ) => {
											if ( isUndefined( prompt ) || isUndefined( prompt.Prompt ) || ( search && ! lowerCase( prompt.PromptName ).includes( lowerCase( search ) ) ) ) {
												return
											}

											const className = classnames( 'prompt-item ' + index, {
												active: index === selectedPrompt,
												'custom-prompt': prompt.PromptCategory === 'custom',
											} )

											const deletePromptClass = classnames( 'delete-prompt', {
												'rank-math-loader': deleting === index,
											} )

											return (
												<Button
													className={ className }
													key={ index }
													onClick={ () => ( setPrompt( index ) ) }
												>
													<i>{ prompt.PromptIcon ? prompt.PromptIcon : '🖌️' }</i>
													<span>{ prompt.PromptName }</span>

													{
														prompt.PromptCategory === 'custom' &&
														<span
															role="button"
															tabIndex="0"
															onKeyDown={ undefined }
															className={ deletePromptClass }
															onClick={ () => {
																setDeleting( index )
																apiFetch( {
																	method: 'POST',
																	path: '/rankmath/v1/ca/updatePrompt',
																	data: {
																		prompt: prompt.PromptName,
																	},
																} )
																	.then( ( newPrompts ) => {
																		updateData( 'prompts', newPrompts )
																		setPrompt( 0 )
																		setDeleting( false )
																	} )
																	.catch( ( error ) => {
																		setDeleting( false )

																		// eslint-disable-next-line no-console
																		console.log( error )
																	} )
															} }
														>
															{ deleting !== index && <i className="dashicons dashicons-no-alt"></i> }
														</span>
													}
												</Button>
											)
										} )
									}
								</div>
							</>
						}
					</div>
					<div className="column column-second">
						{
							'custom' === selectedPrompt && 'all' === selectedCategory &&
							<div className="custom-prompt-form">
								<div className="form-field">
									<TextControl
										label={ __( 'Prompt Name', 'rank-math' ) }
										onChange={ ( val ) => {
											customPrompt.prompt_name = val
											setCustomPrompt( { ...customPrompt } )
										} }
										className={ isError && ! customPrompt.prompt_name ? 'is-required' : '' }
									/>
								</div>
								<div className="form-field">
									<div className={ customPrompt.prompt.length >= 2000 ? 'limit limit-reached' : 'limit' }>
										<span className="count">{ customPrompt.prompt.length }</span>/2000
									</div>
									<TextareaControl
										label={ __( 'Prompt Text', 'rank-math' ) }
										help={ __( 'Use [brackets] to insert placeholders.', 'rank-math' ) }
										onChange={ ( val ) => {
											customPrompt.prompt = val
											setCustomPrompt( { ...customPrompt } )
										} }
										maxLength="2000"
										className={ isError && ! customPrompt.prompt ? 'is-required' : '' }
									/>
								</div>
								<div className="form-field">
									<BaseControl className="save-prompt">
										<Button
											variant="primary"
											className="is-large"
											onClick={ () => {
												if ( ! customPrompt.prompt_name || ! customPrompt.prompt ) {
													setError( true )
													return
												}

												const promptExists = find( prompts, function( prompt ) {
													return prompt.PromptName === customPrompt.prompt_name
												} )

												if ( ! isUndefined( promptExists ) ) {
													alert( __( 'Prompt with this name already exists', 'rank-math' ) )
													return
												}

												setSaving( true )
												apiFetch( {
													method: 'POST',
													path: '/rankmath/v1/ca/updatePrompt',
													data: {
														prompt: {
															PromptName: customPrompt.prompt_name,
															Prompt: customPrompt.prompt,
															PromptCategory: customPrompt.prompt_category,
														},
													},
												} )
													.then( ( newPrompts ) => {
														data.prompts.push( newPrompts[ newPrompts.length - 1 ] )
														updateData( 'prompts', newPrompts )
														setCategory( 'custom' )

														setTimeout( () => {
															setPrompt( 0 )
															setSaving( false )
														}, 300 )
													} )
													.catch( ( error ) => {
														// eslint-disable-next-line no-console
														console.log( error )
														setSaving( false )
													} )
											} }
										>
											{ saving && <span className="rank-math-loader"></span> }
											{ ! saving && __( 'Save Prompt', 'rank-math' ) }
										</Button>
									</BaseControl>
								</div>
							</div>
						}

						{
							( 'custom' !== selectedPrompt || 'all' !== selectedCategory ) && ! isEmpty( prompts ) && ! isEmpty( prompts[ selectedPrompt ] ) &&
							<div className="prompt-details">
								<div className="prompt-preview">
									<h3>{ __( 'Preview', 'rank-math' ) }</h3>
									<div
										className="prompt-preview-content"
									>
										<p
											dangerouslySetInnerHTML={ {
												__html: isUndefined( prompts[ selectedPrompt ].Prompt ) ? '' : prompts[ selectedPrompt ].Prompt.replaceAll( '[', '<span>[' ).replaceAll( ']', ']</span>' ),
											} }
										>
										</p>
									</div>
								</div>
								<div className="form-field">
									<BaseControl className="use-prompt">
										<Button
											variant="primary"
											className="is-large"
											onClick={ () => {
												apiFetch( {
													method: 'POST',
													path: '/rankmath/v1/ca/updateRecentPrompt',
													data: {
														prompt: prompts[ selectedPrompt ].PromptName,
													},
												} )
													.then( () => {
														data.recentPrompts.unshift( prompts[ selectedPrompt ].PromptName )
														updateData( 'recentPrompts', data.recentPrompts )
													} )
													.catch( ( error ) => {
														console.log( error )
													} )
												setMessage( prompts[ selectedPrompt ].Prompt.replaceAll( '[', '<span>[' ).replaceAll( ']', ']</span>' ) )
												toggleModal( false )
											} }
										>
											{ __( 'Use Prompt', 'rank-math' ) }
										</Button>
									</BaseControl>
								</div>
							</div>
						}
					</div>
				</div>
			</div>
			{ blurredClass && <ErrorCTA width="60" isResearch={ true } /> }
		</Modal>
	)
}
