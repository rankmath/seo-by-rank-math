/**
 * External dependencies
 */
import jQuery from 'jquery'
import { isUndefined, endsWith } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { dispatch, useSelect, select } from '@wordpress/data'
import { Fragment } from '@wordpress/element'
import { registerPlugin } from '@wordpress/plugins'
import { addAction, addFilter, applyFilters, doAction } from '@wordpress/hooks'
import {
	PluginSidebar,
	PluginSidebarMoreMenuItem,
	PluginPostPublishPanel,
} from '@wordpress/edit-post'
import {
	// eslint-disable-next-line camelcase
	PluginSidebar as FSE_PluginSidebar,
	// eslint-disable-next-line camelcase
	PluginSidebarMoreMenuItem as FSE_PluginSidebarMoreMenuItem,
} from '@wordpress/edit-site'

/**
 * Internal dependencies
 */
import { getStore } from '@root/redux/store'
import RankMathIcon from '@components/AppIcon'
import PrimaryTermSelector from '@components/PrimaryTerm/PrimaryTermSelector'
import LinkSuggestions from '@components/LinkSuggestions'
import isSiteEditorAvailable from '@helpers/isSiteEditorAvailable'
import isSiteEditorHomepage from '@helpers/isSiteEditorHomepage'
import Editor from '../rankMathEditor'
import DataCollector from './DataCollector'
import ReusableBlockAnalysis from './ReusableBlockAnalysis'
import SiteEditor from './SiteEditor'
import PostPublish from '../sidebar/PostPublish'

/**
 * Slots
 */
import RankMathAfterEditor from '@slots/AfterEditor'
import RankMathAdvancedTab from '@slots/AdvancedTab'
import RankMathAfterFocusKeyword from '@slots/AfterFocusKeyword'

/**
 * Conditionally get sidebar on FSE & Gutenberg pages.
 */
const GetSidebar = () => {
	const isSiteEditorPage = useSelect( ( select ) => {
		return isSiteEditorHomepage() ||
		(
			! isUndefined( select( 'core/edit-site' ) ) &&
			endsWith( select( 'core/edit-site' ).getEditedPostId(), '//page' )
		)
	} )

	if ( isSiteEditorAvailable() && isSiteEditorPage ) {
		if ( isSiteEditorHomepage() ) {
			doAction( 'rank_math_id_changed' )
		}

		return (
			<Fragment>
				<FSE_PluginSidebarMoreMenuItem
					target="seo-by-rank-math-sidebar"
					icon={ <RankMathIcon /> }
				>
					{ __( 'Rank Math', 'rank-math' ) }
				</FSE_PluginSidebarMoreMenuItem>
				<FSE_PluginSidebar
					name="seo-by-rank-math-sidebar"
					title={ __( 'Rank Math', 'rank-math' ) }
					className="rank-math-sidebar-panel"
				>
					{
						/* Filter to include components from the common editor file */
						applyFilters( 'rank_math_app', {} )()
					}
				</FSE_PluginSidebar>
			</Fragment>
		)
	}

	return (
		<Fragment>
			<PluginSidebarMoreMenuItem
				target="seo-by-rank-math-sidebar"
				icon={ <RankMathIcon /> }
			>
				{ __( 'Rank Math', 'rank-math' ) }
			</PluginSidebarMoreMenuItem>
			<PluginSidebar
				name="seo-by-rank-math-sidebar"
				title={ __( 'Rank Math', 'rank-math' ) }
				className="rank-math-sidebar-panel"
			>
				{
					/* Filter to include components from the common editor file */
					applyFilters( 'rank_math_app', {} )()
				}
			</PluginSidebar>
		</Fragment>
	)
}

class GutenbergEditor extends Editor {
	setup( dataCollector ) {
		getStore()
		this.registerSlots = this.registerSlots.bind( this )
		addAction( 'rank_math_loaded', 'rank-math', this.registerSlots, 0 )

		super.setup( dataCollector )
		this.registerPostPublish()
		this.registerPrimaryTermSelector()
		new LinkSuggestions()
		new ReusableBlockAnalysis()

		if ( isSiteEditorAvailable() ) {
			new SiteEditor()
		}
		addAction( 'rank_math_data_changed', 'rank-math', this.toggleSettings )
	}

	toggleSettings( key, value, metaKey ) {
		if ( ! metaKey || metaKey === 'rank_math_seo_score' ) {
			return
		}

		const isSaving = select( 'core/editor' ).isSavingPost()
		if ( isSaving ) {
			return
		}

		dispatch( 'core/editor' ).editPost( {
			refreshMe: 'refreshUI',
		} )
	}

	/**
	 * Register slots
	 */
	registerSlots() {
		this.registerSidebar()
		this.RankMathAfterEditor = RankMathAfterEditor
		this.RankMathAfterFocusKeyword = RankMathAfterFocusKeyword
		this.RankMathAdvancedTab = RankMathAdvancedTab
		this.slots = {
			AfterEditor: RankMathAfterEditor,
			AfterFocusKeyword: RankMathAfterFocusKeyword,
			AdvancedTab: RankMathAdvancedTab,
		}
	}

	/**
	 * Register plugin sidebar into gutenberg editor.
	 */
	registerSidebar() {
		registerPlugin( 'rank-math', {
			icon: <RankMathIcon />,
			render: GetSidebar,
		} )
	}

	registerPostPublish() {
		const PostStatus = () => (
			<PluginPostPublishPanel
				initialOpen={ true }
				title={ __( 'Rank Math', 'rank-math' ) }
				className="rank-math-post-publish"
				icon={ <Fragment /> }
			>
				<PostPublish />
			</PluginPostPublishPanel>
		)

		registerPlugin( 'rank-math-post-publish', {
			render: PostStatus,
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
