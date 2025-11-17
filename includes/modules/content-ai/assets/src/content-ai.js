/**
 * External dependencies
 */
import jQuery from 'jquery'
import { isEmpty, isNull, isUndefined, includes, endsWith } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { addAction, addFilter } from '@wordpress/hooks'
import { Button } from '@wordpress/components'
import { Fragment, createElement, createRoot } from '@wordpress/element'
import { registerPlugin } from '@wordpress/plugins'
import { select, dispatch, subscribe, useSelect } from '@wordpress/data'
import { PluginSidebar, PluginSidebarMoreMenuItem, PluginPrePublishPanel } from '@wordpress/edit-post'
import {
	PluginSidebar as SePluginSidebar,
	PluginSidebarMoreMenuItem as SePluginSidebarMoreMenuItem,
} from '@wordpress/edit-site'

/**
 * Internal dependencies
 */
import isGutenbergAvailable from '@helpers/isGutenbergAvailable'
import isSiteEditorAvailable from '@helpers/isSiteEditorAvailable'
import isSiteEditorHomepage from '@helpers/isSiteEditorHomepage'
import ContentAITab from './tabs/ContentAITab'
import ContentAIPage from './tabs/ContentAIPage'
import ContentAnalysis from './research/ContentAnalysis'
import ContentAIIcon from './components/ContentAIIcon'
import FixWithAI from './components/FixWithAI'
import MediaHandler from './components/MediaHandler'
import WriteShortcut from './components/WriteShortcut'
import tinyMceAnnotators from './helpers/tinyMceAnnotators'
import getBlockContent from './helpers/getBlockContent'
import { getStore } from './store'
import helpers from './helpers'
import shortcutCommand from './shortcutCommand'
import { DashboardHeader } from '@rank-math/components'

// Content AI Panel to add in Gutenberg & Site Editor.
const GetAIPanel = () => {
	const isSiteEditorPage = useSelect( () => {
		return isSiteEditorHomepage() ||
		(
			! isUndefined( select( 'core/edit-site' ) ) &&
			endsWith( select( 'core/edit-site' ).getEditedPostId(), '//page' )
		)
	} )

	if ( isSiteEditorAvailable() && isSiteEditorPage ) {
		return (
			<Fragment>
				<SePluginSidebarMoreMenuItem
					target="seo-by-rank-math-content-ai-sidebar"
					icon={ <ContentAIIcon /> }
				>
					{ __( 'Content AI', 'rank-math' ) }
				</SePluginSidebarMoreMenuItem>
				<SePluginSidebar
					name="seo-by-rank-math-content-ai-sidebar"
					title="Content AI"
					className="rank-math-sidebar-panel rank-math-sidebar-content-ai-panel"
				>
					<ContentAITab />
				</SePluginSidebar>
			</Fragment>
		)
	}

	return (
		<Fragment>
			<PluginSidebarMoreMenuItem
				target="seo-by-rank-math-content-ai-sidebar"
				icon={ <ContentAIIcon /> }
			>
				{ __( 'Content AI', 'rank-math' ) }
			</PluginSidebarMoreMenuItem>
			<PluginSidebar
				name="seo-by-rank-math-content-ai-sidebar"
				title="Content AI"
				className="rank-math-sidebar-panel rank-math-sidebar-content-ai-panel"
			>
				<ContentAITab />
			</PluginSidebar>
		</Fragment>
	)
}

class ContentAI {
	/**
	 * Constructor.
	 */
	constructor() {
		this.init()
		this.events()
		this.addPrePublishPanel( this.data )
		this.registerWriteShortcut( this.data )
		this.loadContentAIPage( this.data )
		this.changePlaceholder = this.changePlaceholder.bind( this )
		this.setup = this.setup.bind( this )
		addAction( 'rank_math_loaded', 'rank-math', this.setup )
		addFilter( 'blocks.registerBlockType', 'rank-math', this.changePlaceholder )
		addFilter(
			'rankMath.checklists.FixWithAI',
			'rank-math-pro',
			() => FixWithAI
		)
		addFilter( 'rank_math_before_help_link', 'rank-math', this.showRemainingCredits )
	}

	// Load Redux store, helpers, shortcut Command and Annotators.
	init() {
		getStore()
		helpers()
		shortcutCommand()
		tinyMceAnnotators()

		this.data = select( 'rank-math-content-ai' ).getData()
		if ( ! this.data.isContentAIPage ) {
			new MediaHandler()
			new ContentAnalysis()
		}
	}

	// Setup Content AI on different editor.
	setup() {
		this.addHeaderButton()
		this.addSidebarTab()

		// Add Content AI data in the metabox in Classic Editor.
		const metaboxElem = document.getElementById( 'cmb2-metabox-rank_math_metabox_content_ai' )
		if ( ! metaboxElem ) {
			return
		}

		setTimeout( () => {
			createRoot( metaboxElem ).render( createElement( ContentAITab ) )
		}, 1000 )
	}

