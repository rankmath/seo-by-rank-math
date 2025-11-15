/**
 * External dependencies
 */
import { defaults, has, map, isUndefined, filter } from 'lodash'

/**
 * Internal dependencies
 */
import { cleanHTML, cleanText } from '@helpers/cleanText'
import normalizeQuotes from '@helpers/normalizeQuotes'
import removeDiacritics from '@helpers/removeDiacritics'
import replaceInvalid from '@helpers/replaceInvalid'

class Paper {
	/**
	 * Arguments.
	 */
	args = {}

	/**
	 * The constructor.
	 *
	 * @param {string} text                The text to use in the analysis.
	 * @param {Object} args                The object containing all arguments.
	 * @param {Object} [args.title]        The SEO title.
	 * @param {Object} [args.keyword]      The main keyword.
	 * @param {Object} [args.titleWidth]   The width of the title in pixels.
	 * @param {Object} [args.url]          The base url + slug.
	 * @param {Object} [args.permalink]    The slug.
	 * @param {Object} [args.description]  The SEO description.
	 * @param {Object} [args.locale]       The locale.
	 * @param {Object} [args.thumbnail]    The thumbnail.
	 * @param {Object} [args.thumbnailAlt] The thumbnail alt text.
	 */
	constructor( text, args ) {
		args = args || {}
		this.args = defaults( args, {
			title: '',
			keyword: '',
			keywords: [],
			titleWidth: 0,
			url: '',
			permalink: '',
			description: '',
			thumbnail: '',
			thumbnailAlt: '',
			locale: 'en_US',
			contentAI: false,
			schemas: {},
		} )
		this.setText( isUndefined( text ) ? '' : text )
		this.args.shortLocale = this.args.locale.split( '_' )[ 0 ]
	}

	/**
	 * Get argument value.
	 *
	 * @param {string} id Argument id to get value.
	 *
	 * @return {string} Return value.
	 */
	get( id ) {
		return has( this.args, id ) ? this.args[ id ] : ''
	}

	/**
	 * Get argument value in lower-case.
	 *
	 * @param {string} id Argument id to get value.
	 *
	 * @return {string} Return value.
	 */
	getLower( id ) {
		return this.get( id + 'Lower' )
	}

	/**
	 * Check whether a keyword is available.
	 *
	 * @return {boolean} Returns true if the Paper has a keyword.
	 */
	hasKeyword() {
		return '' !== this.args.keyword
	}

	/**
	 * Return the associated keyword or an empty string if no keyword is available.
	 *
	 * @return {boolean} Returns Keywords
	 */
	getKeyword() {
		return this.args.keyword
	}

	/**
	 * Set the keyword.
	 *
	 * @param {string} keyword [description]
	 */
	setKeyword( keyword ) {
		this.args.keyword = replaceInvalid( removeDiacritics( keyword ) )
		this.args.keywordLower = this.args.keyword.toLowerCase()
		this.keywordPlurals = false
		this.keywordPermalink = false
		this.keywordPermalinkRaw = false
		this.keywordCombinations = false
	}

	/**
	 * Set the keywords.
	 *
	 * @param {string} keywords Array of focus keywords.
	 */
	setKeywords( keywords ) {
		this.args.keywords = filter(
			map( keywords, ( keyword ) => {
				return replaceInvalid( removeDiacritics( keyword ) ).toLowerCase()
			} )
		)
	}

	/**
	 * Check whether an title is available
	 *
	 * @return {boolean} Returns true if the Paper has a title.
	 */
	hasTitle() {
		return '' !== this.args.title
	}

	/**
	 * Return the title, or an empty string of no title is available.
	 *
	 * @return {string} Returns the title
	 */
	getTitle() {
		return this.args.title
	}

	/**
	 * Set the title.
	 *
	 * @param {string} title The title
	 */
	setTitle( title ) {
		this.args.title = replaceInvalid( removeDiacritics( normalizeQuotes( title ) ) )
		this.args.titleLower = this.args.title.toLowerCase()
	}

