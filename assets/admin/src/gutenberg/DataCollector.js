/**
 * External dependencies
 */
import { get, map, debounce, forEach, isEmpty, isInteger, isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { dispatch, select, subscribe } from '@wordpress/data'
import apiFetch from '@wordpress/api-fetch'
import { safeDecodeURIComponent } from '@wordpress/url'
import { doAction } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import { swapVariables } from '@helpers/swapVariables'
import isGutenbergAvailable from '@helpers/isGutenbergAvailable'

/**
 * DataCollector class
 *
 * @copyright Copyright (C) 2008-2019, Yoast BV
 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
 */
class DataCollector {
	/**
	 * Is plugin initialize.
	 *
	 * @type {boolean}
	 */
	initialize = false

	/**
	 * Saving redirection.
	 *
	 * @type {boolean}
	 */
	isSavingRedirection = false

	/**
	 * Saving schemas.
	 *
	 * @type {boolean}
	 */
	isSavingSchemas = false

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
		this.isSavingPost = this.isSavingPost.bind( this )
		this.getPostAttribute = this.getPostAttribute.bind( this )
		this.subscribeToGutenberg()
	}

	/**
	 * Collects the title, slug, permalink, content, featured image and excerpt of a post from Gutenberg.
	 *
	 * @return {Object} Collected data.
	 */
	collectGutenbergData() {
		if ( ! isGutenbergAvailable() ) {
			return
		}

		if ( ! this._coreEditorSelect ) {
			this._coreEditorSelect = select( 'core/editor' )
		}

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
		return this._coreEditorSelect.getCurrentPostId()
	}

	/**
	 * Get the post's permalink.
	 *
	 * @return {string} The post's permalink.
	 */
	getPermalink() {
		/**
		 * Before the post has been saved for the first time, the generated_slug is "auto-draft".
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

		return safeDecodeURIComponent( this._coreEditorSelect.getPermalink() )
	}

	/**
	 * Get the post's slug.
	 *
	 * @return {string} The post's slug.
	 */
	getSlug() {
		/**
		 * Before the post has been saved for the first time, the generated_slug is "auto-draft".
		 *
		 * Before the post is saved the post status is "auto-draft", so when this is the case the slug
		 * should be empty.
		 */
		// if ( 'auto-draft' === this.getPostAttribute( 'status' ) ) {
		// 	return ''
		// }

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
		return safeDecodeURIComponent(
			this.getPostAttribute( 'slug' ) || generatedSlug
		)
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
		if ( ! isGutenbergAvailable() ) {
			return ''
		}

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
		subscribe( this.isSavingPost )
	}

	/**
	 * Refreshes app when the Gutenberg data is dirty.
	 *
	 * @return {void}
	 */
	refresh() {
		const oldData = { ...this._data }
		this._data = this.collectGutenbergData()
		this.handleEditorChange( oldData )
	}

	isSavingPost() {
		const editor = select( 'core/editor' )

		if ( editor.isAutosavingPost() ) {
			return
		}

		if ( editor.isSavingPost() ) {
			const repo = select( 'rank-math' )
			const meta = repo.getDirtyMetadata()

			if ( ! isEmpty( meta ) ) {
				apiFetch( {
					method: 'POST',
					path: 'rankmath/v1/updateMeta',
					data: {
						objectID: rankMath.objectID,
						objectType: rankMath.objectType,
						meta,
						content: this.getPostAttribute( 'content' ),
					},
				} ).then( ( response ) => {
					doAction( 'rank_math_metadata_updated', response )
				} )
				dispatch( 'rank-math' ).resetDirtyMetadata()
			}

			if ( 'publish' === this.getPostAttribute( 'status' ) ) {
				this.saveRedirection()
				this.autoCreateRedirectionNotice()
			}

			this.saveSchemas()
		}
	}

	saveSchemas() {
		if ( this.isSavingSchemas ) {
			return
		}

		const schemas = select( 'rank-math' ).getSchemas()
		if ( isEmpty( schemas ) || ! select( 'rank-math' ).hasSchemaUpdated() ) {
			return
		}

		this.isSavingSchemas = true

		const editSchemas = select( 'rank-math' ).getEditSchemas()
		apiFetch( {
			method: 'POST',
			path: 'rankmath/v1/updateSchemas',
			data: {
				objectID: this.getPostID(),
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
			setTimeout( () => {
				dispatch( 'rank-math' ).schemaUpdated( false )
				doAction( 'rank_math_schema_changed' )
				this.isSavingSchemas = false
			}, 2000 )
		} )
	}

	/**
	 * Save redirection item.
	 */
	saveRedirection() {
		if ( this.isSavingRedirection ) {
			return
		}

		const redirection = select( 'rank-math' ).getRedirectionItem()
		if ( isEmpty( redirection ) ) {
			return
		}

		this.isSavingRedirection = true
		redirection.objectID = this.getPostID()
		redirection.redirectionSources = this.getPermalink()

		const rankMath = dispatch( 'rank-math' )
		const notices = dispatch( 'core/notices' )

		rankMath.resetRedirection()

		apiFetch( {
			method: 'POST',
			path: 'rankmath/v1/updateRedirection',
			data: redirection,
		} ).then( ( response ) => {
			if ( 'delete' === response.action ) {
				notices.createInfoNotice( response.message, {
					id: 'redirectionNotice',
				} )
				rankMath.updateRedirection( 'redirectionID', 0 )
			} else if ( 'update' === response.action ) {
				notices.createInfoNotice( response.message, {
					id: 'redirectionNotice',
				} )
			} else if ( 'new' === response.action ) {
				rankMath.updateRedirection( 'redirectionID', response.id )
				notices.createSuccessNotice( response.message, {
					id: 'redirectionNotice',
				} )
			}

			setTimeout( () => {
				this.isSavingRedirection = false
				notices.removeNotice( 'redirectionNotice' )
			}, 2000 )
		} )
	}

	autoCreateRedirectionNotice() {
		// If redirection and auto creation is not enabled.
		if (
			! rankMath.assessor.hasRedirection ||
			! get( rankMath, [ 'assessor', 'autoCreateRedirection' ], false ) ||
			select( 'core/editor' ).isEditedPostNew() ||
			false === this.oldSlug
		) {
			return
		}

		// If slug is not changed.
		const slug = this.getSlug()
		if ( this.oldSlug === slug ) {
			return
		}

		const notices = dispatch( 'core/notices' )

		this.oldSlug = slug
		notices.createSuccessNotice(
			__( 'Auto redirection created.', 'rank-math' ),
			{ id: 'redirectionAutoCreationNotice' }
		)

		setTimeout( () => {
			notices.removeNotice( 'redirectionAutoCreationNotice' )
		}, 2000 )
	}

	/**
	 * Updates the redux store with the changed data.
	 *
	 * @param {Object} oldData Old data.
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
		if ( '' !== this.getSlug() && false === this.oldSlug ) {
			this.oldSlug = this.getSlug()
		}
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
