/**
 * External dependencies
 */
import $ from 'jquery'
import { debounce, isUndefined } from 'lodash'

/**
 * Internal dependencies
 */
import DataCollector from './DataCollector'

class TermCollector extends DataCollector {
	setup() {
		this.elemSlug = $( '#slug' )
		this.elemTitle = $( '#name' )
		this.elemDescription = $( '#rank_math_description_editor' )

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
		$( window ).on( 'load', () => {
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
