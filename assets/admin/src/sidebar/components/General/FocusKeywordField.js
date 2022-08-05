/* global DOMParser */

/**
 * External dependencies
 */
import jQuery from 'jquery'
import { debounce, has, isUndefined } from 'lodash'
import { Helpers } from '@rankMath/analyzer'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Component, createRef } from '@wordpress/element'
import { withDispatch, withSelect } from '@wordpress/data'
import { applyFilters } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import TagifyField from '@components/TagifyField'
import FocusKeywordCTA from './FocusKeywordCTA'

class FocusKeywordField extends Component {
	/**
	 * Component state
	 *
	 * @type {Object}
	 */
	state = {}

	/**
	 * Ajax request
	 *
	 * @type {jqXHR}
	 */
	request = null

	/**
	 * Has Added
	 *
	 * @type {boolean}
	 */
	hasAdded = false

	/**
	 * Double click check.
	 *
	 * @type {Function}
	 */
	clickCount = 0

	/**
	 * Click timer.
	 *
	 * @type {number}
	 */
	singleClickTimer = null

	constructor( { keywords } ) {
		super()
		this.state = { showCTA: false }
		this.tagifyField = createRef()
		this.keywords = keywords
		this.hideDropdown = this.hideDropdown.bind( this )
		this.callbacks = {
			add: this.onAdd.bind( this ),
			remove: debounce( this.onRemove.bind( this ), 300 ),
			'edit:updated': this.onEditUpdated.bind( this ),
			click: this.onClick.bind( this ),
			dragEnd: this.onDragEnd.bind( this ),
			setup: this.onSetup.bind( this ),
			blur: this.hideDropdown,
		}

		if ( rankMath.isUserRegistered ) {
			this.callbacks.input = debounce( this.onInput.bind( this ), 300 )
		}

		window.rankMathEditor.focusKeywordField = this
	}

	render() {
		const settings = applyFilters(
			'rank_math_focus_keyword_settings',
			{
				addTagOnBlur: true,
				maxTags: 'post' === rankMath.objectType ? rankMath.maxTags : 1,
				whitelist: this.state.whitelist || [],
				focusableTags: true,
				transformTag: ( tagData ) => {
					tagData.value = this.stripTags( tagData.value.replaceAll( ',', '' ) )
				},
				templates: {
					tag: ( tagData ) => {
						const value = tagData.title || tagData.value
						let classes = ''
						if ( ! tagData.class ) {
							const score = rankMathEditor.resultManager.getScore(
								Helpers.removeDiacritics( value )
							)
							classes += this.getScoreClass( score )
						}
						return "<tag draggable='true' title='".concat( this.stripTags( value ), "'\n tabIndex='0'\n contenteditable='false'\n spellcheck='false'\n class='tagify__tag " ).concat( tagData.class ? tagData.class : classes, "'\n " ).concat( this.getAttributesEsc( tagData ), ">\n <x title='' class='tagify__tag__removeBtn' role='button' aria-label='remove tag'></x>\n <div>\n <span class='tagify__tag-text'>" ).concat( this.stripTags( value ), '</span>\n </div>\n </tag>' )
					},
				},
				callbacks: this.callbacks,
			},
			this
		)

		this.setKeywordsClasses()

		return (
			<>
				<TagifyField
					ref={ this.tagifyField }
					mode="input"
					settings={ settings }
					showDropdown={ this.state.showDropdown }
					initialValue={ this.keywords }
					placeholder={ __( 'Example: Rank Math SEO', 'rank-math' ) }
				/>

				{
					this.state.showCTA && (
						<FocusKeywordCTA onClick={ () => ( this.setState( { showCTA: false } ) ) } />
					)
				}
			</>
		)
	}

	shouldComponentUpdate( nextProps, nextState ) {
		if (
			this.state.showCTA !== nextState.showCTA ||
			this.state.showDropdown !== nextState.showDropdown ||
			nextProps.isRefreshing !== this.props.isRefreshing ||
			nextProps.keywords !== this.props.keywords
		) {
			return true
		}

		return false
	}

	getScoreClass( score ) {
		if ( 80 < score ) {
			return 'good-fk'
		}

		if ( 50 < score ) {
			return 'ok-fk'
		}

		return 'bad-fk'
	}

	setKeywordsClasses() {
		if ( null === this.tagifyField.current ) {
			return
		}

		const tagifyField = this.tagifyField.current
		const values = tagifyField.tagify.value
		if ( values.length > 0 ) {
			const tags = tagifyField.queryTags()

			/*eslint array-callback-return: 0*/
			values.map( ( keyword, index ) => {
				if ( isUndefined( tags[ index ] ) ) {
					return
				}

				const score = rankMathEditor.resultManager.getScore(
					Helpers.removeDiacritics( keyword.value )
				)
				tags[ index ].classList.remove( 'ok-fk', 'good-fk', 'bad-fk' )
				tags[ index ].classList.add( this.getScoreClass( score ) )
			} )
		}
	}

