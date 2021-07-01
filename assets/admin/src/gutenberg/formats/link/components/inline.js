/**
 * External dependencies
 */
import { isUndefined, isNull } from 'lodash'

/**
 * WordPress dependencies
 */
import { useState, useRef } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { withSpokenMessages, Popover } from '@wordpress/components'
import { prependHTTP } from '@wordpress/url'
import {
	create,
	insert,
	isCollapsed,
	applyFormat,
	useAnchorRef,
} from '@wordpress/rich-text'
import { __experimentalLinkControl as LinkControl } from '@wordpress/block-editor'

/**
 * Internal dependencies
 */
import { createLinkFormat, isValidHref } from './utils'
import { link as settings } from '../index'

function InlineLinkUI( {
	isActive,
	activeAttributes,
	addingLink,
	value,
	onChange,
	speak,
	stopAddingLink,
	contentRef,
} ) {
	/**
	 * Pending settings to be applied to the next link. When inserting a new
	 * link, toggle values cannot be applied immediately, because there is not
	 * yet a link for them to apply to. Thus, they are maintained in a state
	 * value until the time that the link can be inserted or edited.
	 *
	 */
	const [ nextLinkValue, setNextLinkValue ] = useState()

	const linkValue = {
		url: activeAttributes.url,
		type: activeAttributes.type,
		id: activeAttributes.id,
		opensInNewTab: activeAttributes.target === '_blank',
		noFollow: ! isUndefined( activeAttributes.rel ) && -1 !== activeAttributes.rel.indexOf( 'nofollow' ),
		sponsored: ! isUndefined( activeAttributes.rel ) && -1 !== activeAttributes.rel.indexOf( 'sponsored' ),
		...nextLinkValue,
	}

	function onChangeLink( nextValue ) {
		// Merge with values from state, both for the purpose of assigning the
		// next state value, and for use in constructing the new link format if
		// the link is ready to be applied.
		nextValue = {
			...nextLinkValue,
			...nextValue,
		}

		// LinkControl calls `onChange` immediately upon the toggling a setting.
		const didToggleSetting =
			linkValue.url === nextValue.url &&
			( linkValue.opensInNewTab !== nextValue.opensInNewTab ||
			linkValue.noFollow !== nextValue.noFollow ||
			linkValue.sponsored !== nextValue.sponsored )

		// If change handler was called as a result of a settings change during
		// link insertion, it must be held in state until the link is ready to
		// be applied.
		const didToggleSettingForNewLink =
			didToggleSetting && isUndefined( nextValue.url )

		// If link will be assigned, the state value can be considered flushed.
		// Otherwise, persist the pending changes.
		setNextLinkValue( didToggleSettingForNewLink ? nextValue : undefined )

		if ( didToggleSettingForNewLink ) {
			return
		}

		const newUrl = prependHTTP( nextValue.url )
		const format = createLinkFormat( {
			url: newUrl,
			type: nextValue.type,
			id:
				! isUndefined( nextValue.id ) && ! isNull( nextValue.id )
					? String( nextValue.id )
					: undefined,
			opensInNewWindow: nextValue.opensInNewTab,
			noFollow: nextValue.noFollow,
			sponsored: nextValue.sponsored,
		} )

		if ( isCollapsed( value ) && ! isActive ) {
			const newText = nextValue.title || newUrl
			const toInsert = applyFormat(
				create( { text: newText } ),
				format,
				0,
				newText.length
			)
			onChange( insert( value, toInsert ) )
		} else {
			const newValue = applyFormat( value, format )
			newValue.start = newValue.end
			newValue.activeFormats = []
			onChange( newValue )
		}

		// Focus should only be shifted back to the formatted segment when the
		// URL is submitted.
		if ( ! didToggleSetting ) {
			stopAddingLink()
		}

		if ( ! isValidHref( newUrl ) ) {
			speak(
				__(
					'Warning: the link has been inserted but may have errors. Please test it.',
					'rank-math'
				),
				'assertive'
			)
		} else if ( isActive ) {
			speak( __( 'Link edited.', 'rank-math' ), 'assertive' )
		} else {
			speak( __( 'Link inserted.', 'rank-math' ), 'assertive' )
		}
	}

	// The focusOnMount prop shouldn't evolve during render of a Popover
	// otherwise it causes a render of the content.
	const focusOnMount = useRef( addingLink ? 'firstElement' : false )

	const linkControlSettings = [
		{
			id: 'opensInNewTab',
			title: __( 'Open in new tab.', 'rank-math' ),
		},
		{
			id: 'noFollow',
			title: __( 'Set to nofollow.', 'rank-math' ),
		},
		{
			id: 'sponsored',
			title: __( 'Set to sponsored.', 'rank-math' ),
		},
	]

	const anchorRef = useAnchorRef( { ref: contentRef, value, settings } )

	return (
		<Popover
			anchorRef={ anchorRef }
			focusOnMount={ focusOnMount.current }
			onClose={ stopAddingLink }
			position="bottom center"
		>
			<LinkControl
				value={ linkValue }
				onChange={ onChangeLink }
				forceIsEditingLink={ addingLink }
				settings={ linkControlSettings }
			/>
		</Popover>
	)
}

export default withSpokenMessages( InlineLinkUI )
