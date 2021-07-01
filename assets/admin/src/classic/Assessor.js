/**
 * External dependencies
 */
import { debounce, isUndefined, filter, intersection } from 'lodash'
import { Analyzer, Paper, Helpers } from '@rankMath/analyzer'

/**
 * WordPress dependencies
 */
import * as i18n from '@wordpress/i18n'
import { doAction, addAction, applyFilters } from '@wordpress/hooks'

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
		this.promises = []

		this.hooks()
		this.registerRefresh()
	}

	hooks() {
		this.assessAll = this.assessAll.bind( this )
		this.assessTitle = this.assessTitle.bind( this )
		this.assessContent = this.assessContent.bind( this )
		this.assessPermalink = this.assessPermalink.bind( this )
		this.updateKeywordResult = this.updateKeywordResult.bind( this )
		this.assessKeyword = this.assessKeyword.bind( this )
		this.assessThumbnail = this.assessThumbnail.bind( this )

		addAction(
			'rankMath_analysis_keywordUsage_updated',
			'rank-math',
			this.updateKeywordResult
		)
		addAction( 'rank_math_init_refresh', 'rank-math', this.assessAll )
		addAction( 'rank_math_title_refresh', 'rank-math', this.assessTitle )
		addAction(
			'rank_math_content_refresh',
			'rank-math',
			this.assessContent,
			11
		)
		addAction(
			'rank_math_permalink_refresh',
			'rank-math',
			this.assessPermalink,
			11
		)
		addAction(
			'rank_math_featuredImage_refresh',
			'rank-math',
			this.assessThumbnail,
			11
		)
		addAction(
			'rank_math_keyword_refresh',
			'rank-math',
			this.assessKeyword,
			12
		)
	}

	getPaper( keyword ) {
		const data = this.dataCollector.getData()
		const paper = new Paper( '', { locale: rankMath.localeFull } )

		paper.setTitle( data.title )
		paper.setPermalink( data.slug )
		paper.setDescription( data.description )
		paper.setUrl( data.permalink )
		paper.setText( applyFilters( 'rank_math_content', data.content ) )
		paper.setKeyword( Helpers.removeDiacritics( keyword ) )
		paper.setKeywords( rankMathEditor.components.focusKeywords.getFocusKeywords() )

		if ( ! isUndefined( data.featuredImage ) ) {
			paper.setThumbnail( data.featuredImage.source_url )
			paper.setThumbnailAltText(
				Helpers.removeDiacritics( data.featuredImage.alt_text )
			)
		}

		return paper
	}

	registerRefresh() {
		this.refresh = debounce( ( what ) => {
			this.promises = []
			doAction( 'rank_math_' + what + '_refresh' )

			Promise.all( this.promises ).then( () => {
				this.refreshResults()
			} )
		}, 500 )
	}

	assessAll() {
		const keywords = rankMathEditor.components.focusKeywords.getFocusKeywords()
		/*eslint array-callback-return: 0*/
		keywords.map( ( keyword, index ) => {
			this.run(
				0 === index
					? rankMath.assessor.researchesTests
					: this.getSecondaryKeywordTests(),
				keyword,
				0 === index
			)
		} )
	}

	run( researches, keyword = '', isPrimary = false ) {
		keyword = keyword ? keyword : rankMathEditor.getSelectedKeyword()
		const paper = this.getPaper( keyword )
		this.promises.push(
			this.analyzer
				.analyzeSome( this.filterTests( researches ), paper )
				.then( ( results ) => {
					rankMathEditor.resultManager.update(
						paper.getKeyword(),
						results,
						isPrimary
					)
				} )
		)
	}

	updateKeywordResult( keyword, result ) {
		rankMathEditor.resultManager.update( keyword, {
			keywordNotUsed: result,
		} )

		if ( keyword === rankMathEditor.getSelectedKeyword().toLowerCase() ) {
			this.refreshResults()
		}
	}

	assessThumbnail() {
		this.run( [ 'keywordInImageAlt', 'contentHasAssets' ] )
	}

	assessKeyword() {
		const isPrimary =
			rankMathEditor.getSelectedKeyword() ===
			rankMathEditor.getPrimaryKeyword()
		this.run(
			isPrimary
				? rankMath.assessor.researchesTests
				: this.getSecondaryKeywordTests()
		)
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

	assessTitle() {
		this.run( [
			'keywordInTitle',
			'titleHasPowerWords',
			'titleHasNumber',
			'titleSentiment',
			'titleStartWithKeyword',
		] )
	}

	assessContent() {
		return this.run( [
			'contentHasShortParagraphs',
			'contentHasTOC',
			'contentHasAssets',
			'keywordDensity',
			'keywordIn10Percent',
			'keywordInContent',
			'keywordInImageAlt',
			'keywordInMetaDescription',
			'keywordInSubheadings',
			'lengthContent',
			'linksHasExternals',
			'linksHasInternal',
			'linksNotAllExternals',
		] )
	}

	assessPermalink() {
		return this.run( [ 'keywordInPermalink', 'lengthPermalink' ] )
	}

	getPrimaryKeyword() {
		return rankMathEditor.components.focusKeywords.getPrimaryKeyword()
	}

	getSelectedKeyword() {
		return rankMathEditor.components.focusKeywords.getSelectedKeyword()
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

	refreshResults() {
		doAction( 'rank_math_refresh_results' )
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
