/*global ETBuilderBackendDynamic, ET_Builder*/

/**
 * External dependencies
 */
import jQuery from 'jquery'
import {
	debounce,
	forEach,
	isString,
	isEmpty,
	isInteger,
	isEqual,
	get,
	map,
} from 'lodash'

/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch'
import { dispatch, select, subscribe } from '@wordpress/data'
import { addFilter, doAction } from '@wordpress/hooks'

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
		this.etSettingsData = {
			title: '',
			excerpt: '',
			featuredImage: '',
		}
		this._featuredImage = null
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
		this.subscribeToDivi()
	}

	/**
	 * Collects the content, title, slug and excerpt of the post.
	 *
	 * @return {Object} The content, title, slug and excerpt.
	 */
	collectPostData() {
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
		return parseInt(
			get( ETBuilderBackendDynamic, 'postId', 0 )
		)
	}

	/**
	 * Get post title.
	 *
	 * @return {string} The post's title.
	 */
	getTitle() {
		return this.etSettingsData.title
	}

	/**
	 * Set post title.
	 *
	 * @param {string} title The new title.
	 */
	setTitle( title ) {
		this.etSettingsData.title = title
	}

	/**
	 * Get post content.
	 *
	 * @return {string} The post's content.
	 */
	getContent() {
		const content = []
		this.getContentArea()
			.find( '.et_pb_section' )
			.each( function() {
				content.push( jQuery( this ).html() )
			} )
		return content.join( '' )
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

		if ( ! this._etAppFrameElem ) {
			this._etAppFrameElem = get(
				ET_Builder,
				'Frames.app.frameElement',
				document.querySelector( 'iframe#et-fb-app-frame' )
			)
		}

		if ( ! this._etAppFrameElem ) {
			return jQuery( '<div />' )
		}

		const contentArea = jQuery(
			this._etAppFrameElem.contentWindow.document.querySelector( '#et-fb-app' )
		)

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
		return this.etSettingsData.excerpt
	}

	/**
	 * Set post excerpt.
	 *
	 * @param {string} excerpt The new excerpt.
	 */
	setExcerpt( excerpt ) {
		this.etSettingsData.excerpt = excerpt
	}

	/**
	 * Get the post's permalink.
	 *
	 * @return {string} The post's permalink.
	 */
	getPermalink() {
		return rankMath.is_front_page
			? rankMath.homeUrl + '/'
			: rankMath.homeUrl + '/' + select( 'rank-math' ).getPermalink()
	}

	/**
	 * Get the post's slug.
	 *
	 * @return {string} The post's slug.
	 */
	getSlug() {
		const $permalinkInput = jQuery( document ).find( '#rank-math-editor-permalink' )
		if ( $permalinkInput.length ) {
			return $permalinkInput.val()
		}
		return get(
			ETBuilderBackendDynamic,
			'postMeta.post_name',
			this.getPermalink().replace( rankMath.homeUrl, '' ).replace( /\//g, '' )
		)
	}

	/**
	 * Get featued image.
	 *
	 * @return {null|Object} null or image data.
	 */
	getFeaturedImage() {
		if ( this._featuredImage ) {
			return this._featuredImage
		}
		this.setFeaturedImage()
	}

	async setFeaturedImage( imgId ) {
		if ( typeof imgId === 'undefined' ) {
			imgId = await this.fetchFeaturedImageId()
		}
		if ( this.isValidMediaId( imgId ) && imgId ) {
			this._featuredImage = await this.fetchWpMedia( imgId )
		} else {
			this._featuredImage = false
		}
	}

	async fetchFeaturedImageId() {
		let id = null
		await apiFetch( {
			path: `/wp-json/rankmath/v1/getFeaturedImageId`,
			method: 'POST',
			data: {
				postId: get( ETBuilderBackendDynamic, 'postId', 0 ),
			},
		} ).then( ( resp ) => id = resp.success ? resp.featImgId : false )
		return id
	}

	async fetchWpMedia( mediaId ) {
		let media = {}
		await apiFetch( {
			path: `/wp-json/wp/v2/media/${ mediaId }`,
			method: 'GET',
		} )
			.then( ( resp ) => media = resp )
		return media
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
	 * Listens to the Divi data.
	 *
	 * @return {void}
	 */
	subscribeToDivi() {
		this.setTitle( get( ETBuilderBackendDynamic, 'postTitle', '' ) )
		this.setExcerpt( get( ETBuilderBackendDynamic, 'postMeta.post_excerpt', '' ) )
		this.setFeaturedImage( get( ETBuilderBackendDynamic, 'currentPage.thumbnailId', 0 ) )

		dispatch( 'rank-math' ).updatePermalink( rankMath.postName )

		this.subscriber = debounce( this.refresh, 500 )
		subscribe( this.subscriber )

		jQuery( '.et-fb-page-settings-bar' )
			.find( '.et-fb-button--save-draft, .et-fb-button--publish' )
			.on( 'click', () => {
				this.savePost()
				this.saveRedirection()
				this.saveSchemas()
			} )

		addFilter(
			'et.builder.store.setting.update',
			'rank-math',
			( value, setting ) => {
				if ( value ) {
					switch ( setting ) {
						case 'et_pb_post_settings_title':
							this.setTitle( value )
							this.subscriber()
							break
						case 'et_pb_post_settings_excerpt':
							this.setExcerpt( value )
							this.subscriber()
							break
						case 'et_pb_post_settings_image':
							this.setFeaturedImage( parseInt( value ) )
							this.subscriber()
							break
					}
				}
				return value
			}
		)

		// eslint-disable-next-line @wordpress/no-global-event-listener
		window.addEventListener( 'message', ( e ) => {
			const etEvent = e.data.etBuilderEvent
			if ( 'et_fb_section_content_change' === etEvent ) {
				this.subscriber()
			}
		} )
	}

	/**
	 * Refreshes app when the Divi data is dirty.
	 *
	 * @return {void}
	 */
	refresh() {
		const oldData = { ...this._data }
		this._data = this.collectPostData()
		this.handleEditorChange( oldData )
		if ( ! isEqual( oldData, this._data ) && oldData.id ) {
			dispatch( 'rank-math' ).refreshResults()
		}
	}

	savePost() {
		const meta = select( 'rank-math' ).getDirtyMetadata()
		if ( isEmpty( meta ) ) {
			return
		}
		apiFetch(
			{
				method: 'POST',
				path: '/wp-json/rankmath/v1/updateMeta',
				data: {
					objectID: rankMath.objectID,
					objectType: rankMath.objectType,
					meta,
				},
			}
		).then( ( response ) => {
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
			path: '/wp-json/rankmath/v1/updateRedirection',
			data: redirection,
		} ).then( ( response ) => {
			if ( 'delete' === response.action ) {
				dispatch( 'rank-math' ).updateRedirection( 'redirectionID', 0 )
			} else if ( 'new' === response.action ) {
				dispatch( 'rank-math' ).updateRedirection( 'redirectionID', response.id )
			}
		} )

		dispatch( 'rank-math' ).resetRedirection()
	}

	saveSchemas() {
		const schemas = select( 'rank-math' ).getSchemas()
		if ( isEmpty( schemas ) || isEqual( schemas, get( rankMath, 'schemas', {} ) ) ) {
			return
		}

		const editSchemas = select( 'rank-math' ).getEditSchemas()
		apiFetch( {
			method: 'POST',
			path: '/wp-json/rankmath/v1/updateSchemas',
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

				dispatch( 'rank-math' ).updateSchemas( newSchemas )
				dispatch( 'rank-math' ).updateEditSchemas( newEditSchemas )
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
