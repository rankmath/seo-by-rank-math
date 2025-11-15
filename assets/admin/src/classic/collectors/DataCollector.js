/**
 * External dependencies
 */
import $ from 'jquery'
import { debounce, isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { addAction, applyFilters } from '@wordpress/hooks'
import { dispatch, select } from '@wordpress/data'

/**
 * Internal dependencies
 */
import { swapVariables } from '@helpers/swapVariables'

/**
 * DataCollector class
 */
class DataCollector {
	/**
	 * Class constructor
	 */
	constructor() {
		this.updateBtn = $( '#publish' )
		this.form = $( '#post' )
		this._data = {
			id: false,
			slug: false,
			permalink: false,
			content: false,
			title: false,
			excerpt: false,
			featuredImage: false,
			description: '',
		}

		this.refresh = this.refresh.bind( this )
		addAction( 'rank_math_loaded', 'rank-math', this.refresh )

		this.setup()
		this.init()
	}

	init() {
		this.elemTitle.on(
			'input',
			debounce( () => {
				this.handleTitleChange( this.elemTitle.val() )
			}, 500 )
		).trigger( 'input' )

		this.elemDescription.on(
			'input',
			debounce( () => {
				this.handleExcerptChange( this.elemDescription.val() )
			}, 500 )
		).trigger( 'input' )

		this.elemSlug.on(
			'input',
			debounce( () => {
				rankMathEditor.updatePermalink( this.elemSlug.val() )
			}, 500 )
		)
	}

	/**
	 * Collects the title, slug, permalink, content, featured image and excerpt of a post from Gutenberg.
	 *
	 * @copyright Copyright (C) 2008-2019, Yoast BV
	 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
	 */
	collectData() {
		this._data = {
			id: this.getPostID(),
			slug: this.getSlug(),
			permalink: this.getPermalink(),
			content: this.getContent(),
			title: this.getTitle(),
			excerpt: this.getExcerpt(),
			featuredImage: this.getFeaturedImage(),
		}
	}

	/**
	 * Get the post ID.
	 *
	 * @return {number} The post's ID.
	 */
	getPostID() {
		return rankMath.objectID
	}

	/**
	 * Get post title.
	 *
	 * @return {string} The post's title.
	 */
	getTitle() {
		return this.elemTitle.val()
	}

	/**
	 * Get post excerpt.
	 *
	 * @return {string} The post's excerpt.
	 */
	getExcerpt() {
		const excerpt = this.elemDescription.val()
		swapVariables.setVariable( 'excerpt', excerpt )
		swapVariables.setVariable( 'excerpt_only', excerpt )
		swapVariables.setVariable( 'wc_shortdesc', excerpt )
		swapVariables.setVariable( 'seo_description', excerpt )

		return excerpt
	}

	/**
	 * Get the post's permalink.
	 *
	 * @return {string} The post's permalink.
	 */
	getPermalink() {
		return this.getSlug()
			? rankMath.permalinkFormat
				.replace( /%(postname|pagename|term|author)%/, this.getSlug() )
			: ''
	}

	/**
	 * Get the post's slug.
	 *
	 * @return {string} The post's slug.
	 */
	getSlug() {
		return this.elemSlug.val()
	}

	/**
	 * Get featured image.
	 *
	 * @return {null|Object} null or image datta.
	 */
	getFeaturedImage() {
		return this.featuredImage
	}

	/**
	 * Refreshes App when the Elementor data is dirty.
	 *
	 * @return {void}
	 */
	refresh() {
		this.collectData()
		dispatch( 'rank-math' ).toggleLoaded( true )
		rankMathEditor.refresh( 'init' )
	}

	handleSlugChange( slug, force = false ) {
		if ( 'auto-draft' !== this.getStatus() || force ) {
			this.elemSlug.val( slug )
		}

		$( '#editable-post-name' ).text( slug )
		$( '#editable-post-name-full' ).text( slug )

		this._data.slug = this.getSlug()
		this._data.permalink = this.getPermalink()
		dispatch( 'rank-math' ).updateSerpSlug( slug )
		rankMathEditor.refresh( 'permalink' )
	}

	handleTitleChange( title ) {
		swapVariables.setVariable( 'title', title )
		swapVariables.setVariable( 'term', title )
		swapVariables.setVariable( 'author', title )
		dispatch( 'rank-math' ).updateSerpTitle(
			select( 'rank-math' ).getTitle()
		)
		rankMathEditor.refresh( 'title' )
	}

	handleExcerptChange() {
		this._data.excerpt = this.getExcerpt()
		dispatch( 'rank-math' ).updateSerpDescription(
			select( 'rank-math' ).getDescription()
		)
		rankMathEditor.refresh( 'content' )
	}

	handleFeaturedImageChange() {
		this._data.featuredImage = this.getFeaturedImage()
		dispatch( 'rank-math' ).updateFeaturedImage( this.getFeaturedImage() )
		rankMathEditor.refresh( 'featuredImage' )
	}

	handleContentChange() {
		this._data.excerpt = this.getExcerpt()
		this._data.content = this.getContent()
		dispatch( 'rank-math' ).updateSerpDescription(
			select( 'rank-math' ).getDescription()
		)
		rankMathEditor.refresh( 'content' )
	}

	getData( field = '' ) {
		const data = field ? this._data[ field ] : this._data
		return applyFilters( 'rank_math_dataCollector_data', data )
	}

	updateData( field, value ) {
		this._data[ field ] = value
	}

	isTinymce() {
		return 'undefined' !== typeof tinymce
	}

	getStatus() {
		const status = ! isUndefined( this.postStatus ) && this.postStatus.length ? this.postStatus.val() : ''

		return isUndefined( status ) ? '' : status
	}
}

export default DataCollector
