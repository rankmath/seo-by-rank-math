/**
 * External dependencies
 */
import { debounce, isEmpty, isUndefined, isObject, intersection } from 'lodash'

/**
 * WordPress dependencies
 */
import * as i18n from '@wordpress/i18n'
import { select, dispatch } from '@wordpress/data'
import { addAction, addFilter, doAction, applyFilters } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import { Analyzer, Paper } from '@rankMath/analyzer'
import { sanitizeAppData } from '@helpers/cleanText'
import removeDiacritics from '@helpers/removeDiacritics'
import { getStore } from '../redux/store'
import unescape from '@helpers/unescape'

class Assessor {
	/**
	 * Class constructor
	 *
	 * @param {Object} dataCollector DataCollector for assessment.
	 */
	constructor( dataCollector ) {
		this.analyzer = new Analyzer( {
			i18n,
			analyses: rankMath.assessor.researchesTests,
		} )
		this.dataCollector = dataCollector
		this.registerRefresh()

		this.updateKeywordResult = this.updateKeywordResult.bind( this )
		this.sanitizeData = this.sanitizeData.bind( this )
		addAction(
			'rankMath_analysis_keywordUsage_updated',
			'rank-math',
			this.updateKeywordResult
		)
		addFilter(
			'rank_math_sanitize_meta_value',
			'rank-math',
			this.sanitizeData
		)
		addFilter( 'rank_math_sanitize_data', 'rank-math', this.sanitizeData )
	}

	updateKeywordResult( keyword, result ) {
		rankMathEditor.resultManager.update( keyword, {
			keywordNotUsed: result,
		} )
		if ( keyword === this.getSelectedKeyword().toLowerCase() ) {
			dispatch( 'rank-math' ).refreshResults()
		}
	}

	sanitizeData( value, key ) {
		// TODO: move it to helper itself
		if ( 'schemas' === key || isObject( value ) ) {
			return value
		}

		return isEmpty( value ) ? value : sanitizeAppData( value )
	}

	getPaper( keyword, keywords ) {
		const store = getStore().getState()
		const gutenbergData = this.dataCollector.getData()
		const paper = new Paper( '', { locale: rankMath.localeFull } )

		paper.setTitle( store.appUi.serpTitle )
		paper.setPermalink( gutenbergData.slug )
		paper.setDescription( store.appUi.serpDescription )
		paper.setUrl( gutenbergData.permalink )
		paper.setText(
			unescape( applyFilters( 'rank_math_content', gutenbergData.content ) )
		)
		paper.setKeyword( keyword )
		paper.setKeywords( keywords )
		paper.setSchema( store.appData.schemas )

		if ( ! isUndefined( gutenbergData.featuredImage ) ) {
			paper.setThumbnail( gutenbergData.featuredImage.source_url )
			paper.setThumbnailAltText(
				removeDiacritics( gutenbergData.featuredImage.alt_text )
			)
		}

		const contentAIStore = select( 'rank-math-content-ai' )
		if ( ! isEmpty( contentAIStore ) ) {
			const contentAiData = contentAIStore.getData()
			const contentAiScore = contentAIStore.getScore()
			paper.setContentAI( contentAiScore || ! isEmpty( contentAiData.keyword ) )
		}

		return paper
	}

	registerRefresh() {
		this.refresh = debounce( ( what ) => {
			const store = getStore().getState()

			if ( false === store.appUi.isLoaded ) {
				return
			}

			const keywords = store.appData.keywords.split( ',' )
			const promises = []

			doAction( 'rank_math_' + what + '_refresh' )

			/*eslint array-callback-return: 0*/
			keywords.map( ( keyword, index ) => {
				const paper = this.getPaper(
					removeDiacritics( keyword ),
					keywords
				)

				const researches =
					0 === index
						? rankMath.assessor.researchesTests
						: this.filterTests( this.getSecondaryKeywordTests() )
				promises.push(
					this.analyzer
						.analyzeSome( researches, paper )
						.then( ( results ) => {
							rankMathEditor.resultManager.update(
								paper.getKeyword(),
								results,
								0 === index
							)

							if ( 0 === index ) {
								dispatch( 'rank-math' ).updateAnalysisScore(
									rankMathEditor.resultManager.getScore(
										paper.getKeyword()
									)
								)
							}
						} )
				)

				Promise.all( promises ).then( () => {
					dispatch( 'rank-math' ).refreshResults()
				} )
			} )
		}, 500 )
	}

	getSecondaryKeywordTests() {
		return [
			'keywordInContent',
			'lengthContent',
			'keywordInSubheadings',
			'keywordDensity',
			'lengthPermalink',
			'linksHasExternals',
			'linksNotAllExternals',
			'linksHasInternal',
			'titleSentiment',
			'titleHasPowerWords',
			'titleHasNumber',
			'contentHasTOC',
			'contentHasShortParagraphs',
			'contentHasAssets',
		]
	}

	/**
	 * Get orimary keyword.
	 *
	 * @return {string} Primary keyword text.
	 */
	getPrimaryKeyword() {
		const data = getStore().getState()
		const keywords = data.appData.keywords
		return ! keywords ? '' : removeDiacritics( keywords.split( ',' )[ 0 ] )
	}

	/**
	 * Get selected keyword.
	 *
	 * @return {string} Selected keyword text.
	 */
	getSelectedKeyword() {
		const data = getStore().getState()
		const keyword =
			'' !== data.appUi.selectedKeyword.data.value
				? data.appUi.selectedKeyword.data.value
				: data.appData.keywords.split( ',' )[ 0 ]

		return removeDiacritics( keyword )
	}

	/**
	 * Return the Research by name.
	 *
	 * @param {string} name The name to reference the research by.
	 *
	 * @return {*} Returns the result of the research or false if research does not exist.
	 */
	getResearch( name ) {
		return this.analyzer.researcher.getResearch( name )
	}

	/**
	 * Filter the Researcher tests.
	 *
	 * @param { Array } tests Tests.
	 *
	 * @return {Array} Filter Researches tests.
	 */
	filterTests( tests ) {
		return intersection( tests, rankMath.assessor.researchesTests )
	}
}

export default Assessor
