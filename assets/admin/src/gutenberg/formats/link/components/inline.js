/**
 * WordPress dependencies
 */
import { useRef, useMemo } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { withSpokenMessages, Popover } from '@wordpress/components'
import { prependHTTP } from '@wordpress/url'
import {
	create,
	insert,
	isCollapsed,
	applyFormat,
	removeFormat,
	useAnchor,
	slice,
	replace,
	split,
	concat,
} from '@wordpress/rich-text'
import { LinkControl, store as blockEditorStore } from '@wordpress/block-editor'

/**
 * Internal dependencies
 */
import { createLinkFormat, isValidHref, getFormatBoundary } from './utils'
import { link as settings } from '../index'
import { useDispatch, useSelect } from '@wordpress/data'

const LINK_SETTINGS = [
	...LinkControl.DEFAULT_LINK_SETTINGS,
	{
		id: 'nofollow',
		title: __( 'Set to nofollow' ),
	},
	{
		id: 'sponsored',
		title: __( 'Set to sponsored' ),
	},
]

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
	const richLinkTextValue = getRichTextValueFromSelection( value, isActive )

	// Get the text content minus any HTML tags.
	const richTextText = richLinkTextValue.text

	const linkValue = useMemo(
		() => ( {
			url: activeAttributes.url,
			type: activeAttributes.type,
			id: activeAttributes.id,
			opensInNewTab: activeAttributes.target === '_blank',
			nofollow: activeAttributes.rel?.includes( 'nofollow' ),
			sponsored: activeAttributes.rel?.includes( 'sponsored' ),
			title: richTextText,
		} ),
		[
			activeAttributes.id,
			activeAttributes.rel,
			activeAttributes.target,
			activeAttributes.type,
			activeAttributes.url,
			richTextText,
		]
	)

	const { selectionChange } = useDispatch( blockEditorStore )
	const { selectionStart } = useSelect(
		( select ) => {
			const { getSettings, getSelectionStart } =
				select( blockEditorStore )
			const _settings = getSettings()

			return {
				createPageEntity: _settings.__experimentalCreatePageEntity,
				userCanCreatePages: _settings.__experimentalUserCanCreatePages,
				selectionStart: getSelectionStart(),
			}
		},
		[]
	)

	function removeLink() {
		const newValue = removeFormat( value, 'core/link' )
		onChange( newValue )
		stopAddingLink()
		speak( __( 'Link removed.' ), 'assertive' )
	}

	function onChangeLink( nextValue ) {
		const hasLink = linkValue?.url
		const isNewLink = ! hasLink

		// Merge the next value with the current link value.
		nextValue = {
			...linkValue,
			...nextValue,
		}

		const newUrl = prependHTTP( nextValue.url )
		const linkFormat = createLinkFormat( {
			url: newUrl,
			type: nextValue.type,
			id:
				nextValue.id !== undefined && nextValue.id !== null
					? String( nextValue.id )
					: undefined,
			opensInNewWindow: nextValue.opensInNewTab,
			nofollow: nextValue.nofollow,
			sponsored: nextValue.sponsored,
		} )

		const newText = nextValue.title || newUrl

		// Scenario: we have any active text selection or an active format.
		let newValue
		if ( isCollapsed( value ) && ! isActive ) {
			// Scenario: we don't have any actively selected text or formats.
			const inserted = insert( value, newText )

			newValue = applyFormat(
				inserted,
				linkFormat,
				value.start,
				value.start + newText.length
			)

			onChange( newValue )

			// Close the Link UI.
			stopAddingLink()

			// Move the selection to the end of the inserted link outside of the format boundary
			// so the user can continue typing after the link.
			selectionChange( {
				clientId: selectionStart.clientId,
				identifier: selectionStart.attributeKey,
				start: value.start + newText.length + 1,
			} )

			return
		} else if ( newText === richTextText ) {
			newValue = applyFormat( value, linkFormat )
		} else {
			// Scenario: Editing an existing link.

			// Create new RichText value for the new text in order that we
			// can apply formats to it.
			newValue = create( { text: newText } )
			// Apply the new Link format to this new text value.
			newValue = applyFormat( newValue, linkFormat, 0, newText.length )

			// Get the boundaries of the active link format.
			const boundary = getFormatBoundary( value, {
				type: 'core/link',
			} )

			// Split the value at the start of the active link format.
			// Passing "start" as the 3rd parameter is required to ensure
			// the second half of the split value is split at the format's
			// start boundary and avoids relying on the value's "end" property
			// which may not correspond correctly.
			const [ valBefore, valAfter ] = split(
				value,
				boundary.start,
				boundary.start
			)

			// Update the original (full) RichTextValue replacing the
			// target text with the *new* RichTextValue containing:
			// 1. The new text content.
			// 2. The new link format.
			// As "replace" will operate on the first match only, it is
			// run only against the second half of the value which was
			// split at the active format's boundary. This avoids a bug
			// with incorrectly targeted replacements.
			// See: https://github.com/WordPress/gutenberg/issues/41771.
			// Note original formats will be lost when applying this change.
			// That is expected behaviour.
			// See: https://github.com/WordPress/gutenberg/pull/33849#issuecomment-936134179.
			const newValAfter = replace( valAfter, richTextText, newValue )

			newValue = concat( valBefore, newValAfter )
		}

		onChange( newValue )

		// Focus should only be returned to the rich text on submit if this link is not
		// being created for the first time. If it is then focus should remain within the
		// Link UI because it should remain open for the user to modify the link they have
		// just created.
		if ( ! isNewLink ) {
			stopAddingLink()
		}

		if ( ! isValidHref( newUrl ) ) {
			speak(
				__(
					'Warning: the link has been inserted but may have errors. Please test it.'
				),
				'assertive'
			)
		} else if ( isActive ) {
			speak( __( 'Link edited.' ), 'assertive' )
		} else {
			speak( __( 'Link inserted.' ), 'assertive' )
		}
	}

	// The focusOnMount prop shouldn't evolve during render of a Popover
	// otherwise it causes a render of the content.
	const focusOnMount = useRef( addingLink ? 'firstElement' : false )

	const popoverAnchor = useAnchor( {
		editableContentElement: contentRef.current,
		settings: {
			...settings,
			isActive,
		},
	} )

	return (
		<Popover
			anchor={ popoverAnchor }
			focusOnMount={ focusOnMount.current }
			onClose={ stopAddingLink }
			position="bottom"
			offset={ 8 }
			shift
		>
			<LinkControl
				value={ linkValue }
				onChange={ onChangeLink }
				forceIsEditingLink={ addingLink }
				onRemove={ removeLink }
				settings={ LINK_SETTINGS }
				hasTextControl
				showInitialSuggestions
				suggestionsQuery={ {
					// always show Pages as initial suggestions
					initialSuggestionsSearchOptions: {
						type: 'post',
						subtype: 'page',
						perPage: 20,
					},
				} }
			/>
		</Popover>
	)
}

function getRichTextValueFromSelection( value, isActive ) {
	// Default to the selection ranges on the RichTextValue object.
	let textStart = value.start
	let textEnd = value.end

	// If the format is currently active then the rich text value
	// should always be taken from the bounds of the active format
	// and not the selected text.
	if ( isActive ) {
		const boundary = getFormatBoundary( value, {
			type: 'core/link',
		} )

		textStart = boundary.start

		// Text *selection* always extends +1 beyond the edge of the format.
		// We account for that here.
		textEnd = boundary.end + 1
	}

	// Get a RichTextValue containing the selected text content.
	return slice( value, textStart, textEnd )
}

export default withSpokenMessages( InlineLinkUI )
