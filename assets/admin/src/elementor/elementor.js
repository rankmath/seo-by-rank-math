/**
 * External dependencies
 */
import jQuery from 'jquery'

/**
 * WordPress dependencies
 */
import { dispatch } from '@wordpress/data'
import { MediaUpload } from '@wordpress/media-utils'
import { addAction, addFilter } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import Editor from '../rankMathEditor'
import DataCollector from './DataCollector'
import ElementorAddRegion from './AddRegion'
import UIThemeComponent from './UIThemeComponent'

/**
 * Slots
 */
import RankMathAfterEditor from '@slots/AfterEditor'
import RankMathAdvancedTab from '@slots/AdvancedTab'
import RankMathAfterFocusKeyword from '@slots/AfterFocusKeyword'

const replaceMediaUpload = () => MediaUpload

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

jQuery( function() {
	window.rankMathEditor = new ElementorEditor()
	window.rankMathGutenberg = window.rankMathEditor
	new UIThemeComponent()
	elementor.once( 'preview:loaded', function() {
		$e.components
			.get( 'panel/elements' )
			.addTab( 'rank-math', { title: 'SEO' } )

		window.rankMathEditor.setup( new DataCollector() )
		dispatch( 'rank-math' ).refreshResults()
	} )
} )

jQuery( window ).on( 'elementor:init', function() {
	elementor.hooks.addFilter(
		'panel/elements/regionViews',
		ElementorAddRegion
	)
} )