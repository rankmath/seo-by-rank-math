/**
 * External dependencies
 */
import jQuery from 'jquery'

/**
 * WordPress dependencies
 */
import { dispatch } from '@wordpress/data'
import { MediaUpload } from '@wordpress/media-utils'
import { addAction, addFilter, doAction } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import Editor from '../../../../../assets/admin/src/rankMathEditor'
import DataCollector from './DataCollector'
import ElementorAddRegion from './AddRegion'
import UIThemeComponent from './UIThemeComponent'
import LockModifiedDate from './LockModifiedDate'
import { getContentAIOriginalBlocks, updateWithAIGeneratedContent, contentTestApprove, contentTestReject } from './contentAIHelpers'

/**
 * Slots
 */
import RankMathAfterEditor from '@slots/AfterEditor'
import RankMathAdvancedTab from '@slots/AdvancedTab'
import RankMathAfterFocusKeyword from '@slots/AfterFocusKeyword'
import apiFetch from '@wordpress/api-fetch'

const replaceMediaUpload = () => MediaUpload
let referencesForWidgetIds = {}

class ElementorEditor extends Editor {
	setup( dataCollector ) {
		this.registerSlots = this.registerSlots.bind( this )
		addAction( 'rank_math_loaded', 'rank-math', this.registerSlots, 0 )

		addFilter(
			'editor.MediaUpload',
			'rank-math/replace-media-upload',
			replaceMediaUpload
		)

		super.setup( dataCollector )
	}

	/**
	 * Register slots.
	 */
	registerSlots() {
		this.RankMathAfterEditor = RankMathAfterEditor
		this.RankMathAfterFocusKeyword = RankMathAfterFocusKeyword
		this.RankMathAdvancedTab = RankMathAdvancedTab
		this.slots = {
			AfterEditor: RankMathAfterEditor,
			AfterFocusKeyword: RankMathAfterFocusKeyword,
			AdvancedTab: RankMathAdvancedTab,
		}
	}

	updatePermalink( slug ) {
		dispatch( 'rank-math' ).updatePermalink( slug )
	}

	updatePermalinkSanitize( slug ) {
		slug = this.assessor.getResearch( 'slugify' )( slug )
		dispatch( 'rank-math' ).updatePermalink( slug )
	}
}

function resetReferencesForWidgetIds() {
	referencesForWidgetIds = {}
}

new LockModifiedDate()
jQuery( function() {
	window.rankMathEditor = new ElementorEditor()
	window.rankMathGutenberg = window.rankMathEditor
	new UIThemeComponent()

	elementor.once( 'preview:loaded', function() {
		$e.components
			.get( 'panel/elements' )
			.addTab( 'rank-math', { title: 'SEO' } )

		window.rankMathDataCollector = new DataCollector()
		window.rankMathEditor.setup( window.rankMathDataCollector )
		dispatch( 'rank-math' ).refreshResults()
	} )

	let postID = null
	elementor.on( 'preview:loaded', async function() {
		addFilter( 'rank_math_content_ai_payload_blocks', 'rank-math', getContentAIOriginalBlocks )
		addFilter( 'rank_math_content_ai_original_blocks', 'rank-math', getContentAIOriginalBlocks )
		addAction( 'rank_math_replace_ai_content', 'rank-math', ( apiResponse ) => {
			updateWithAIGeneratedContent( apiResponse, referencesForWidgetIds )
		} )
		addAction( 'rank_math_content_test_approve', 'rank-math', () => {
			contentTestApprove()
			resetReferencesForWidgetIds()
		} )
		addAction( 'rank_math_content_test_reject', 'rank-math', ( originalBlocks ) => {
			contentTestReject( originalBlocks )
			resetReferencesForWidgetIds()
		} )

		const document = elementor.config.document
		const currentId = document.id

		if ( ! currentId || ! document.type ) {
			return
		}

		if ( ! postID || currentId === postID ) {
			postID = currentId
			return
		}

		postID = currentId

		const { slug } = await apiFetch( {
			path: `/wp/v2/${ document.type.replace( 'wp-', '' ) + 's' }/${ postID }?_fields=slug`,
		} )
		dispatch( 'rank-math' ).updatePermalink( slug )
		dispatch( 'rank-math' ).refreshResults()
	} )
} )

jQuery( window ).on( 'elementor:init', function() {
	elementor.hooks.addFilter(
		'panel/elements/regionViews',
		ElementorAddRegion
	)

	/**
	 * @copyright Copyright (C) Elementor
	 * The following code is a derivative work of the code from the Elementor(https://github.com/elementor/elementor/), which is licensed under GPL v3.
	 *
	 * @link https://github.com/elementor/elementor/issues/20055#issuecomment-1282415610
	 */
	class RankMathElementorUpdaterBeforeSave extends $e.modules.hookUI.Before {
		getCommand() {
			return 'document/save/save'
		}

		getId() {
			return 'custom-updater-before-save'
		}

		getConditions() {
			return true
		}

		apply( args, result ) {
			doAction( 'rank_math_elementor_before_save', args, result )
		}
	}

	$e.hooks.registerUIBefore( new RankMathElementorUpdaterBeforeSave() )
} )
