/*
 * External dependencies
 */
import jQuery from 'jquery'
import { isUndefined } from 'lodash'
import { ResultManager, Helpers } from '@rankMath/analyzer'

/**
 * WordPress dependencies
 */
import { addAction, doAction } from '@wordpress/hooks'
import { dispatch } from '@wordpress/data'
import { createElement, render } from '@wordpress/element'

/**
 * Internal dependencies
 */
import Assessor from '@classic/Assessor'
import isGutenbergAvailable from '@helpers/isGutenbergAvailable'
import CommonFilters from '../commonFilters'
import RankMathApp from '../rankMathApp'

/**
 * Data Collectors
 */
import { getStore } from '@root/redux/store'
import PostCollector from '@classic/collectors/PostCollector'
import TermCollector from '@classic/collectors/TermCollector'
import UserCollector from '@classic/collectors/UserCollector'
import GutenbergDataCollector from './../gutenberg/DataCollector'

/**
 * Components
 */
import LinkSuggestions from '@classic/components/LinkSuggestions'
import PrimaryTerm from '@classic/components/PrimaryTerm'
import FeaturedImage from '@classic/components/FeaturedImage'

import App from '../sidebar/App'

import RankMathAfterEditor from '@slots/AfterEditor'
import RankMathAdvancedTab from '@slots/AdvancedTab'
import RankMathAfterFocusKeyword from '@slots/AfterFocusKeyword'

class ClassicEditor {
	setup() {
		getStore()
		this.registerSlots = this.registerSlots.bind( this )
		addAction( 'rank_math_loaded', 'rank-math', this.registerSlots, 0 )

		this.resultManager = new ResultManager()
		this.assessor = new Assessor( this.getCollector() )

		new CommonFilters()
		this.registerComponents = this.registerComponents.bind( this )
		addAction( 'rank_math_loaded', 'rank-math', this.registerComponents, 11 )
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

	registerComponents() {
		this.components = {}

		this.components.linkSuggestions = new LinkSuggestions()
		this.components.primaryTerm = new PrimaryTerm()
		this.components.featuredImage = new FeaturedImage()

		rankMathAdmin.variableInserter( false )
		setTimeout( () => {
			render(
				createElement( App ),
				document.getElementById( 'rank-math-metabox-wrapper' )
			)
		}, 1000 )
	}

	refresh( what ) {
		this.assessor.refresh( what )
	}

	updatePermalink( slug ) {
		slug = this.assessor.getResearch( 'slugify' )( slug )
		this.assessor.dataCollector.handleSlugChange( slug )
		dispatch( 'rank-math' ).updatePermalink( slug )
	}

	updatePermalinkSanitize( slug ) {
		this.updatePermalink( slug )
	}

	getPrimaryKeyword() {
		return Helpers.removeDiacritics(
			this.assessor.getPrimaryKeyword()
		)
	}

	getSelectedKeyword() {
		const keyword = this.assessor.getSelectedKeyword()
		if ( isUndefined( keyword ) ) {
			return ''
		}

		return Helpers.removeDiacritics(
			keyword
		)
	}

	getCollector() {
		if ( 'post' === rankMath.objectType ) {
			if ( isGutenbergAvailable() ) {
				return new GutenbergDataCollector()
			}

			return new PostCollector( this )
		}

		if ( 'term' === rankMath.objectType ) {
			return new TermCollector()
		}

		if ( 'user' === rankMath.objectType ) {
			return new UserCollector()
		}
	}
}

jQuery( () => {
	window.rankMathEditor = new ClassicEditor()
	window.rankMathEditor.setup()
	window.RankMathApp = new RankMathApp()
} )

jQuery( window ).on( 'load', () => {
	jQuery.when( jQuery.ready ).then( () => {
		doAction( 'rank_math_loaded' )
	} )
} )
