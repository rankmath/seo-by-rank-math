/**
 * External dependencies
 */
import jQuery from 'jquery'
import { debounce, isUndefined } from 'lodash'

/**
 * Internal dependencies
 */
import DataCollector from './DataCollector'

class TermCollector extends DataCollector {
	setup() {
		this.updateBtn = jQuery( '.edit-tag-actions input[type="submit"]' )
		this.form = jQuery( '#edittag' )
		this.elemSlug = jQuery( '#slug' )
		this.elemTitle = jQuery( '#name' )
		this.elemDescription = jQuery( '#rank_math_description_editor' )

		this.events()
	}

	getContent() {
		if (
			null === this.elemDescription ||
			0 === this.elemDescription.length
		) {
			return ''
		}

		return this.isTinymce() &&
			tinymce.activeEditor &&
			'rank_math_description_editor' === tinymce.activeEditor.id
			? tinymce.activeEditor.getContent()
			: this.elemDescription.val()
	}

	events() {
		jQuery( window ).on( 'load', () => {
			if (
				this.isTinymce() &&
				tinymce.activeEditor &&
				! isUndefined( tinymce.editors.rank_math_description_editor )
			) {
				tinymce.editors.rank_math_description_editor.on(
					'keyup change',
					debounce( () => {
						this.handleContentChange()
					}, 500 )
				)
			}
		} )
	}
}

export default TermCollector
