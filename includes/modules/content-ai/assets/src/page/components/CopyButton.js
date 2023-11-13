/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState } from '@wordpress/element'
import { Button } from '@wordpress/components'
import { useCopyToClipboard } from '@wordpress/compose'
import { serialize } from '@wordpress/blocks'

/**
 * Copy Button component.
 *
 * @param {Object} props          Component props.
 * @param {string} props.value    Content to copy.
 * @param {string} props.label    Copy Button label.
 * @param {string} props.disabled Is Copy button disabled.
 * @param {string} props.onClick  Function to call when button is clicked.
 */
export default ( { value, label = '', disabled = false, onClick = '' } ) => {
	const [ isCopied, setCopied ] = useState()

	if ( onClick ) {
		value = serialize( wp.data.select( 'core/block-editor' ).getBlocks() )
	}

	label = label ? label : __( 'Copy', 'rank-math' )
	const copyClipboardRef = useCopyToClipboard( value )

	return (
		<Button
			variant="secondary"
			className="button structured-data-copy is-small"
			ref={ copyClipboardRef }
			disabled={ disabled }
			onClick={ () => {
				setCopied( true )
				setTimeout( () => {
					setCopied( false )
				}, 700 )
			} }
		>
			<i className="rm-icon rm-icon-copy"></i>
			{ isCopied ? __( 'Copied!', 'rank-math' ) : label }
		</Button>
	)
}