	/**
	 * Check whether an title width in pixels is available
	 *
	 * @return {boolean} Returns true if the Paper has a title.
	 */
	hasTitleWidth() {
		return 0 !== this.args.titleWidth
	}

	/**
	 * Return the title width in pixels, or an empty string of no title width in pixels is available.
	 *
	 * @return {string} Returns the title
	 */
	getTitleWidth() {
		return this.args.titleWidth
	}

	/**
	 * Check whether a permalink is available
	 *
	 * @return {boolean} Returns true if the Paper has a permalink.
	 */
	hasPermalink() {
		return '' !== this.args.permalink
	}

	/**
	 * Return the permalink, or an empty string of no permalink is available.
	 *
	 * @return {string} Returns the permalink.
	 */
	getPermalink() {
		return this.args.permalink
	}

	/**
	 * Set the permalink.
	 *
	 * @param {string} permalink The permalink.
	 */
	setPermalink( permalink ) {
		this.args.permalink = permalink
		this.args.permalinkLower = permalink.toLowerCase()
	}

	/**
	 * Check whether a description is available.
	 *
	 * @return {boolean} Returns true if the paper has a description.
	 */
	hasDescription() {
		return '' !== this.args.description
	}

	/**
	 * Return the description or an empty string if no description is available.
	 *
	 * @return {string} Returns the description.
	 */
	getDescription() {
		return this.args.description
	}

	/**
	 * Set the description.
	 *
	 * @param {string} description The description.
	 */
	setDescription( description ) {
		this.args.description = replaceInvalid( removeDiacritics( cleanText( description ) ) )
		this.args.descriptionLower = this.args.description.toLowerCase()
	}

	/**
	 * Check whether the text is available.
	 *
	 * @return {boolean} Returns true if the paper has a text.
	 */
	hasText() {
		return '' !== this.text
	}

	/**
	 * Return the associated text or am empty string if no text is available.
	 *
	 * @return {string} Returns text
	 */
	getText() {
		return this.text
	}

	/**
	 * Return the associated text or am empty string if no text is available.
	 *
	 * @return {string} Returns text
	 */
	getTextLower() {
		return this.textLower
	}

	/**
	 * Set the text.
	 *
	 * @param {string} text The text.
	 */
	setText( text ) {
		this.text = text || ''
		this.textLower = ''

		if ( '' === text ) {
			return
		}

		this.text = replaceInvalid( removeDiacritics( cleanHTML( text ) ) )
		this.textLower = this.text.toLowerCase()
	}

	/**
	 * Check whether an url is available
	 *
	 * @return {boolean} Returns true if the Paper has an Url.
	 */
	hasUrl() {
		return '' !== this.args.url
	}

	/**
	 * Return the url, or an empty string of no url is available.
	 *
	 * @return {string} Returns the url
	 */
	getUrl() {
		return this.args.url
	}

	/**
	 * Set the url.
	 *
	 * @param {string} url The url.
	 */
	setUrl( url ) {
		this.args.url = url
	}

	/**
	 * Check whether a locale is available
	 *
	 * @return {boolean} Returns true if the paper has a locale
	 */
	hasLocale() {
		return '' !== this.args.locale
	}

	/**
	 * Return the locale or an empty string if no locale is available
	 *
	 * @return {string} Returns the locale
	 */
	getLocale() {
		return this.args.locale
	}

	/**
	 * Return the language code from locale
	 *
	 * @return {string} Returns the locale
	 */
	getShortLocale() {
		return this.args.shortLocale
	}

	/**
	 * Check whether a thumbnail is available
	 *
	 * @return {boolean} Returns true if the Paper has a thumbnail.
	 */
	hasThumbnail() {
		return '' !== this.args.thumbnail
	}

	/**
	 * Return the thumbnail, or an empty string of no thumbnail is available.
	 *
	 * @return {string} Returns the thumbnail.
	 */
	getThumbnail() {
		return this.args.thumbnail
	}

