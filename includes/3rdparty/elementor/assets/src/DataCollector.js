/**
 * External dependencies
 */
import jQuery from 'jquery'
import {
	get,
	map,
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
import { addAction, doAction } from '@wordpress/hooks'
import apiFetch from '@wordpress/api-fetch'
import { dispatch, select, subscribe } from '@wordpress/data'
import { safeDecodeURIComponent } from '@wordpress/url'

/**
 * Internal dependencies
 */
import { swapVariables } from '@helpers/swapVariables'

/**
 * DataCollector class
 */
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
		this.saveSchemas = this.saveSchemas.bind( this )
		this.subscribeToElementor()
		addAction( 'rank_math_elementor_before_save', 'rank-math', this.beforeSave );

		setTimeout( this.elementorPreviewLoaded.bind( this ), 5000 )
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

		addAction( 'rank_math_update_app_ui', 'rank-math', ( key ) => {
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
		window.top.$e.internal( 'document/save/set-is-modified', { status: true } );

		elementor.channels.editor.trigger( 'status:change', true )
	}

	/**
	 * Collects the title, slug, permalink, content, featured image and excerpt of a post from elementor.
	 *
	 * @return {Object} Collected data.
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
	 * Get the post ID.
	 *
	 * @return {number} The post's ID.
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
		const content = []
		this.getContentArea()
			.find( '.elementor-widget-container' )
			.each( ( index, element ) => {
				content.push( this.decodeEntities( jQuery( element ).html() ) )
			} )

		return content.join( '' )
	}

	/**
	 * Decode the HTML entities in the given string.
	 *
	 * @param {string} html The string to decode.
	 * @return {string} The decoded string.
	 */
	decodeEntities( html ) {
		if ( ! html ) {
			return ''
		}

		html = html.replace( '–', '-' )
		return html
	}

	/**
	 * Get content area.
	 *
	 * @return {Object} jQuery node.
	 */
	getContentArea() {
		if ( this._contentArea ) {
			return this._contentArea
		}

		const contentArea = elementor.$preview
			.contents()
			.find( '[data-elementor-type="' + ElementorConfig.document.type + '"]' )

		if ( contentArea.length < 1 ) {
			return jQuery( '<div />' )
		}

		this._contentArea = contentArea
		return contentArea
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
	 * Get featured image.
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
		elementor.settings.page.model.on( 'change', this.subscriber )
	}

	/**
	 * Before save hook
	 *
	 * @param {object} args The hook arguments.
	 * @return {object} The hook result.
	 */
	beforeSave( args, result ) {
		window.rankMathDataCollector.savePost()
		window.rankMathDataCollector.saveRedirection()
		window.rankMathDataCollector.saveSchemas()
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
				content: this.getContent(),
			},
		} ).then( ( response ) => {
			if ( isString( response.slug ) ) {
				dispatch( 'rank-math' ).updatePermalink( response.slug )
			}

			doAction( 'rank_math_metadata_updated', response )
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

	saveSchemas() {
		const schemas = select( 'rank-math' ).getSchemas()
		if ( isEmpty( schemas ) || ! select( 'rank-math' ).hasSchemaUpdated() ) {
			return
		}

		const editSchemas = select( 'rank-math' ).getEditSchemas()
		apiFetch( {
			method: 'POST',
			path: 'rankmath/v1/updateSchemas',
			data: {
				objectID: rankMath.objectID,
				objectType: rankMath.objectType,
				schemas,
			},
		} ).then( ( response ) => {
			if ( ! isEmpty( response ) ) {
				const newSchemas = { ...schemas }
				const newEditSchemas = { ...editSchemas }
				map( response, ( metaId, schemaId ) => {
					newSchemas[ 'schema-' + metaId ] = { ...newSchemas[ schemaId ] }
					newEditSchemas[ 'schema-' + metaId ] = { ...newEditSchemas[ schemaId ] }
					delete newSchemas[ schemaId ]
					delete newEditSchemas[ schemaId ]
				} )

				dispatch( 'rank-math' ).schemaUpdated( false )
				dispatch( 'rank-math' ).updateSchemas( newSchemas )
				dispatch( 'rank-math' ).updateEditSchemas( newEditSchemas )
			} else {
				dispatch( 'rank-math' ).updateSchemas( schemas )
			}
		} )
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
