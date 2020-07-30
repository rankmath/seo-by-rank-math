/**
 * External dependencies
 */
import jQuery from 'jquery'
import {
	get,
	debounce,
	forEach,
	isString,
	isEmpty,
	isInteger,
	isUndefined,
	isEqual,
} from 'lodash'

/**
 * WordPress dependencies
 */
import { dispatch, select, subscribe } from '@wordpress/data'
import apiFetch from '@wordpress/api-fetch'
import { addAction } from '@wordpress/hooks'
import { safeDecodeURIComponent } from '@wordpress/url'

/**
 * Internal dependencies
 */
import { swapVariables } from '@helpers/swapVariables'

class DataCollector {
	/**
	 * Is plugin initialize
	 *
	 * @type {boolean}
	 */
	initialize = false

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
		this.savePost = this.savePost.bind( this )
		this.saveRedirection = this.saveRedirection.bind( this )
		this.subscribeToElementor()

		elementor.once(
			'preview:loaded',
			debounce( this.elementorPreviewLoaded.bind( this ), 5000 )
		)
	}

	/**
	 * Refreshes app when content is changed.
	 *
	 * @return {void}
	 */
	elementorPreviewLoaded() {
		addAction( 'rank_math_data_changed', 'rank-math', () => {
			this.activateSaveButton()
		} )

		addAction( 'rank_math_update_app_ui', 'rank-math', ( key, value ) => {
			if ( 'hasRedirect' !== key ) {
				return
			}

			this.activateSaveButton()
		} )
	}

	/**
	 * Activate Elementor's update button.
	 *
	 * @return {void}
	 */
	activateSaveButton() {
		const footerSaver = get( elementor, 'saver.footerSaver', false )
		if ( false !== footerSaver ) {
			footerSaver.activateSaveButtons( document, true )
			return
		}

		elementor.channels.editor.trigger( 'status:change', true )
	}

	/**
	 * Collects the content, title, slug and excerpt of a post from Gutenberg.
	 *
	 * @return {Object} The content, title, slug and excerpt.
	 */
	collectGutenbergData() {
		return {
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
		return elementor.config.document.id
	}

	/**
	 * Get post title.
	 *
	 * @return {string} The post's title.
	 */
	getTitle() {
		return elementor.settings.page.model.get( 'post_title' )
	}

	/**
	 * Get post content.
	 *
	 * @return {string} The post's content.
	 */
	getContent() {
		if ( ! this._contentArea ) {
			const documentType =
				'[data-elementor-type="' + ElementorConfig.document.type + '"]'
			this._contentArea = elementor.$preview
				.contents()
				.find( documentType )
		}

		const content = []
		this._contentArea
			.find( '.elementor-widget-container' )
			.each( function() {
				content.push( jQuery( this ).html() )
			} )

		return content.join( '' )
	}

	/**
	 * Get post excerpt.
	 *
	 * @return {string} The post's excerpt.
	 */
	getExcerpt() {
		return elementor.settings.page.model.get( 'post_excerpt' )
	}

	/**
	 * Get the post's permalink.
	 *
	 * @return {string} The post's permalink.
	 */
	getPermalink() {
		if ( rankMath.is_front_page ) {
			return rankMath.homeUrl + '/'
		}

		return this.getSlug()
			? rankMath.permalinkFormat
				.replace( /%(postname|pagename)%/, this.getSlug() )
				.trimRight( '/' )
			: ''
	}

	/**
	 * Get the post's slug.
	 *
	 * @return {string} The post's slug.
	 */
	getSlug() {
		return safeDecodeURIComponent( select( 'rank-math' ).getPermalink() )
	}

	/**
	 * Gett featued image.
	 *
	 * @return {null|Object} null or image datta.
	 */
	getFeaturedImage() {
		const { model } = elementor.settings.page
		let featuredImageId = model.get( 'post_featured_image' )
		if ( isUndefined( featuredImageId ) ) {
			return
		}

		featuredImageId =
			'' === featuredImageId.id ? 0 : parseInt( featuredImageId.id )

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
	 * Listens to the Elementor data.
	 *
	 * @return {void}
	 */
	subscribeToElementor() {
		dispatch( 'rank-math' ).updatePermalink( rankMath.postName )

		this.subscriber = debounce( this.refresh, 500 )
		subscribe( this.subscriber )
		elementor.saver.on( 'before:save', this.savePost )
		elementor.saver.on( 'before:save', this.saveRedirection )
		elementor.settings.page.model.on( 'change', this.subscriber )
	}

	/**
	 * Refreshes app when the Elementor data is dirty.
	 *
	 * @return {void}
	 */
	refresh() {
		const oldData = { ...this._data }
		this._data = this.collectGutenbergData()
		this.handleEditorChange( oldData )
		if ( ! isEqual( oldData, this._data ) && oldData.id ) {
			elementor.channels.editor.trigger( 'status:change', true )
		}
	}

	savePost() {
		const meta = select( 'rank-math' ).getDirtyMetadata()
		if ( isEmpty( meta ) ) {
			return
		}
		apiFetch( {
			method: 'POST',
			path: 'rankmath/v1/updateMeta',
			data: {
				objectID: rankMath.objectID,
				objectType: rankMath.objectType,
				meta,
			},
		} ).then( ( response ) => {
			if ( isString( response ) ) {
				dispatch( 'rank-math' ).updatePermalink( response )
			}
		} )

		dispatch( 'rank-math' ).resetDirtyMetadata()
	}

	/**
	 * Save redirection item.
	 */
	saveRedirection() {
		const redirection = select( 'rank-math' ).getRedirectionItem()
		if ( isEmpty( redirection ) ) {
			return
		}

		redirection.objectID = this.getPostID()
		redirection.redirectionSources = this.getPermalink()

		apiFetch( {
			method: 'POST',
			path: 'rankmath/v1/updateRedirection',
			data: redirection,
		} ).then( ( response ) => {
			if ( 'delete' === response.action ) {
				dispatch( 'rank-math' ).updateRedirection( 'redirectionID', 0 )
			} else if ( 'new' === response.action ) {
				dispatch( 'rank-math' ).updateRedirection(
					'redirectionID',
					response.id
				)
			}
		} )

		dispatch( 'rank-math' ).resetRedirection()
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
			id: 'handleIDChange',
			slug: 'handleSlugChange',
			title: 'handleTitleChange',
			excerpt: 'handleExcerptChange',
			content: 'handleContentChange',
			featuredImage: 'handleFeaturedImageChange',
		}

		if ( ! isInteger( oldData.id ) ) {
			dispatch( 'rank-math' ).refreshResults()
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

	handleIDChange( postID ) {
		dispatch( 'rank-math' ).updatePostID( postID )
		dispatch( 'rank-math' ).toggleLoaded( true )
	}

	handleSlugChange() {
		rankMathEditor.refresh( 'permalink' )
	}

	handleTitleChange( title ) {
		swapVariables.setVariable( 'title', title )
		swapVariables.setVariable( 'term', title )
		swapVariables.setVariable( 'author', title )
		swapVariables.setVariable( 'name', title )

		dispatch( 'rank-math' ).updateSerpTitle(
			select( 'rank-math' ).getTitle()
		)
		rankMathEditor.refresh( 'title' )
	}

	handleExcerptChange( excerpt ) {
		swapVariables.setVariable( 'excerpt', excerpt )
		swapVariables.setVariable( 'excerpt_only', excerpt )
		swapVariables.setVariable( 'wc_shortdesc', excerpt )
		swapVariables.setVariable( 'seo_description', excerpt )

		dispatch( 'rank-math' ).updateSerpDescription(
			select( 'rank-math' ).getDescription()
		)
	}

	handleFeaturedImageChange( featuredImage ) {
		dispatch( 'rank-math' ).updateFeaturedImage( featuredImage )
		rankMathEditor.refresh( 'featuredImage' )
	}

	handleContentChange() {
		dispatch( 'rank-math' ).updateSerpDescription(
			select( 'rank-math' ).getDescription()
		)
		rankMathEditor.refresh( 'content' )
	}

	getData() {
		return this._data
	}
}

export default DataCollector
