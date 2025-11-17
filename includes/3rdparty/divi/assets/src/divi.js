/**
 * External dependencies
 */
import jQuery from 'jquery'

/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch'
import { dispatch } from '@wordpress/data'
import { MediaUpload } from '@wordpress/media-utils'
import { createElement, createRoot } from '@wordpress/element'
import { addAction, addFilter } from '@wordpress/hooks'

/**
 * Slots
 */
import RankMathAfterEditor from '@slots/AfterEditor'
import RankMathAdvancedTab from '@slots/AdvancedTab'
import RankMathAfterFocusKeyword from '@slots/AfterFocusKeyword'

/**
 * Internal dependencies
 */
import Editor from '../../../../../assets/admin/src/rankMathEditor'
import DataCollector from './DataCollector'
import rankMathSettingsBar from './settings-bar'
import rankMathPrimaryTerm from './primary-term'
import App from './components/App'

const replaceMediaUpload = () => MediaUpload

class DiviEditor extends Editor {
	setup( dataCollector ) {
		apiFetch.use( apiFetch.createRootURLMiddleware( rankMath.api.root ) )
		apiFetch.use( apiFetch.createNonceMiddleware( rankMath.restNonce ) )
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

window.addEventListener( 'message', function( event ) {
	if ( 'et_builder_api_ready' === event.data.etBuilderEvent ) {
		wp.i18n.setLocaleData( wp.i18n.getLocaleData( 'seo-by-rank-math' ), 'rank-math' )
		window.rankMathEditor = new DiviEditor()
		window.rankMathGutenberg = window.rankMathEditor
		window.rankMathEditor.setup( new DataCollector() )
		rankMathSettingsBar.init()
		new rankMathPrimaryTerm()
		createRoot( document.getElementById( 'rank-math-rm-app-root' ) ).render( createElement( App ) )
		dispatch( 'rank-math' ).refreshResults()
		jQuery( '.rank-math-rm-modal' ).draggable()
	}
} )
