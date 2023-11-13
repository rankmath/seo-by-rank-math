/**
 * External dependencies
 */
import { isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { createElement, useEffect } from '@wordpress/element'
import { TabPanel } from '@wordpress/components'

/**
 * Internal dependencies.
 */
import AITool from './tabs/ai-tools'
import ContentEditor from './tabs/content-editor'
import Chat from './tabs/chat'
import History from './tabs/history'
import ErrorMessage from './components/ErrorMessage'

const getTabs = () => {
	const tabs = [
		{
			name: 'ai-tools',
			id: 'ai-tools',
			title: __( 'AI Tools', 'rank-math' ),
			view: AITool,
			className: 'rank-math-ai-tools-tab rank-math-tab',
		},
		{
			name: 'content-editor',
			id: 'content-editor',
			title: __( 'Content Editor', 'rank-math' ),
			view: ContentEditor,
			className: 'rank-math-content-editor-tab rank-math-tab',
		},
		{
			name: 'chat',
			id: 'chat',
			title: __( 'Chat', 'rank-math' ),
			view: Chat,
			className: 'rank-math-chat-tab rank-math-tab',
		},
		{
			name: 'history',
			id: 'history',
			title: __( 'History', 'rank-math' ),
			view: History,
			className: 'rank-math-history-tab rank-math-tab',
		},
	]

	return tabs
}

// Content AI App
const ContentAIApp = () => {
	const activeTab = ! window.location.hash ? 'ai-tools' : window.location.hash.replace( '#', '' )
	const isRegistered = rankMath.isUserRegistered && rankMath.contentAIPlan && rankMath.contentAICredits

	useEffect( () => {
		if ( isRegistered && activeTab !== 'content-editor' && isUndefined( wp.blocks.getBlockType( 'core/paragraph' ) ) ) {
			wp.blockLibrary.registerCoreBlocks()
		}
	}, [] )
	return (
		<>
			<TabPanel
				className={ 'rank-math-tabs' }
				activeClass="is-active"
				tabs={ getTabs() }
				initialTabName={ activeTab }
				onSelect={ ( tabName ) => {
					window.location.hash = tabName
				} }
			>
				{ ( tab ) => {
					const blurredClass = ! isRegistered && 'history' !== tab.id ? ' blurred' : ''
					return (
						<>
							<div className={ 'rank-math-tab-content dashboard-wrapper rank-math-tab-content-' + tab.name + blurredClass }>
								{ createElement( tab.view, { isPage: true } ) }
							</div>

							{ blurredClass && <ErrorMessage width="40" /> }
						</>
					)
				} }
			</TabPanel>
		</>
	)
}

export default ContentAIApp
