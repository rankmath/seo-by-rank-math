/*
 * External dependencies
 */
import { debounce, forEach, isInteger, isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { addAction } from '@wordpress/hooks'
import { select, subscribe } from '@wordpress/data'

/**
 * Internal dependencies
 */
import { swapVariables } from '@helpers/swapVariables'

/**
 * DataCollector class
 *
 * Some functionality adapted from Yoast (https://github.com/Yoast/wordpress-seo/)
 */
class DataCollector {
	/**
	 * Is plugin initialize
	 *
	 * @type {boolean}
	 */
	initialize = false

	/**
	 * Old slug.
	 *
	 * @type {string}
	 */
	oldSlug = false

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
		}
		this.refresh = this.refresh.bind( this )
		addAction( 'rank_math_loaded', 'rank-math', this.refresh )
		this.getPostAttribute = this.getPostAttribute.bind( this )
		this.subscribeToGutenberg()
	}

	/**
	 * Collects the title, slug, permalink, content, featured image and excerpt of a post from Gutenberg.
	 *
	 * @return {Object} Collected data.
	 */
	collectGutenbergData() {
		if ( false === this.oldSlug && '' !== this.getSlug() ) {
			this.oldSlug = this.getSlug()
		}

		return {
			id: this.getPostID(),
			slug: this.getSlug(),
			permalink: this.getPermalink(),
			content: this.getPostAttribute( 'content' ),
			title: this.getPostAttribute( 'title' ),
			excerpt: this.getPostAttribute( 'excerpt' ),
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
	 * Get the post's permalink.
	 *
	 * @return {string} The post's permalink.
	 */
	getPermalink() {
		/**
		 * Before the post has been saved for the first time, the `generated_slug` is "auto-draft".
		 *
		 * Before the post is saved the post status is "auto-draft", so when this is the case the slug
		 * should be empty.
		 */
		if ( 'auto-draft' === this.getPostAttribute( 'status' ) ) {
			return ''
		}

		let generatedSlug = this.getPostAttribute( 'generated_slug' )

		/**
		 * This should be removed when the following issue is resolved:
		 *
		 * https://github.com/WordPress/gutenberg/issues/8770
		 */
		if ( 'auto-draft' === generatedSlug || 'en' !== rankMath.locale ) {
			generatedSlug = ''
		}

		return this._coreEditorSelect.getPermalink()
	}

	/**
	 * Get the post's slug.
	 *
	 * @return {string} The post's slug.
	 */
	getSlug() {
		/**
		 * Before the post has been saved for the first time, the `generated_slug` is "auto-draft".
		 *
		 * Before the post is saved the post status is "auto-draft", so when this is the case the slug
		 * should be empty.
		 */
		if ( 'auto-draft' === this.getPostAttribute( 'status' ) ) {
			return ''
		}

		let generatedSlug = this.getPostAttribute( 'generated_slug' )

		/**
		 * This should be removed when the following issue is resolved:
		 *
		 * https://github.com/WordPress/gutenberg/issues/8770
		 */
		if ( 'auto-draft' === generatedSlug || 'en' !== rankMath.locale ) {
			generatedSlug = ''
		}

		// When no custom slug is provided we should use the generated_slug attribute.
		return this.getPostAttribute( 'slug' ) || generatedSlug
	}

	/**
	 * Get featured image.
	 *
	 * @return {null|Object} null or image datta.
	 */
	getFeaturedImage() {
		const featuredImageId = this.getPostAttribute( 'featured_media' )

		if ( ! this.isValidMediaId( featuredImageId ) ) {
			return
		}

		const imageData = select( 'core' ).getMedia( featuredImageId )
		if ( isUndefined( imageData ) ) {
			return
		}

		return imageData
	}

	/**
	 * Returns whether the featured image ID is a valid media ID.
	 *
	 * @param {*} featuredImageId The candidate featured image ID.
	 *
	 * @return {boolean} Whether the given ID is a valid ID.
	 */
	isValidMediaId( featuredImageId ) {
		return 'number' === typeof featuredImageId && 0 < featuredImageId
	}

	/**
	 * Retrieves the Gutenberg data for the passed post attribute.
	 *
	 * @param {string} attribute The post attribute you'd like to retrieve.
	 *
	 * @return {string} The post attribute.
	 */
	getPostAttribute( attribute ) {
		if ( ! this._coreEditorSelect ) {
			this._coreEditorSelect = select( 'core/editor' )
		}

		return this._coreEditorSelect.getEditedPostAttribute( attribute )
	}

	/**
	 * Listens to the Gutenberg data.
	 *
	 * @return {void}
	 */
	subscribeToGutenberg() {
		this.subscriber = debounce( this.refresh, 500 )
		subscribe( this.subscriber )
	}

	/**
	 * Refreshes App when the Gutenberg data is dirty.
	 *
	 * @return {void}
	 */
	refresh() {
		const oldData = { ...this._data }
		this._data = this.collectGutenbergData()
		this.handleEditorChange( oldData )
	}

	/**
	 * Updates the redux store with the changed data.
	 *
	 * @param {Object} oldData The old data.
	 *
	 * @return {void}
	 */
	handleEditorChange( oldData ) {
		const hash = {
			slug: 'handleSlugChange',
			title: 'handleTitleChange',
			excerpt: 'handleExcerptChange',
			content: 'handleContentChange',
			featuredImage: 'handleFeaturedImageChange',
		}

		if ( ! isInteger( oldData.id ) ) {
			return
		}

		if ( ! this.initialize ) {
			this.initialize = true
			forEach( hash, ( func, key ) => {
				this[ func ]( this._data[ key ] )
			} )

			rankMathEditor.refresh( 'init' )
			return
		}

		forEach( hash, ( func, key ) => {
			if ( this._data[ key ] !== oldData[ key ] ) {
				this[ func ]( this._data[ key ] )
			}
		} )
	}

	handleSlugChange() {
		if ( '' !== this.getSlug() && false === this.oldSlug ) {
			this.oldSlug = this.getSlug()
		}

		rankMathEditor.refresh( 'permalink' )
	}

	handleTitleChange( title ) {
		swapVariables.setVariable( 'title', title )
		swapVariables.setVariable( 'term', title )
		swapVariables.setVariable( 'author', title )
		swapVariables.setVariable( 'name', title )

		rankMathEditor.refresh( 'title' )
	}

	handleExcerptChange( excerpt ) {
		swapVariables.setVariable( 'excerpt', excerpt )
		swapVariables.setVariable( 'excerpt_only', excerpt )
		swapVariables.setVariable( 'wc_shortdesc', excerpt )
		swapVariables.setVariable( 'seo_description', excerpt )
		rankMathEditor.refresh( 'content' )
	}

	handleFeaturedImageChange( featuredImage ) {
		this._data.featuredImage = featuredImage
		rankMathEditor.refresh( 'featuredImage' )
	}

	handleContentChange() {
		rankMathEditor.refresh( 'content' )
	}

	getData() {
		return this._data
	}

	updateData( field, value ) {
		this._data[ field ] = value
	}
}

export default DataCollector
