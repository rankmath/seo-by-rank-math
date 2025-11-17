/**
 * External dependencies
 */
import { has, forEach, isUndefined, pick } from 'lodash'

/**
 * Internal dependencies
 */
import Researcher from '@root/analyzer/Researcher'
import ContentHasAssets from '@analysis/contentHasAssets'
import ContentHasShortParagraphs from '@analysis/contentHasShortParagraphs'
import ContentHasTOC from '@analysis/contentHasTOC'
import KeywordDensity from '@analysis/keywordDensity'
import KeywordIn10Percent from '@analysis/keywordIn10Percent'
import KeywordInContent from '@analysis/keywordInContent'
import KeywordInImageAlt from '@analysis/keywordInImageAlt'
import KeywordInMetaDescription from '@analysis/keywordInMetaDescription'
import KeywordInPermalink from '@analysis/keywordInPermalink'
import KeywordInSubheadings from '@analysis/keywordInSubheadings'
import KeywordInTitle from '@analysis/keywordInTitle'
import KeywordNotUsed from '@analysis/keywordNotUsed'
import LengthContent from '@analysis/lengthContent'
import LengthPermalink from '@analysis/lengthPermalink'
import LinksHasExternals from '@analysis/linksHasExternals'
import LinksHasInternal from '@analysis/linksHasInternal'
import LinksNotAllExternals from '@analysis/linksNotAllExternals'
import TitleHasNumber from '@analysis/titleHasNumber'
import TitleHasPowerWords from '@analysis/titleHasPowerWords'
import TitleSentiment from '@analysis/titleSentiment'
import TitleStartWithKeyword from '@analysis/titleStartWithKeyword'
import ContentAI from '@analysis/contentAI'
import isReviewEnabled from '@analysis/isReviewEnabled'
import ProductSchema from '@analysis/ProductSchema'

/**
 * Creates the Analyzer.
 */
class Analyzer {
	/**
	 * Constructor
	 *
	 * @param {Object} options Options for analyzer.
	 */
	constructor( options ) {
		this.options = options
		this.researcher = has( options, 'researcher' ) ? options.researcher : new Researcher
		// this.setI18n( has( options, 'i18n' ) ? options.i18n : undefined )
		this.setAnalyses()
	}

	/**
	 * Runs the analyses defined in the tasklist or the default analyses.
	 *
	 * @param {Paper} paper The paper to run analyses on.
	 *
	 * @return {Promise} Promise object.
	 */
	analyze( paper ) {
		return this.generateResults( this.analyses, paper )
	}

	/**
	 * Runs the analyses defined.
	 *
	 * @param {Array} analyses List of analyses to run.
	 * @param {Paper} paper    The paper to run analyses on.
	 *
	 * @return {Promise} Promise object.
	 */
	analyzeSome( analyses, paper ) {
		return this.generateResults( pick( this.defaultAnalyses, analyses ), paper )
	}

	/**
	 * Generate results.
	 *
	 * @param {Array} analyses List of analyses to run.
	 * @param {Paper} paper    The paper to run analyses on.
	 *
	 * @return {Promise} Promise object.
	 */
	generateResults( analyses, paper ) {
		return new Promise( ( resolve ) => {
			this.results = {}
			this.researcher.setPaper( paper )
			forEach( analyses, ( analysis, analysisId ) => {
				const result = analysis.isApplicable( paper, this.researcher ) ?
					analysis.getResult( paper, this.researcher ) :
					analysis.newResult( paper )

				if ( null !== result ) {
					this.results[ analysisId ] = result
				}
			} )

			resolve( this.results )
		} )
	}

	/**
	 * Set i18n object.
	 *
	 * @param {Object} i18n The i18n object used for translations.
	 *
	 * @throws {Error} Parameter needs to be a valid i18n object.
	 */
	// setI18n( i18n ) {
	// 	if ( isUndefined( i18n ) ) {
	// 		throw new Error( 'The assessor requires an i18n object.' )
	// 	}

	// 	this.i18n = i18n
	// }

	/**
	 * Set analyses.
	 */
	setAnalyses() {
		this.defaultAnalyses = {
			contentHasAssets: new ContentHasAssets,
			contentHasShortParagraphs: new ContentHasShortParagraphs,
			contentHasTOC: new ContentHasTOC,
			keywordDensity: new KeywordDensity,
			keywordIn10Percent: new KeywordIn10Percent,
			keywordInContent: new KeywordInContent,
			keywordInImageAlt: new KeywordInImageAlt,
			keywordInMetaDescription: new KeywordInMetaDescription,
			keywordInPermalink: new KeywordInPermalink,
			keywordInSubheadings: new KeywordInSubheadings,
			keywordInTitle: new KeywordInTitle,
			keywordNotUsed: new KeywordNotUsed,
			lengthContent: new LengthContent,
			lengthPermalink: new LengthPermalink,
			linksHasExternals: new LinksHasExternals,
			linksHasInternal: new LinksHasInternal,
			linksNotAllExternals: new LinksNotAllExternals,
			titleHasNumber: new TitleHasNumber,
			titleHasPowerWords: new TitleHasPowerWords,
			titleSentiment: new TitleSentiment,
			titleStartWithKeyword: new TitleStartWithKeyword,
			hasContentAI: new ContentAI,
			isReviewEnabled: new isReviewEnabled,
			hasProductSchema: new ProductSchema,
		}

		this.analyses = this.defaultAnalyses
		if ( has( this.options, 'analyses' ) && ! isUndefined( this.options.analyses ) ) {
			this.analyses = pick( this.defaultAnalyses, this.options.analyses )
		}
	}
}

export default Analyzer
