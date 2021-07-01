/**
 * External dependencies
 */
import { map } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState } from '@wordpress/element'
import { link as linkIcon, linkOff } from '@wordpress/icons'
import { isURL, isEmail } from '@wordpress/url'
import {
	getTextContent,
	applyFormat,
	removeFormat,
	slice,
} from '@wordpress/rich-text'
import {
	RichTextToolbarButton,
	RichTextShortcut,
} from '@wordpress/block-editor'

/**
 * Internal dependencies
 */
import InlineLinkUI from './inline'

const name = 'core/link'

function Edit( {
	isActive,
	activeAttributes,
	value,
	onChange,
	onFocus,
	contentRef,
} ) {
	const [ addingLink, setAddingLink ] = useState( false )
	function addLink() {
		const text = getTextContent( slice( value ) )

		if ( text && isURL( text ) ) {
			onChange(
				applyFormat( value, {
					type: name,
					attributes: { url: text },
				} )
			)
		} else if ( text && isEmail( text ) ) {
			onChange(
				applyFormat( value, {
					type: name,
					attributes: { url: `mailto:${ text }` },
				} )
			)
		} else {
			setAddingLink( true )
		}
	}

	function stopAddingLink() {
		setAddingLink( false )
		onFocus()
	}

	function onRemoveFormat() {
		let newValue = value
		newValue = removeFormat( newValue, 'core/link' )

		onChange( { ...newValue } )
	}

	return (
		<>
			<RichTextShortcut type="primary" character="k" onUse={ addLink } />
			<RichTextShortcut
				type="primaryShift"
				character="k"
				onUse={ onRemoveFormat }
			/>
			{ isActive && (
				<RichTextToolbarButton
					name="link"
					icon={ linkOff }
					title={ __( 'Unlink', 'rank-math' ) }
					onClick={ onRemoveFormat }
					isActive={ isActive }
					shortcutType="primaryShift"
					shortcutCharacter="k"
				/>
			) }
			{ ! isActive && (
				<RichTextToolbarButton
					name="link"
					icon={ linkIcon }
					title={ __( 'Link', 'rank-math' ) }
					onClick={ addLink }
					isActive={ isActive }
					shortcutType="primary"
					shortcutCharacter="k"
				/>
			) }
			{ ( addingLink || isActive ) && (
				<InlineLinkUI
					addingLink={ addingLink }
					stopAddingLink={ stopAddingLink }
					isActive={ isActive }
					activeAttributes={ activeAttributes }
					value={ value }
					onChange={ onChange }
					contentRef={ contentRef }
				/>
			) }
		</>
	)
}

export default Edit
