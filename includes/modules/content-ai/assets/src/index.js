/**
 * External dependencies
 */
import jQuery from 'jquery'
import classnames from 'classnames'
import { isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { addAction, addFilter } from '@wordpress/hooks'
import { Button } from '@wordpress/components'
import { Fragment, createElement, render } from '@wordpress/element'
import { registerPlugin } from '@wordpress/plugins'
import { select } from '@wordpress/data'
import {
	PluginSidebar,
	PluginSidebarMoreMenuItem,
	PluginPrePublishPanel,
} from '@wordpress/edit-post'

/**
 * Internal dependencies
 */
import ContentAI from './ContentAI'
import ContentAIIcon from './ContentAIIcon'
import ContentAnalysis from './ContentAnalysis'

const ContentAIButton = () => {
	const className = classnames( 'button-secondary rank-math-content-ai', {
		'is-new': ! rankMath.ca_viewed,
	} )
	return (
		<Button
			className={ className }
			onClick={ () => {
				if ( jQuery( '.rank-math-toolbar-score.content-ai-score' ).length ) {
					jQuery( '.rank-math-toolbar-score.content-ai-score' ).parent().trigger( 'click' )
					return
				}

				jQuery( '.rank-math-content-ai-tab' ).trigger( 'click' )
			} }
		>
			<i className="rm-icon rm-icon-target"></i>
			{ __( 'Content AI', 'rank-math' ) }
		</Button>
	)
}

addFilter(
	'rankMath.analytics.contentAI',
	'rank-math',
	() => ContentAIButton
)

addAction( 'rank_math_loaded', 'rank-math', () => {
	addFilter( 'rank_math_sidebar_tabs', 'rank-math', ( tabs ) => {
		tabs.push( {
			name: 'contentAI',
			title: (
				<Fragment>
					<i className="rm-icon rm-icon-target" />
					<span>{ __( 'Content AI', 'rank-math' ) }</span>
				</Fragment>
			),
			view: ContentAI,
			className: 'rank-math-content-ai-tab hidden is-active',
		} )

		return tabs
	} )

	// Add Content AI data in the metabox in Classic Editor.
	const metaboxElem = document.getElementById( 'cmb2-metabox-rank_math_metabox_content_ai' )
	if ( metaboxElem ) {
		setTimeout( () => {
			render(
				createElement( ContentAI ),
				metaboxElem
			)
		}, 1000 )
	}

	if ( 'gutenberg' === rankMath.currentEditor ) {
		registerPlugin( 'rank-math-content-ai-box', {
			render() {
				const score = select( 'rank-math' ).getContentAIScore()
				if ( ! isEmpty( score ) || ! isEmpty( rankMath.ca_keyword ) ) {
					return false
				}

				return (
					<PluginPrePublishPanel
						title={ __( 'Content AI', 'rank-math' ) }
						icon="rm-icon rm-icon-target"
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

		if ( 'gutenberg' === rankMath.currentEditor ) {
			const RankMathContentAISidebar = () => (
				<Fragment>
					<PluginSidebarMoreMenuItem
						target="seo-by-rank-math-content-ai-sidebar"
						icon={ <ContentAIIcon /> }
					>
						{ __( 'Content AI', 'rank-math' ) }
					</PluginSidebarMoreMenuItem>
					<PluginSidebar
						name="seo-by-rank-math-content-ai-sidebar"
						title={ __( 'Content AI', 'rank-math' ) }
						className="rank-math-sidebar-content-ai-panel"
					>
						<ContentAI showMinimal={ true } />
					</PluginSidebar>
				</Fragment>
			)

			setTimeout( () => {
				registerPlugin( 'rank-math-content-ai', {
					icon: <ContentAIIcon />,
					render: RankMathContentAISidebar,
				} )
			}, 1000 )
		}
	}

	if ( 'classic' !== rankMath.currentEditor ) {
		jQuery( document ).on( 'click', '.rank-math-open-contentai', ( e ) => {
			e.preventDefault()
			jQuery( '.rank-math-content-ai-tab' ).trigger( 'click' )
			return false
		} )
	}

	new ContentAnalysis()
} )
