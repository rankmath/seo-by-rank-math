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
import { compose } from '@wordpress/compose'
import { withDispatch, withSelect } from '@wordpress/data'

/**
 * Internal dependencies.
 */
import AITool from './AITool'
import ContentEditor from './ContentEditor'
import Chat from './Chat'
import History from './History'
import ErrorCTA from '@components/ErrorCTA'
import FreePlanNotice from '../components/FreePlanNotice'

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
const ContentAIApp = ( props ) => {
	const activeTab = ! window.location.hash ? 'ai-tools' : window.location.hash.replace( '#', '' )
	useEffect( () => {
		if ( ! props.hasError && activeTab !== 'content-editor' && isUndefined( wp.blocks.getBlockType( 'core/paragraph' ) ) ) {
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
					const blurredClass = props.hasError && 'history' !== tab.id ? ' blurred' : ''
					return (
						<>
							<div className={ 'rank-math-tab-content dashboard-wrapper rank-math-tab-content-' + tab.name + blurredClass }>
								<FreePlanNotice isContentAIPage="true" />
								{ createElement( tab.view, { ...props } ) }
							</div>

							{ blurredClass && <ErrorCTA width="40" /> }
						</>
					)
				} }
			</TabPanel>
		</>
	)
}

export default compose(
	withSelect( ( select, props ) => {
		return {
			data: props,
			hasError: ! props.isUserRegistered || ! props.plan || ! props.credits || props.isMigrating,
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updateData( key, value ) {
				dispatch( 'rank-math-content-ai' ).updateData( key, value )
			},
		}
	} )
)( ContentAIApp )