	/**
	 * Set the thumbnail.
	 *
	 * @param {string} thumbnail The thumbnail.
	 */
	setThumbnail( thumbnail ) {
		this.args.thumbnail = thumbnail
	}

	/**
	 * Check whether a thumbnailAlt is available
	 *
	 * @return {boolean} Returns true if the Paper has a thumbnailAlt.
	 */
	hasThumbnailAltText() {
		return '' !== this.args.thumbnailAlt
	}

	/**
	 * Return the thumbnailAlt, or an empty string of no thumbnailAlt is available.
	 *
	 * @return {string} Returns the thumbnailAlt.
	 */
	getThumbnailAltText() {
		return this.args.thumbnailAlt
	}

	/**
	 * Set the thumbnailAlt.
	 *
	 * @param {string} thumbnailAlt The thumbnailAlt.
	 */
	setThumbnailAltText( thumbnailAlt ) {
		this.args.thumbnailAlt = removeDiacritics( thumbnailAlt )
		this.args.thumbnailAltLower = thumbnailAlt.toLowerCase()
	}

	/**
	 * Get keyword as permalink.
	 *
	 * @param {Researcher} researcher The researcher used for the assessment.
	 *
	 * @return {string} Formatted permalink.
	 */
	getKeywordPermalink( researcher ) {
		if ( false === this.keywordPermalink ) {
			const slugify = researcher.getResearch( 'slugify' )
			const removePunctuation = researcher.getResearch( 'removePunctuation' )
			const keywordLower = this.getLower( 'keyword' )
				.replace( /\'/g, '' )
				.replace( /[-_.]+/g, '-' )


			this.keywordPermalink = slugify( removePunctuation( keywordLower ) )
			this.keywordPermalinkRaw = this.keywordPermalink
		}

		return this.keywordPermalink
	}

	/**
	 * Get keyword as permalink with stop words
	 *
	 * @param {Researcher} researcher The researcher used for the assessment.
	 *
	 * @return {string} Formatted permalink.
	 */
	getPermalinkWithStopwords( researcher ) {
		if ( false === this.keywordPermalink ) {
			this.getKeywordPermalink( researcher )
		}
		return this.keywordPermalinkRaw
	}

	/**
	 * Get keyword combinations.
	 *
	 * @param {Researcher} researcher The researcher used for the assessment.
	 *
	 * @return {Array} Array of keyword combination.
	 */
	getKeywordCombination( researcher ) {
		// Early Bail!!
		if ( ! this.hasKeyword() ) {
			return []
		}

		if ( false === this.keywordCombinations ) {
			this.generateCombinations( researcher )
		}

		return this.keywordCombinations
	}

	/**
	 * Generate keyword combinations.
	 *
	 * @param {Researcher} researcher The researcher used for the assessment.
	 */
	generateCombinations( researcher ) {
		const keywordLower = this.getLower( 'keyword' )

		// Researches.
		const getWords = researcher.getResearch( 'getWords' )
		const pluralize = researcher.getResearch( 'pluralize' )
		const combinations = researcher.getResearch( 'combinations' )

		// Plurals.
		this.keywordPlurals = new Map()
		getWords( keywordLower ).forEach( function( word, index ) {
			this.keywordPlurals.set( index, { word, plural: pluralize.get( word ) } )
		}, this )

		// Permalink.
		this.keywordPermalink = this.getKeywordPermalink( researcher )

		// Combinations.
		this.keywordCombinations = combinations( this.keywordPlurals )
	}

	/**
	 * Set the Content AI.
	 *
	 * @param {string} value Content AI.
	 */
	setContentAI( value ) {
		this.args.contentAI = value
	}

	/**
	 * Set schema data.
	 *
	 * @param {string} schemas Schema Data.
	 */
	setSchema( schemas ) {
		this.args.schemas = schemas
	}
}

export default Paper
