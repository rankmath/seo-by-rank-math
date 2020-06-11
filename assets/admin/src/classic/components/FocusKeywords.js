/**
 * External dependencies
 */
import $ from 'jquery'
import Tagify from '@yaireo/tagify'
import { debounce, has, get } from 'lodash'
import { Helpers } from '@rankMath/analyzer'

/**
 * WordPress dependencies
 */
import { doAction, addAction } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import getClassByScore from '@helpers/getClassByScore'

class FocusKeywords {
	/**
	 * Class constructor
	 */
	constructor() {
		if ( ! rankMath.canUser.general ) {
			return
		}

		this.elem = $( '#rank_math_focus_keyword' )
		this.elemWrapper = this.elem.parent()
		this.clickCount = 0
		this.selectedKeyword = {}

		const callbacks = {
			add: this.onAdd.bind( this ),
			remove: this.onRemove.bind( this ),
			edit: this.onEdit.bind( this ),
			click: this.onClick.bind( this ),
			setup: this.onSetup.bind( this ),
		}

		if ( rankMath.isUserRegistered ) {
			callbacks.input = debounce( this.onInput.bind( this ), 300 )
		}

		this.tagify = new Tagify( this.elem[ 0 ], {
			addTagOnBlur: true,
			maxTags: 'post' === rankMath.objectType ? rankMath.maxTags : 1,
			whitelist: this.whitelist || [],
			transformTag: ( tagData ) => {
				tagData.value = this.stripTags( tagData.value )
			},
			templates: {
				tag: (value, tagData) => {
				  return "<tag title='".concat(this.stripTags(value), "'\n                        contenteditable='false'\n                        spellcheck='false'\n                        class='tagify__tag ").concat(tagData["class"] ? tagData["class"] : "", "'\n                        ").concat(this.getAttributes_esc(tagData), ">\n                <x title='' class='tagify__tag__removeBtn' role='button' aria-label='remove tag'></x>\n                <div>\n                    <span class='tagify__tag-text'>").concat(this.stripTags(value), "</span>\n                </div>\n            </tag>");
				},
			},
			callbacks,
		} )

		this.selectFirstKeyword()

		this.setKeywordsClasses = this.setKeywordsClasses.bind( this )
		addAction(
			'rank_math_refresh_results',
			'rank-math',
			this.setKeywordsClasses,
			11
		)
	}

	onInput( value ) {
		value = has( value.detail, 'value' )
			? value.detail.value
			: value.detail.data.value

		if ( value.length < 2 ) {
			return
		}

		if ( this.hasAdded ) {
			this.hasAdded = false
			return
		}

		this.request = $.ajax( {
			url: rankMath.keywordsApi.url,
			data: {
				keyword: value,
				locale: rankMath.locale,
			},
			success: ( data ) => {
				if ( this.hasAdded ) {
					this.hasAdded = false
					return
				}

				const whiteList = $.map( data, function( item ) {
					return item
				} )

				this.tagify.settings.whitelist = whiteList
				this.tagify.dropdown.show.call( this.tagify, value )
			},
		} )
	}

	onAdd( event ) {
		this.hasAdded = true
		event.detail.tag.classList.add( 'bad-fk' )
		this.updateSelectedKeyword( event.detail )
		this.updateKeywords()
	}

	onRemove( event ) {
		if ( 0 === event.detail.index ) {
			this.onSetup()
		}

		rankMathEditor.resultManager.deleteResult( event.detail.data.value )
		this.selectFirstKeyword()
		this.updateKeywords()
	}

	onClick( event ) {
		this.updateSelectedKeyword( event.detail )
		setTimeout( function() {
			doAction( 'rank_math_refresh_results' )
		}, 500 )
	}

	onEdit( e ) {
		e.detail.tag.setAttribute( 'value', e.detail.data.value )
		this.hasAdded = true
		this.updateKeywords()
	}

	onSetup() {
		this.selectFirstKeyword()
		this.setKeywordsClasses()
	}

	updateSelectedKeyword( keyword ) {
		this.elemWrapper.find( 'tag.selected' ).removeClass( 'selected' )

		if ( '' !== keyword.tag ) {
			this.selectedKeyword = keyword
			keyword.tag.classList.add( 'selected' )
		} else {
			this.selectedKeyword = {}
		}
	}

	setKeywordsClasses() {
		if ( undefined === this.tagify.value ) {
			return
		}

		const values = this.tagify.value
		if ( values.length > 0 ) {
			const tags = this.elemWrapper.find( 'tag' )

			/*eslint array-callback-return: 0*/
			values.map( ( keyword, index ) => {
				const score = rankMathEditor.resultManager.getScore(
					Helpers.removeDiacritics( keyword.value )
				)

				tags[ index ].classList.remove( 'ok-fk', 'good-fk', 'bad-fk' )
				tags[ index ].classList.add( getClassByScore( score ) )
			} )
		}
	}

	selectFirstKeyword() {
		const values = this.elemWrapper.find( 'tag' )

		let keyword = {
			tag: '',
			index: 0,
			data: { value: '' },
		}

		if ( values.length > 0 ) {
			keyword = {
				tag: values[ 0 ],
				index: 0,
				data: { value: values[ 0 ].getAttribute( 'value' ) },
			}
		}

		this.updateSelectedKeyword( keyword )
	}

	updateKeywords() {
		rankMathEditor.refresh( 'keyword' )
	}

	getFocusKeywords() {
		if ( undefined === this.tagify || undefined === this.tagify.value ) {
			return [ '' ]
		}

		const keywords = this.tagify.value.map( ( tag ) => tag.value )
		return keywords.length ? keywords : [ '' ]
	}

	getSelectedKeyword() {
		return get( this.selectedKeyword, [ 'data', 'value' ], '' )
	}

	getAttributes_esc( data ) {
		// only items which are objects have properties which can be used as attributes
		if (Object.prototype.toString.call(data) != "[object Object]") return '';
		var keys = Object.keys(data),
			s = "",
			propName,
			i;

		for (i = keys.length; i--;) {
		  propName = keys[i];
		  if (propName != 'class' && data.hasOwnProperty(propName) && data[propName]) s += " " + propName + (data[propName] ? "=\"".concat(this.stripTags(data[propName]), "\"") : "");
		}

		return s;
	}

	stripTags( html ) {
		// First decode.
		html = jQuery('<textarea />').html( html ).text();

		// Strip tags.
		var doc = new DOMParser().parseFromString( html, 'text/html' );
		var output = doc.body.textContent || "";

		// Strip remaining characters.
		return output.replace( /["<>]/g, '' ) || '(invalid)';
	}
}

export default FocusKeywords
