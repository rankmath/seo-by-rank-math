/**
 * External dependencies
 */
import $ from 'jquery'
import { debounce, isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { addAction } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import { swapVariables } from '@helpers/swapVariables'

class DataCollector {
	/**
	 * Class constructor
	 */
	constructor() {
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
		)

		this.elemDescription.on(
			'input',
			debounce( () => {
				this.handleExcerptChange( this.elemDescription.val() )
			}, 500 )
		)

		this.elemSlug.on(
			'input',
			debounce( () => {
				rankMathEditor.updatePermalink( this.elemSlug.val() )
			}, 500 )
		)
	}

	/**
	 * Collects the content, title, slug and excerpt of a post from Gutenberg.
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
	 * Get the post id.
	 *
	 * @return {number} The post's id.
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
		return rankMath.homeUrl + '/' + this.getSlug()
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
	 * Gett featued image.
	 *
	 * @return {null|Object} null or image datta.
	 */
	getFeaturedImage() {
		return this.featuredImage
	}

	/**
	 * Refreshes app when the Elementor data is dirty.
	 *
	 * @return {void}
	 */
	refresh() {
		this.collectData()
		rankMathEditor.refresh( 'init' )
	}

	handleSlugChange( slug ) {
		const { SerpPreview } = rankMathEditor.components
		this.elemSlug.val( slug )
		$( '#editable-post-name' ).text( slug )
		$( '#editable-post-name-full' ).text( slug )

		if ( ! isUndefined( SerpPreview.serpPermalinkField ) ) {
			SerpPreview.serpPermalinkField.val( slug )
		}

		this._data.slug = this.getSlug()
		this._data.permalink = this.getPermalink()
		rankMathEditor.refresh( 'permalink' )
	}

	handleTitleChange( title ) {
		swapVariables.setVariable( 'title', title )
		swapVariables.setVariable( 'term', title )
		swapVariables.setVariable( 'author', title )
		swapVariables.setVariable( 'name', title )
		rankMathEditor.refresh( 'title' )
	}

	handleExcerptChange() {
		this._data.excerpt = this.getExcerpt()
		rankMathEditor.refresh( 'content' )
	}

	handleFeaturedImageChange() {
		this._data.featuredImage = this.getFeaturedImage()
		rankMathEditor.refresh( 'featuredImage' )
	}

	handleContentChange() {
		this._data.excerpt = this.getExcerpt()
		this._data.content = this.getContent()
		rankMathEditor.refresh( 'content' )
	}

	getData( field = '' ) {
		return field ? this._data[ field ] : this._data
	}

	updateData( field, value ) {
		this._data[ field ] = value
	}

	isTinymce() {
		return 'undefined' !== typeof( tinymce )
	}
}

export default DataCollector
