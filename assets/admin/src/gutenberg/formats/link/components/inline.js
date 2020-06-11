/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { prependHTTP } from '@wordpress/url'
import { URLPopover } from '@wordpress/block-editor'
import { getRectangleFromRange } from '@wordpress/dom'
import { ToggleControl, withSpokenMessages } from '@wordpress/components'
import { Component, createRef, useMemo, Fragment } from '@wordpress/element'
import { LEFT, RIGHT, UP, DOWN, BACKSPACE, ENTER } from '@wordpress/keycodes'
import {
	create,
	insert,
	isCollapsed,
	applyFormat,
	getTextContent,
	slice,
} from '@wordpress/rich-text'

/**
 * Internal dependencies
 */
import { createLinkFormat, isValidHref } from './utils'

const stopKeyPropagation = ( event ) => event.stopPropagation()

function isShowingInput( props, state ) {
	return props.addingLink || state.editLink
}

const URLPopoverAtLink = ( { isActive, addingLink, value, ...props } ) => {
	const anchorRef = useMemo( () => {
		const selection = window.getSelection()
		if ( ! selection.rangeCount ) {
			return
		}

		const range = selection.getRangeAt( 0 )

		if ( addingLink ) {
			return getRectangleFromRange( range )
		}

		let element = range.startContainer

		// If the caret is right before the element, select the next element.
		element = element.nextElementSibling || element

		while ( element.nodeType !== window.Node.ELEMENT_NODE ) {
			element = element.parentNode
		}

		const closest = element.closest( 'a' )
		if ( closest ) {
			return closest.getBoundingClientRect()
		}
	}, [ isActive, addingLink, value.start, value.end ] )

	if ( ! anchorRef ) {
		return null
	}

	return <URLPopover anchorRect={ anchorRef } { ...props } />
}

class InlineLinkUI extends Component {
	constructor() {
		super( ...arguments )

		this.editLink = this.editLink.bind( this )
		this.submitLink = this.submitLink.bind( this )
		this.onKeyDown = this.onKeyDown.bind( this )
		this.onChangeInputValue = this.onChangeInputValue.bind( this )
		this.setLinkTarget = this.setLinkTarget.bind( this )
		this.setNoFollow = this.setNoFollow.bind( this )
		this.setSponsored = this.setSponsored.bind( this )
		this.onFocusOutside = this.onFocusOutside.bind( this )
		this.resetState = this.resetState.bind( this )
		this.autocompleteRef = createRef()

		this.state = {
			opensInNewWindow: false,
			noFollow: false,
			sponsored: false,
			inputValue: '',
		}
	}

	static getDerivedStateFromProps( props, state ) {
		const {
			activeAttributes: { url, target, rel },
		} = props
		const opensInNewWindow = target === '_blank'

		if ( ! isShowingInput( props, state ) ) {
			const update = {}
			if ( url !== state.inputValue ) {
				update.inputValue = url
			}

			if ( opensInNewWindow !== state.opensInNewWindow ) {
				update.opensInNewWindow = opensInNewWindow
			}

			if ( typeof rel === 'string' ) {
				const noFollow = rel.split( ' ' ).includes( 'nofollow' )
				const sponsored = rel.split( ' ' ).includes( 'sponsored' )

				if ( noFollow !== state.noFollow ) {
					update.noFollow = noFollow
				}

				if ( sponsored !== state.sponsored ) {
					update.sponsored = sponsored
				}
			}
			return Object.keys( update ).length ? update : null
		}

		return null
	}

	onKeyDown( event ) {
		if (
			[ LEFT, DOWN, RIGHT, UP, BACKSPACE, ENTER ].indexOf(
				event.keyCode
			) > -1
		) {
			// Stop the key event from propagating up to ObserveTyping.startTypingInTextField.
			event.stopPropagation()
		}
	}

	onChangeInputValue( inputValue ) {
		this.setState( { inputValue } )
	}

	setLinkTarget( opensInNewWindow ) {
		const {
			activeAttributes: { url = '' },
			value,
			onChange,
		} = this.props

		this.setState( { opensInNewWindow } )

		// Apply now if URL is not being edited.
		if ( ! isShowingInput( this.props, this.state ) ) {
			const selectedText = getTextContent( slice( value ) )

			onChange(
				applyFormat(
					value,
					createLinkFormat( {
						url,
						opensInNewWindow,
						noFollow: this.state.noFollow,
						sponsored: this.state.sponsored,
						text: selectedText,
					} )
				)
			)
		}
	}

