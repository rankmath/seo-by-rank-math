/**
 * External dependencies
 */
import jQuery from 'jquery'
import { map, isEmpty, reverse, uniqueId, trim } from 'lodash'
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment, useState, useEffect } from '@wordpress/element'
import { Button, SelectControl, Dashicon, Tooltip } from '@wordpress/components'
import { RichText } from '@wordpress/block-editor'

/**
 * Internal dependencies
 */
import PromptModal from './prompt-modal'
import getData from '../helpers/getData'
import deleteOutput from '../helpers/deleteOutput'
import markdownConverter from '../helpers/markdownConverter'
import CopyButton from '../components/CopyButton'
import ContentAiText from '../components/ContentAiText'
import ErrorMessage from '../components/ErrorMessage'

// Chat component.
export default ( { setCredits = false, hasContentAiError = false } ) => {
	const [ openModal, toggleModal ] = useState( false )
	const [ generating, setGenerating ] = useState( false )
	const [ message, setMessage ] = useState( '' )
	const [ chats, setChats ] = useState( rankMath.contentAIChats )
	const [ session, setSession ] = useState( '' )

	const isContentAIPage = rankMath.isContentAIPage

	// Select and highlight span on click.
	useEffect( () => {
		jQuery( '.chat-input span' ).on( 'click', ( e ) => {
			if ( ! window.getSelection || ! document.createRange ) {
				return
			}

			const sel = window.getSelection()
			if ( sel.toString() !== '' ) {
				return
			}

			window.setTimeout( () => {
				const el = e.target
				const range = document.createRange()
				range.selectNodeContents( el )
				sel.removeAllRanges()
				sel.addRange( range )
			}, 1 )
		} )
	}, [ message ] )

	const examples = [
		'How do backlinks affect SEO?',
		'Why is keyword research important for SEO?',
		'What are some effective SEO strategies for small businesses?',
		'Can you explain the difference between on-page and off-page SEO?',
		'List trending topics in [Industry] that I should write about.',
		'How can I optimize my website for local search queries?',
		'What are some effective strategies for managing [product/service description] reputation on social media?',
		'Develop a content strategy for [product/service description] to increase organic search traffic.',
	]

	// Prompt examples shown on New chat.
	const ExamplesWrapper = () => {
		return (
			<>
				<div className="prompt-examples">
					<h2>{ __( 'Examples', 'rank-math' ) }</h2>
					<p>{ __( 'Here are some examples of questions you can ask RankBot', 'rank-math' ) }</p>
					<div className="grid">
						{
							map( examples, ( example, key ) => {
								return (
									// eslint-disable-next-line jsx-a11y/click-events-have-key-events
									<div
										role="button"
										tabIndex="0"
										onClick={ () => {
											setMessage( example.replaceAll( '[', '<span>[' ).replaceAll( ']', ']</span>' ) )
										} }
										key={ key }
										dangerouslySetInnerHTML={ {
											__html: example.replaceAll( '[', '<span>[' ).replaceAll( ']', ']</span>' ),
										} }
									>
									</div>
								)
							} )
						}
					</div>
				</div>
			</>
		)
	}

	// Options to select Chat group.
	const getOptions = () => {
		const options = [
			{
				label: __( 'New Chat', 'rank-math' ),
				value: '',
			},
		]

		map( chats, ( value, index ) => {
			options.push(
				{
					label: value[ 0 ].content.replace( /(<([^>]+)>)/ig, '' ).split( /\s+/ ).slice( 0, 8 ).join( ' ' ) + '...',
					value: index,
				}
			)
		} )

		return options
	}

	// Function to submit the chat.
	const submitChat = ( text = '', regenerate = false ) => {
		setMessage( '' )
		setGenerating( true )
		if ( session === '' ) {
			setSession( 0 )
		}

		const isNew = session === ''
		const index = session !== '' ? session : rankMath.contentAIChats.length
		const data = isEmpty( chats[ index ] ) ? [] : chats[ index ]
		if ( ! regenerate ) {
			data.unshift(
				{
					role: 'user',
					content: text ? text : message,
				}
			)
		}

		if ( isNew ) {
			chats.unshift( data )
		} else {
			chats[ index ] = data
		}
		setChats( chats )
		setTimeout( () => {
			getData( 'Chat', { messages: reverse( data ), session: index, isNew, regenerate, site_type: 'ecommerce', site_name: rankMath.blogName, language: rankMath.ca_language, choices: 1 }, ( result ) => {
				reverse( data )
				if ( result ) {
					data.unshift(
						{
							role: 'assistant',
							content: result[ 0 ],
							isNew: true,
						},
					)
				}

				setGenerating( false )
			},
			true,
			setCredits
			)
		}, 100 )
	}

	return (
		<>
			<div className={ hasContentAiError ? 'blurred' : '' }>
				<div className="tab-header">
					<span className="tab-header-title">
						<i className="rm-icon rm-icon-bot"></i> RankBot <span>- { __( 'Your Personal Assistant', 'rank-math' ) }</span>
					</span>

					<a href="https://rankmath.com/kb/how-to-use-rankbot-ai-tool/?play-video=OBxuy8u0eCY&utm_source=Plugin&utm_medium=RankBot+Tab&utm_campaign=WP" rel="noreferrer" target="_blank" title={ __( 'Know more about RankBot', 'rank-math' ) }>
						<em className="dashicons-before dashicons-editor-help rank-math-tooltip"></em>
					</a>

					{
						! isContentAIPage && session !== '' &&
						<Button
							className="clear-history is-small button-link-delete"
							onClick={ () => {
								chats.splice( session, 1 )
								setChats( chats )
								deleteOutput( true, session )
								setTimeout( () => {
									setSession( '' )
								}, 10 )
							} }
						>
							{ __( 'Delete Session', 'rank-math' ) }
						</Button>
					}
				</div>
				<div className="rank-math-content-chat-page">
					{
						! isEmpty( chats ) &&
						<div className="chat-sidebar">
							<div className="chat-sidebar-content">
								{
									! isContentAIPage &&
									<SelectControl
										value={ session }
										options={ getOptions() }
										onChange={ ( newIndex ) => {
											setSession( newIndex )
										} }
									/>
								}
								{
									isContentAIPage &&
									<Button
										className={
											classnames( 'history-button button new-chat', {
												active: session === '',
											} )
										}
										onClick={ () => {
											setSession( '' )
										} }
									>
										<i className="rm-icon rm-icon-circle-plus"></i> { __( 'New Chat', 'rank-math' ) }
									</Button>
								}
								{
									isContentAIPage &&
									map( chats, ( value, index ) => {
										const title = value.length > 2 ? value[ value.length - 2 ].content : value[ 0 ].content
										return (
											<div
												role="button"
												tabIndex="0"
												className={
													classnames( 'history-button button', {
														active: session === index,
													} )
												}
												key={ index }
												onClick={ () => {
													setSession( index )
												} }
												onKeyDown={ undefined }
											>
												<i className="rm-icon rm-icon-comments"></i>
												{ title.split( /\s+/ ).slice( 0, 8 ).join( ' ' ) + '...' }
												<Button
													className="delete-session"
													onClick={ () => {
														chats.splice( index, 1 )
														setChats( chats )
														deleteOutput( true, index )
														setTimeout( () => {
															setSession( '' )
														}, 10 )
													} }
													title={ __( 'Delete Session', 'rank-math' ) }
												>
													<i className="dashicons dashicons-no-alt"></i>
												</Button>
											</div>
										)
									} )
								}
							</div>
						</div>
					}

					<div className="chat-container">
						<div className="chat-messages">
							{ generating && <div className="chat-message loading"><div className="rank-math-loader"></div></div> }

							{ session === '' && <ExamplesWrapper /> }
							{
								! isEmpty( chats ) && session !== '' &&
								map( chats[ session ], ( value, key ) => {
									if ( isEmpty( value.content ) ) {
										return
									}

									const isUser = 'user' === value.role
									const label = isUser ? __( 'You:', 'rank-math' ) : __( 'RankBot:', 'rank-math' )

									const wrapperID = uniqueId()
									const isNew = value.isNew
									value.isNew = false

									return (
										<div className={ isUser ? 'chat-message user' : 'chat-message' } key={ key }>
											<div className="message-actions">
												<span>{ label }</span>
												{
													! isUser &&
													<CopyButton value={ value.content } />
												}
											</div>
											<div className="message" id={ 'block-' + wrapperID }>
												{ isNew && <ContentAiText value={ value.content } showWordCount={ false } /> }
												{
													! isNew &&
													<div
														dangerouslySetInnerHTML={ {
															__html: markdownConverter( value.content ),
														} }
													></div>
												}
											</div>
										</div>
									)
								} )
							}
						</div>

						<div className="chat-input">
							<div className="chat-input-actions">
								<RichText
									tagName="div"
									className="chat-input-textarea"
									value={ message.slice( 0, 2000 ) }
									allowedFormats={ [] }
									onChange={ ( content ) => {
										const inputWrapper = document.getElementsByClassName( 'chat-input-textarea' )[ 0 ]
										if ( content.length > 2000 ) {
											content = content.slice( 0, 2000 )
											inputWrapper.innerHTML = message
											const range = document.createRange()
											const sel = window.getSelection()
											const childNode = inputWrapper.childNodes[ inputWrapper.childNodes.length - 1 ]
											range.setStart( childNode, childNode.textContent.length )
											range.collapse( true )

											sel.removeAllRanges()
											sel.addRange( range )
										}

										setMessage( content )
									} }
									onKeyUp={ ( e ) => {
										if ( e.key === 'Enter' && ! e.shiftKey && ! isEmpty( trim( message ) ) && ! generating ) {
											submitChat()
										}
									} }
									preserveWhiteSpace="true"
									placeholder={ __( 'Type your message here…', 'rank-math' ) }
								/>
								<div className="chat-input-buttons">
									<Button
										className="prompts-button button"
										onClick={ () =>
											toggleModal( true )
										}
									>
										<i className="rm-icon rm-icon-htaccess"></i> { isContentAIPage ? __( 'Prompts Library', 'rank-math' ) : '' }
									</Button>
									<PromptModal isOpen={ openModal } toggleModal={ toggleModal } setMessage={ setMessage } />
									{
										session !== '' && ! generating &&
										<Tooltip text={ __( 'Regenerate Response', 'rank-math' ) }>
											<Button
												className="regenerate-response button button-small"
												onClick={ () => {
													const data = chats[ session ]
													const content = data[ 1 ].content
													data.shift()
													chats[ session ] = data
													setChats( chats )
													submitChat( content, true )
												} }
												showTooltip={ true }
											>
												<Dashicon icon="controls-repeat" />
											</Button>
										</Tooltip>
									}
									<div className={ message.length >= 2000 ? 'limit limit-reached' : 'limit' }>
										<span className="count">{ message.length }</span>/{ __( '2000', 'rank-math' ) }
									</div>
									<Button
										className="button is-primary is-large"
										aria-label={ __( 'Send', 'rank-math' ) }
										disabled={ isEmpty( trim( message ) ) || generating }
										onClick={ () => ( submitChat() ) }
									>
										<span className="rm-icon rm-icon-send"></span>
									</Button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			{ hasContentAiError && <ErrorMessage /> }
		</>
	)
}
