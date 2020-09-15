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
import RankMathAfterEditor from '@slots/AfterEditor'
import RankMathAfterFocusKeyword from '@slots/AfterFocusKeyword'
import RankMathAdvancedTab from '@slots/AdvancedTab'

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
	window.rankMathEditor.setup( new DataCollector() )
	new UIThemeComponent()
	elementor.once( 'preview:loaded', function() {
		$e.components
			.get( 'panel/elements' )
			.addTab( 'rank-math', { title: 'SEO' } )
		dispatch( 'rank-math' ).refreshResults()
	} )
} )

jQuery( window ).on( 'elementor:init', function() {
	elementor.hooks.addFilter(
		'panel/elements/regionViews',
		ElementorAddRegion
	)
} )