	onSetup() {
		this.selectFirstKeyword()
		this.setKeywordsClasses()
	}

	onInput( value ) {
		value = has( value.detail, 'value' )
			? value.detail.value
			: value.detail.data.value
		if ( value.length < 2 ) {
			return
		}

		this.hideDropdown()
		if ( this.hasAdded ) {
			this.hasAdded = false
			return
		}

		this.request = jQuery.ajax( {
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

				const whiteList = jQuery.map( data, function( item ) {
					return item
				} )
				this.setState( { whitelist: whiteList, showDropdown: value } )
			},
		} )
	}

	onAdd( event ) {
		this.hasAdded = true
		if ( 0 === event.detail.index ) {
			this.props.updateSelectedKeyword(
				event.detail,
				this.tagifyField.current
			)
		}

		this.updateKeywords()
	}

	onRemove( event ) {
		this.hideDropdown()
		if ( 0 === event.detail.index ) {
			this.onSetup()
		}
		rankMathEditor.resultManager.deleteResult( event.detail.data.value )
		this.selectFirstKeyword()
		this.updateKeywords()
	}

	onClick( event ) {
		this.clickCount++
		if ( 1 === this.clickCount ) {
			this.singleClickTimer = setTimeout( () => {
				this.clickCount = 0
				this.props.updateSelectedKeyword(
					event.detail,
					this.tagifyField.current
				)
			}, 400 )
		} else if ( 2 === this.clickCount ) {
			clearTimeout( this.singleClickTimer )
			this.clickCount = 0
		}
	}

	onEditUpdated( event ) {
		this.hasAdded = true

		if ( 0 === event.detail.index ) {
			this.props.updateSelectedKeyword(
				event.detail,
				this.tagifyField.current
			)
		}

		this.updateKeywords()
	}

	selectFirstKeyword() {
		const tagifyField = this.tagifyField.current
		const values = tagifyField.tagify.value

		let selectedKeyword = {
			tag: '',
			index: 0,
			data: { value: '' },
		}

		if ( values.length > 0 ) {
			const tags = tagifyField.queryTags()

			selectedKeyword = {
				tag: tags[ 0 ],
				index: 0,
				data: { value: values[ 0 ].value },
			}
		}

		this.props.updateSelectedKeyword( selectedKeyword, tagifyField )
	}

	updateKeywords() {
		const tagify = this.tagifyField.current,
			keywords = this.stripTags( tagify.toString() )
		this.props.updateKeywords( keywords )
	}

	onDragEnd() {
		const tags = this.tagifyField.current.queryTags()
		if ( 1 === tags.length ) {
			return
		}

		this.setState( { showCTA: true } )
	}

	hideDropdown() {
		if ( null !== this.request ) {
			this.request.abort()
			this.request = null
		}

		this.setState( { whitelist: [], showDropdown: false } )
	}

	getAttributesEsc( data ) {
		// only items which are objects have properties which can be used as attributes
		if ( Object.prototype.toString.call( data ) !== '[object Object]' ) {
			return ''
		}

		let s = '',
			propName,
			i

		const keys = Object.keys( data )
		for ( i = keys.length; i--; ) {
			propName = keys[ i ]
			if ( propName !== 'class' && data.hasOwnProperty( propName ) && data[ propName ] ) {
				s += '' + propName + ( data[ propName ] ? '=\"'.concat( this.stripTags( data[ propName ] ), '\"' ) : '' )
			}
		}

		return s
	}

	stripTags( html ) {
		// First decode.
		html = jQuery( '<textarea />' ).html( html ).text()

		// Strip tags.
		const doc = new DOMParser().parseFromString( html, 'text/html' )
		const output = doc.body.textContent || ''

		// Strip remaining characters.
		return output.replace( /["<>]/g, '' ) || ''
	}
}

export default compose(
	withSelect( ( select ) => {
		const repo = select( 'rank-math' )
		return {
			keywords: repo.getKeywords(),
			isRefreshing: repo.isRefreshing(),
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updateKeywords( keywords ) {
				dispatch( 'rank-math' ).updateKeywords( keywords )
			},

			updateSelectedKeyword( keyword, tagifyField ) {
				const tags = tagifyField.queryTags()
				tags.forEach( ( tag ) => {
					tag.classList.remove( 'selected' )
				} )

				if ( ! isUndefined( keyword.tag ) && '' !== keyword.tag ) {
					keyword.tag.classList.add( 'selected' )
				}

				dispatch( 'rank-math' ).updateSelectedKeyword( keyword )
			},
		}
	} )
)( FocusKeywordField )
