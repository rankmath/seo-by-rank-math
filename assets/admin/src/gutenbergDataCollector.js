/**
 * External Dependencies
 */
import { debounce } from 'lodash'

/**
 * Gutenberg DataCollector
 *
 * Some functionality adapted from Yoast (https://github.com/Yoast/wordpress-seo/)
 */
class GutenbergDataCollector {
	constructor( metabox ) {
		this.metabox = metabox
		this.getPostAttribute = this.getPostAttribute.bind( this )
		this.refresh = this.refresh.bind( this )
		this._data = this.collectGutenbergData( this.getPostAttribute )
		this.subscribeToGutenberg()
	}

	/**
	 * Collects the title, slug, content and excerpt of a post from Gutenberg.
	 *
	 * @return {{content: string, title: string, slug: string, excerpt: string}} The content, title, slug and excerpt.
	 */
	collectGutenbergData() {
		return {
			content: this.getPostAttribute( 'content' ),
			title: this.getPostAttribute( 'title' ),
			slug: this.getSlug(),
			excerpt: this.getPostAttribute( 'excerpt' ),
		}
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
	 * Retrieves the Gutenberg data for the passed post attribute.
	 *
	 * @param {string} attribute The post attribute you'd like to retrieve.
	 *
	 * @return {string} The post attribute.
	 */
	getPostAttribute( attribute ) {
		if ( ! this._coreEditorSelect ) {
			this._coreEditorSelect = wp.data.select( 'core/editor' )
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
		wp.data.subscribe( this.subscriber )
	}

	/**
	 * Refreshes App when the Gutenberg data is dirty.
	 *
	 * @return {void}
	 */
	refresh() {
		const gutenbergData = this.collectGutenbergData()
		this.handleEditorChange( gutenbergData )
		this._data = gutenbergData
	}

	/**
	 * Updates the redux store with the changed data.
	 *
	 * @param {Object} newData The changed data.
	 *
	 * @return {void}
	 */
	handleEditorChange( newData ) {
		// Handle title change
		if ( this._data.title !== newData.title ) {
			this.metabox.title = newData.title
			this.metabox.setVariable( 'title', newData.title )
			this.metabox.setVariable( 'term', newData.title )
			this.metabox.setVariable( 'author', newData.title )
			this.metabox.setVariable( 'name', newData.title )
			this.metabox.updateTitlePreview()
		}

		// Handle excerpt change
		if ( this._data.excerpt !== newData.excerpt ) {
			this.metabox.setVariable( 'excerpt', newData.excerpt )
			this.metabox.setVariable( 'excerpt_only', newData.excerpt )
			this.metabox.setVariable( 'wc_shortdesc', newData.excerpt )
			this.metabox.updateDescriptionPreview()
		}

		// Handle slug change
		if ( this._data.slug !== newData.slug ) {
			this.metabox.serpPermalinkField
				.val( newData.slug )
				.trigger( 'input' )
		}

		// Handle content change
		if ( this._data.content !== newData.content ) {
			this.metabox.assessor.paper.setContent( newData.content )
			this.metabox.updateDescriptionPreview()
			this.metabox.socialFields.updateThumbnailPreview()
		}
	}
}

export default GutenbergDataCollector
