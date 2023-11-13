/**
 * External dependencies
 */
import jQuery from 'jquery'
import { isNull } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { addFilter, addAction } from '@wordpress/hooks'
import { Fragment, render } from '@wordpress/element'
import { select, subscribe, dispatch } from '@wordpress/data'
import { registerPlugin } from '@wordpress/plugins'

/*
* Internal dependencies
*/
import AITool from './page/tabs/ai-tools'
import Write from './page/tabs/write'
import Chat from './page/tabs/chat'
import shortcutCommand from './page/shortcutCommand'
import App from './page/App'
import Helpers from './page/helpers'
import WriteShortcut from './page/tabs/WriteShortcut'
import hasError from './page/helpers/hasError'
import isGutenbergAvailable from '@helpers/isGutenbergAvailable'
import { getStore } from './store'

getStore()
Helpers()
shortcutCommand()

addAction( 'rank_math_loaded', 'rank-math', () => {
	addFilter( 'rank_math_content_ai_tabs', 'rank-math', ( tabs ) => {
		if ( rankMath.currentEditor !== 'divi' ) {
			tabs.push(
				{
					name: 'write',
					title: (
						<Fragment>
							<i
								className="rm-icon rm-icon-edit"
								title={ __( 'Write', 'rank-math' ) }
							></i>
							<span>{ __( 'Write', 'rank-math' ) }</span>
						</Fragment>
					),
					view: Write,
					className: 'rank-math-write-tab',
				}
			)
		}

		tabs.push(
			{
				name: 'ai-tools',
				title: (
					<Fragment>
						<i
							className="rm-icon rm-icon-page"
							title={ __( 'AI Tools', 'rank-math' ) }
						></i>
						<span>{ __( 'AI Tools', 'rank-math' ) }</span>
					</Fragment>
				),
				view: AITool,
				className: 'rank-math-ai-tools-tab',
			},
			{
				name: 'chat',
				title: (
					<Fragment>
						<i
							className="rm-icon rm-icon-comments"
							title={ __( 'Chat', 'rank-math' ) }
						></i>
						<span>{ __( 'Chat', 'rank-math' ) }</span>
					</Fragment>
				),
				view: Chat,
				className: 'rank-math-chat-tab',
			},
		)

		return tabs
	} )
} )

// Filter to change the paragraph placeholder text.
addFilter( 'blocks.registerBlockType', 'rank-math', function( settings, name ) {
	if ( 'core/paragraph' !== name ) {
		return settings
	}

	settings.attributes.placeholder = { type: 'string', default: __( 'Type / to choose a block or // to use Content AI', 'rank-math' ) }

	return settings
} )

jQuery( () => {
	const contentAIPage = document.getElementById( 'rank-math-content-ai-page' )
	if ( isNull( contentAIPage ) ) {
		return
	}

	render( <App />, contentAIPage )

	// Activate Content AI tab based on the hash link from URL.
	jQuery( '#wp-admin-bar-rank-math-content-ai-page' ).on( 'click', 'a', () => {
		setTimeout( () => {
			const name = window.location.hash.replace( '#', '' )
			if ( jQuery( '#tab-panel-0-' + name ).length ) {
				jQuery( '#tab-panel-0-' + name ).trigger( 'click' )
			}
		}, 100 )
	} )

	// Prevent changing the URL on Content Editor page when post is auto-saved.
	if ( rankMath.isContentAIPage ) {
		const location = window.location
		const path = location.pathname
		subscribe( () => {
			const isAutosavingPost = select( 'core/editor' ).isAutosavingPost()
			const blocks = select( 'core/block-editor' ).getBlocks()
			if ( blocks.length === 1 && blocks[ 0 ].name === 'core/paragraph' && blocks[ 0 ].attributes.content === '' && blocks[ 0 ].attributes.className !== 'rank-math-command' ) {
				dispatch( 'core/block-editor' ).updateBlockAttributes(
					blocks[ 0 ].clientId,
					{
						className: 'rank-math-command',
					}
				)
			}

			if ( ! isAutosavingPost ) {
				return
			}

			setTimeout( () => {
				dispatch( 'core/editor' ).savePost()
			}, 500 )

			if ( ! isNull( window.history.state ) && window.history.state.id ) {
				window.history.replaceState( '', 'Content AI', location.origin + path + '?page=rank-math-content-ai-page#content-editor' )
			}
		} )
	}
} )

jQuery( window ).on( 'load', () => {
	if ( hasError() || ( ! isGutenbergAvailable() && ! rankMath.isContentAIPage ) ) {
		return
	}

	registerPlugin( 'rank-math-content-ai-write-shortcut', {
		render() {
			return ( <WriteShortcut /> )
		},
	} )
} )
