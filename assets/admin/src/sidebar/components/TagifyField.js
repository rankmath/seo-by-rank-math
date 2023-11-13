/**
 * External dependencies
 */
import Tagify from '@yaireo/tagify'

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element'
import { doAction } from '@wordpress/hooks'

class TagifyField extends Component {
	constructor( props ) {
		super( props )
		this._handleRef = this._handleRef.bind( this )
	}

	componentDidMount() {
		this.tagify = new Tagify( this.component, this.props.settings || {} )
		if ( this.props.settings.callbacks.setup ) {
			setTimeout( () => {
				this.props.settings.callbacks.setup.call( this.tagify )
				this.tagify.DOM.input.setAttribute( 'contenteditable', true )
				this.tagify.DOM.input.addEventListener(
					'blur',
					this.props.settings.callbacks.blur
				)
			}, 100 )
		}

		if ( this.props.settings.callbacks.dragEnd ) {
			this.tagify.DOM.scope.addEventListener( 'dragend', this.props.settings.callbacks.dragEnd )
		}

		doAction( 'rank_math_tagify_init', this )
	}

	shouldComponentUpdate( nextProps ) {
		this.tagify.settings.whitelist = nextProps.settings.whitelist

		if ( nextProps.showDropdown ) {
			this.tagify.dropdown.show.call(
				this.tagify,
				nextProps.showDropdown
			)
		}

		if ( false === nextProps.showDropdown ) {
			this.tagify.dropdown.hide.call( this.tagify, true )
		}

		// Do not allow react to re-render since the component is modifying its own HTML.
		return false
	}

	_handleRef( component ) {
		this.component = component
	}

	render() {
		const attrs = {
			ref: this._handleRef,
			id: this.props.id,
			name: this.props.name,
			className: this.props.className,
			placeholder: this.props.placeholder,
		}
		return this.props.mode === 'textarea' ? (
			<textarea
				{ ...attrs }
				defaultValue={ this.props.initialValue }
			></textarea>
		) : (
			<input { ...attrs } defaultValue={ this.props.initialValue } />
		)
	}

	toArray() {
		return this.tagify.value.map( ( tag ) => tag.value )
	}

	toString() {
		return this.toArray().join( ',' )
	}

	queryTags() {
		return this.tagify.DOM.scope.querySelectorAll( 'tag' )
	}
}

export default TagifyField