	setNoFollow( noFollow ) {
		const {
			activeAttributes: { url = '' },
			value,
			onChange,
		} = this.props

		this.setState( { noFollow } )

		// Apply now if URL is not being edited.
		if ( ! isShowingInput( this.props, this.state ) ) {
			const selectedText = getTextContent( slice( value ) )

			onChange(
				applyFormat(
					value,
					createLinkFormat( {
						url,
						opensInNewWindow: this.state.opensInNewWindow,
						noFollow,
						sponsored: this.state.sponsored,
						text: selectedText,
					} )
				)
			)
		}
	}

	setSponsored( sponsored ) {
		const {
			activeAttributes: { url = '' },
			value,
			onChange,
		} = this.props

		this.setState( { sponsored } )

		// Apply now if URL is not being edited.
		if ( ! isShowingInput( this.props, this.state ) ) {
			const selectedText = getTextContent( slice( value ) )

			onChange(
				applyFormat(
					value,
					createLinkFormat( {
						url,
						opensInNewWindow: this.state.opensInNewWindow,
						noFollow: this.state.noFollow,
						sponsored,
						text: selectedText,
					} )
				)
			)
		}
	}

	editLink( event ) {
		this.setState( { editLink: true } )
		event.preventDefault()
	}

	submitLink( event ) {
		const { isActive, value, onChange, speak } = this.props
		const { inputValue, opensInNewWindow, noFollow, sponsored } = this.state
		const url = prependHTTP( inputValue )
		const selectedText = getTextContent( slice( value ) )
		const format = createLinkFormat( {
			url,
			opensInNewWindow,
			noFollow,
			sponsored,
			text: selectedText,
		} )

		event.preventDefault()

		if ( isCollapsed( value ) && ! isActive ) {
			const toInsert = applyFormat(
				create( { text: url } ),
				format,
				0,
				url.length
			)
			onChange( insert( value, toInsert ) )
		} else {
			onChange( applyFormat( value, format ) )
		}

		this.resetState()

		if ( ! isValidHref( url ) ) {
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

	onFocusOutside() {
		// The autocomplete suggestions list renders in a separate popover (in a portal),
		// so onFocusOutside fails to detect that a click on a suggestion occurred in the
		// LinkContainer. Detect clicks on autocomplete suggestions using a ref here, and
		// return to avoid the popover being closed.
		const autocompleteElement = this.autocompleteRef.current
		if (
			autocompleteElement &&
			autocompleteElement.contains( document.activeElement )
		) {
			return
		}

		this.resetState()
	}

	resetState() {
		this.props.stopAddingLink()
		this.setState( { editLink: false } )
	}

	render() {
		const {
			isActive,
			activeAttributes: { url },
			addingLink,
			value,
		} = this.props

		if ( ! isActive && ! addingLink ) {
			return null
		}

		const { inputValue, opensInNewWindow, noFollow, sponsored } = this.state
		const showInput = isShowingInput( this.props, this.state )
		return (
			<URLPopoverAtLink
				value={ value }
				isActive={ isActive }
				addingLink={ addingLink }
				onFocusOutside={ this.onFocusOutside }
				onClose={ this.resetState }
				focusOnMount={ showInput ? 'firstElement' : false }
				renderSettings={ () => (
					<Fragment>
						<ToggleControl
							label={ __( 'Open in New Tab', 'rank-math' ) }
							checked={ opensInNewWindow }
							onChange={ this.setLinkTarget }
						/>
						<ToggleControl
							label={ __( 'Nofollow', 'rank-math' ) }
							checked={ noFollow }
							onChange={ this.setNoFollow }
						/>
						<ToggleControl
							label={ __( 'Sponsored', 'rank-math' ) }
							checked={ sponsored }
							onChange={ this.setSponsored }
						/>
					</Fragment>
				) }
			>
				{ showInput ? (
					<URLPopover.LinkEditor
						className="editor-format-toolbar__link-container-content block-editor-format-toolbar__link-container-content"
						value={ inputValue }
						onChangeInputValue={ this.onChangeInputValue }
						onKeyDown={ this.onKeyDown }
						onKeyPress={ stopKeyPropagation }
						onSubmit={ this.submitLink }
						autocompleteRef={ this.autocompleteRef }
					/>
				) : (
					<URLPopover.LinkViewer
						className="editor-format-toolbar__link-container-content block-editor-format-toolbar__link-container-content"
						onKeyPress={ stopKeyPropagation }
						url={ url }
						onEditLinkClick={ this.editLink }
						linkClassName={
							isValidHref( prependHTTP( url ) )
								? undefined
								: 'has-invalid-link'
						}
					/>
				) }
			</URLPopoverAtLink>
		)
	}
}

export default withSpokenMessages( InlineLinkUI )
