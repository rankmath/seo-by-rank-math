/**
 * External dependencies
 */
import jQuery from 'jquery'

/**
 * WordPress dependencies
 */
import * as i18n from '@wordpress/i18n'
import { dispatch } from '@wordpress/data'
import { Fragment } from '@wordpress/element'
import { registerPlugin } from '@wordpress/plugins'
import { addAction, addFilter } from '@wordpress/hooks'
import {
	PluginSidebar,
	PluginSidebarMoreMenuItem,
	PluginPostPublishPanel,
} from '@wordpress/edit-post'

/**
 * Internal dependencies
 */
import App from '../sidebar/App'
import Editor from '../rankMathEditor'
import DataCollector from './DataCollector'
import RankMathIcon from '@components/AppIcon'
import PostPublish from '../sidebar/PostPublish'
import PrimaryTermSelector from '@components/PrimaryTerm/PrimaryTermSelector'

/**
 * Slots
 */
import RankMathAfterEditor from '@slots/AfterEditor'
import RankMathAfterFocusKeyword from '@slots/AfterFocusKeyword'

class GutenbergEditor extends Editor {
	setup( dataCollector ) {
		this.registerSlots = this.registerSlots.bind( this )
		addAction( 'rank_math_loaded', 'rank-math', this.registerSlots, 0 )

		super.setup( dataCollector )
		this.registerSidebar()
		this.registerPostPublish()
		this.registerPrimaryTermSelector()
	}

	/**
	 * Register slots.
	 */
	registerSlots() {
		this.RankMathAfterEditor = RankMathAfterEditor
		this.RankMathAfterFocusKeyword = RankMathAfterFocusKeyword
		this.slots = {
			AfterEditor: RankMathAfterEditor,
			AfterFocusKeyword: RankMathAfterFocusKeyword,
		}
	}

	/**
	 * Register plugin sidebar into gutenberg editor.
	 */
	registerSidebar() {
		const RankMathSidebar = () => (
			<Fragment>
				<PluginSidebarMoreMenuItem
					target="seo-by-rank-math-sidebar"
					icon={ <RankMathIcon /> }
				>
					{ i18n.__( 'Rank Math', 'rank-math' ) }
				</PluginSidebarMoreMenuItem>
				<PluginSidebar
					name="seo-by-rank-math-sidebar"
					title={ i18n.__( 'Rank Math', 'rank-math' ) }
					className="rank-math-sidebar-panel"
				>
					<App />
				</PluginSidebar>
			</Fragment>
		)

		registerPlugin( 'rank-math', {
			icon: <RankMathIcon />,
			render: RankMathSidebar,
		} )
	}

	registerPostPublish() {
		const PostStatus = () => (
			<PluginPostPublishPanel
				initialOpen={ true }
				title={ i18n.__( 'Rank Math', 'rank-math' ) }
				className="rank-math-post-publish"
				icon={ <Fragment /> }
			>
				<PostPublish />
			</PluginPostPublishPanel>
		)

		registerPlugin( 'rank-math-post-publish', {
			render: PostStatus
		} )
	}

	registerPrimaryTermSelector() {
		addFilter(
			'editor.PostTaxonomyType',
			'rank-math',
			( PostTaxonomies ) => ( props ) => (
				<PrimaryTermSelector
					TermComponent={ PostTaxonomies }
					{ ...props }
				/>
			)
		)
	}

	updatePermalink( slug ) {
		dispatch( 'core/editor' ).editPost( { slug } )
	}

	updatePermalinkSanitize( slug ) {
		slug = this.assessor.getResearch( 'slugify' )( slug )
		dispatch( 'core/editor' ).editPost( { slug } )
	}
}

jQuery( document ).ready( function() {
	window.rankMathEditor = new GutenbergEditor()
	window.rankMathGutenberg = window.rankMathEditor
	window.rankMathEditor.setup( new DataCollector() )
} )
