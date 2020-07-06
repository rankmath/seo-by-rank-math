/**
 * External dependencies
 */
import { get, map } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { isURL, isEmail } from '@wordpress/url'
import { dispatch, select } from '@wordpress/data'
import { Component, Fragment } from '@wordpress/element'
import { withSpokenMessages } from '@wordpress/components'
import {
	RichTextToolbarButton,
	RichTextShortcut,
} from '@wordpress/block-editor'
import {
	getActiveFormat,
	getTextContent,
	applyFormat,
	removeFormat,
	slice,
} from '@wordpress/rich-text'

/**
 * Internal dependencies
 */
import InlineLinkUI from './inline'

const name = 'rankmath/link'

class LinkEdit extends Component {
	constructor() {
		super( ...arguments )

		this.state = { addingLink: false }
		this.addLink = this.addLink.bind( this )
		this.stopAddingLink = this.stopAddingLink.bind( this )
		this.onRemoveFormat = this.onRemoveFormat.bind( this )
	}

	componentDidMount() {
		const { getFormatType } = select( 'core/rich-text' )
		const oldFormat = getFormatType( 'core/link' )
		if ( oldFormat ) {
			oldFormat.edit = null
			dispatch( 'core/rich-text' ).addFormatTypes( oldFormat )
		}
	}

	addLink() {
		const { value, onChange } = this.props
		const text = getTextContent( slice( value ) )

		if ( text && isURL( text ) ) {
			onChange(
				applyFormat( value, { type: name, attributes: { url: text } } )
			)
		} else if ( text && isEmail( text ) ) {
			onChange(
				applyFormat( value, {
					type: name,
					attributes: { url: `mailto:${ text }` },
				} )
			)
		} else {
			this.setState( { addingLink: true } )
		}
	}

	stopAddingLink() {
		this.setState( { addingLink: false } )
	}

	onRemoveFormat() {
		const { value, onChange, speak } = this.props

		let newValue = value

		map( [ 'core/link', 'rankmath/link' ], ( linkFormat ) => {
			newValue = removeFormat( newValue, linkFormat )
		} )

		onChange( { ...newValue } )
		speak( __( 'Link removed.', 'rank-math' ), 'assertive' )
	}

	render() {
		const { activeAttributes, onChange } = this.props
		let { isActive, value } = this.props
		const activeFormat = getActiveFormat( value, 'core/link' )

		if ( activeFormat ) {
			const rel = get( activeFormat, 'unregisteredAttributes.rel', false )
			activeFormat.type = name

			if ( rel ) {
				activeFormat.attributes = Object.assign(
					activeFormat.attributes,
					{ rel }
				)
			}

			activeFormat.unregisteredAttributes.class = ''
			let newValue = value
			newValue = applyFormat( newValue, activeFormat )
			newValue = removeFormat( newValue, 'core/link' )
			onChange( { ...newValue } )

			value = newValue

			isActive = true
		}

		return (
			<Fragment>
				<RichTextShortcut
					type="primary"
					character="k"
					onUse={ this.addLink }
				/>
				<RichTextShortcut
					type="primaryShift"
					character="k"
					onUse={ this.onRemoveFormat }
				/>

				{ isActive && (
					<RichTextToolbarButton
						name="link"
						icon="editor-unlink"
						title={ __( 'Unlink', 'rank-math' ) }
						onClick={ this.onRemoveFormat }
						isActive={ isActive }
						shortcutType="primaryShift"
						shortcutCharacter="k"
					/>
				) }

				{ ! isActive && (
					<RichTextToolbarButton
						name="link"
						icon="admin-links"
						title={ __( 'Add Link', 'rank-math' ) }
						onClick={ this.addLink }
						isActive={ isActive }
						shortcutType="primary"
						shortcutCharacter="k"
					/>
				) }

				<InlineLinkUI
					addingLink={ this.state.addingLink }
					stopAddingLink={ this.stopAddingLink }
					isActive={ isActive }
					activeAttributes={ activeAttributes }
					value={ value }
					onChange={ onChange }
				/>
			</Fragment>
		)
	}
}

export default withSpokenMessages( LinkEdit )
