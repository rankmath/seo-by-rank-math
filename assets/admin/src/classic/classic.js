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
import GutenbergDataCollector from '@classic/collectors/gutenbergDataCollector'
import PostCollector from '@classic/collectors/PostCollector'
import TermCollector from '@classic/collectors/TermCollector'
import UserCollector from '@classic/collectors/UserCollector'

/**
 * Components
 */
import SerpPreview from '@classic/components/SerpPreview'
import SocialFields from '@classic/components/SocialFields'
import richSnippet from '@classic/components/richSnippet'
import LinkSuggestions from '@classic/components/LinkSuggestions'
import PrimaryTerm from '@classic/components/PrimaryTerm'
import FocusKeywords from '@classic/components/FocusKeywords'
import FeaturedImage from '@classic/components/FeaturedImage'
import CheckLists from '@classic/components/CheckLists'

class ClassicEditor {
	setup() {
		this.elemMetabox = jQuery( '#cmb2-metabox-rank_math_metabox' )
		this.resultManager = new ResultManager()
		this.assessor = new Assessor( this.getCollector() )

		new CommonFilters()
		this.registerComponents = this.registerComponents.bind( this )
		addAction( 'rank_math_loaded', 'rank-math', this.registerComponents, 1 )
	}

	registerComponents() {
		this.components = {}

		this.components.SerpPreview = new SerpPreview()
		this.components.socialFields = new SocialFields()
		this.components.richSnippet = new richSnippet()
		this.components.linkSuggestions = new LinkSuggestions()
		this.components.primaryTerm = new PrimaryTerm()
		this.components.focusKeywords = new FocusKeywords()
		this.components.featuredImage = new FeaturedImage()
		this.components.checkLists = new CheckLists()

		rankMathAdmin.variableInserter( false )
	}

	refresh( what ) {
		this.assessor.refresh( what )
	}

	updatePermalink( slug ) {
		slug = this.assessor.getResearch( 'slugify' )( slug )
		this.assessor.dataCollector.handleSlugChange( slug )
	}

	getPrimaryKeyword() {
		return Helpers.removeDiacritics(
			this.components.focusKeywords.getFocusKeywords()[ 0 ]
		)
	}

	getSelectedKeyword() {
		const keyword = this.components.focusKeywords.getSelectedKeyword()
		if ( isUndefined( keyword ) ) {
			return ''
		}

		return Helpers.removeDiacritics(
			this.components.focusKeywords.getSelectedKeyword()
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

jQuery( document ).ready( () => {
	getStore()
	window.rankMathEditor = new ClassicEditor()
	window.rankMathEditor.setup()
	window.RankMathApp = new RankMathApp()
} )

jQuery( window ).on( 'load', () => {
	doAction( 'rank_math_loaded' )
} )