	// Content AI Events.
	events() {
		if ( 'classic' === rankMath.currentEditor ) {
			return
		}

		jQuery( document ).on( 'click', '.rank-math-open-contentai', ( e ) => {
			e.preventDefault()
			const dispatcher = dispatch( 'core/edit-post' )
			if ( ! isNull( dispatcher ) ) {
				dispatcher.openGeneralSidebar( 'rank-math-content-ai/seo-by-rank-math-content-ai-sidebar' )
				return false
			}

			jQuery( '.rank-math-content-ai-tab' ).trigger( 'click' )
			return false
		} )

		if (
			isGutenbergAvailable() &&
			includes( window.location.search, 'tab=content-ai' )
		) {
			const panelName = 'rank-math-content-ai/seo-by-rank-math-content-ai-sidebar'
			if ( select( 'core/edit-post' ).getActiveGeneralSidebarName() !== panelName ) {
				dispatch( 'core/edit-post' ).openGeneralSidebar( panelName )
			}
		}
	}

	// Add Content AI in Sidebar tab. This is used to open Content AI tab in Elementor & Divi editor.
	addSidebarTab() {
		addFilter( 'rank_math_sidebar_tabs', 'rank-math', ( tabs ) => {
			tabs.push( {
				name: 'contentAI',
				title: (
					<Fragment>
						<i className="rm-icon rm-icon-content-ai" />
						<span>{ __( 'Content AI', 'rank-math' ) }</span>
					</Fragment>
				),
				view: ContentAITab,
				className: 'rank-math-content-ai-tab hidden is-active',
			} )

			return tabs
		} )
	}

	// Filter to change the paragraph placeholder text.
	changePlaceholder( settings, name ) {
		if ( 'core/paragraph' !== name ) {
			return settings
		}

		settings.attributes.placeholder = { type: 'string', default: __( 'Type / to choose a block or // to use Content AI', 'rank-math' ) }

		return settings
	}

	// Add Content AI Button in the Post Header.
	addHeaderButton() {
		// Early Bail if current editor is not Gutenberg.
		if ( ! isGutenbergAvailable() ) {
			return
		}

		registerPlugin( 'rank-math-content-ai', {
			icon: <ContentAIIcon />,
			render: GetAIPanel,
		} )
	}

	// Add Pre Publish Panel that will show a Notice box in the pre-publish panel before the post is published on a new post.
	addPrePublishPanel( data ) {
		// Early Bail if current editor is not Gutenberg.
		if ( ! isGutenbergAvailable() ) {
			return
		}

		registerPlugin( 'rank-math-content-ai-box', {
			render() {
				if ( ! isEmpty( data.score ) || ! isEmpty( data.keyword ) ) {
					return false
				}

				return (
					<PluginPrePublishPanel
						title={ __( 'Content AI', 'rank-math' ) }
						icon="rm-icon rm-icon-content-ai"
						initialOpen="true"
						className="rank-math-content-ai-box"
					>
						<p>{ __( 'Improve your content with a personal Content AI.', 'rank-math' ) }</p>
						<Button
							className="button-primary"
							onClick={ () => {
								jQuery( '.editor-post-publish-panel__header-cancel-button button' ).trigger( 'click' )
								if ( ! jQuery( '.rank-math-toolbar-score' ).parent().hasClass( 'is-pressed' ) ) {
									jQuery( '.rank-math-toolbar-score' ).trigger( 'click' )
								}

								setTimeout( () => {
									jQuery( '.rank-math-content-ai-tab' ).trigger( 'click' )
								}, 100 )
							} }
						>
							{ __( 'Improve Now', 'rank-math' ) }
						</Button>
					</PluginPrePublishPanel>
				)
			},
		} )
	}

	// Register a Keyboard Shortcut (Ctrl + /) in Gutenberg & Content Editor.
	registerWriteShortcut( data ) {
		if ( ! data.registerWriteShortcut || ( ! isGutenbergAvailable() && ! data.isContentAIPage ) ) {
			return
		}

		registerPlugin( 'rank-math-content-ai-write-shortcut', {
			render() {
				return ( <WriteShortcut /> )
			},
		} )
	}

	// Load Content AI Data and Events on Content AI Page
	loadContentAIPage( data ) {
		const contentAIPage = document.getElementById( 'rank-math-content-ai-page' )
		if ( isNull( contentAIPage ) ) {
			return
		}

		createRoot( contentAIPage ).render(
			<>
				<DashboardHeader page="content_ai" />
				<div className="wrap rank-math-wrap">
					<ContentAIPage { ...data } />
				</div>
			</>
		)

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
		const location = window.location
		const path = location.pathname
		subscribe( () => {
			const isAutosavingPost = select( 'core/editor' ).isAutosavingPost()
			const blocks = select( 'core/block-editor' ).getBlocks()
			if (
				blocks.length === 1 &&
				blocks[ 0 ].name === 'core/paragraph' &&
				isEmpty( getBlockContent( blocks[ 0 ] ) ) &&
				blocks[ 0 ].attributes.className !== 'rank-math-command'
			) {
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

	showRemainingCredits() {
		const data = wp.data.select( 'rank-math-content-ai' ).getData()
		if ( ! data.isUserRegistered || ! data.plan ) {
			return
		}

		return (
			<div className="credits-remaining">
				Credits Remaining: <strong>{ rankMath.contentAI.credits }</strong>
			</div>
		)
	}
}

new ContentAI()
