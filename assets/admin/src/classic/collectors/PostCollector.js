/**
 * External dependencies
 */
import jQuery from 'jquery'
import { debounce, isUndefined, isNull } from 'lodash'

/**
 * WordPress dependencies
 */
import { addAction } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import DataCollector from './DataCollector'

class PostCollector extends DataCollector {
	setup() {
		this.elemSlug = jQuery( '#post_name' )
		this.elemTitle = jQuery( '#title' )
		this.elemDescription = jQuery( '#excerpt' )
		this.elemContent = jQuery( '#content' )
		this.saveDraft = jQuery( '#save-post' )
		this.postStatus = jQuery( '#original_post_status' )

		this.assessThumbnail = this.assessThumbnail.bind( this )
		addAction(
			'rank_math_updated_featured_image',
			'rank-math',
			this.assessThumbnail
		)
		this.events()
	}

	/**
	 * Get post content.
	 *
	 * @return {string} The post's content.
	 */
	getContent() {
		if ( null === this.elemContent || 0 === this.elemContent.length ) {
			return
		}

		return this.isTinymce() &&
			tinymce.activeEditor &&
			! isNull( tinymce.get( 'content' ) )
			? tinymce.get( 'content' ).getContent()
			: this.elemContent.val()
	}

	/**
	 * Get the post's slug.
	 *
	 * @return {string} The post's slug.
	 */
	getSlug() {
		const slug =
			'' === this.elemSlug.val() && jQuery( '#editable-post-name-full' ).length
				? jQuery( '#editable-post-name-full' ).text()
				: this.elemSlug.val()

		return isUndefined( slug ) ? '' : slug
	}

	events() {
		this.elemContent.on(
			'input change',
			debounce( () => {
				this.handleContentChange()
			}, 500 )
		).trigger( 'change' )

		jQuery( window ).on( 'load', () => {
			if ( ! this.isTinymce() ) {
				return
			}

			if (
				tinymce.activeEditor &&
				! isUndefined( tinymce.editors.content )
			) {
				tinymce.editors.content.on(
					'keyup change',
					debounce( () => {
						this.handleContentChange()
					}, 500 )
				)
			}

			if ( tinymce.editors && ! isUndefined( tinymce.editors.excerpt ) ) {
				tinymce.editors.excerpt.on(
					'keyup change',
					debounce( () => {
						tinymce.editors.excerpt.save()
						this.handleExcerptChange()
					}, 500 )
				)
			}
		} )

		// Update Permalink.
		jQuery( document ).on( 'ajaxComplete', ( event, response, ajaxOptions ) => {
			const ajaxEndPoint = '/admin-ajax.php'

			if (
				ajaxEndPoint !==
				ajaxOptions.url.substr( 0 - ajaxEndPoint.length )
			) {
				return
			}

			let slug = ''
			if (
				'string' === typeof ajaxOptions.data &&
				-1 !== ajaxOptions.data.indexOf( 'action=sample-permalink' )
			) {
				if ( '' === response.responseText ) {
					slug = this.elemTitle.val()
				} else {
					// Added divs to the response text, otherwise jQuery won't parse to HTML, but an array.
					slug = jQuery( '<div>' + response.responseText + '</div>' )
						.find( '#editable-post-name-full' )
						.text()
				}

				rankMathEditor.updatePermalink( slug )
			}
		} )

		this.saveDraft.on( 'click', () => {
			this.updateBtn.attr( 'data-disable', 'true' )
		} )
	}

	assessThumbnail( featuredImage ) {
		this.featuredImage = {
			source_url: featuredImage.src,
			alt_text: featuredImage.alt,
		}

		this.handleFeaturedImageChange( featuredImage )
	}
}

export default PostCollector
