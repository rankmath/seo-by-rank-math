/**
 * WordPress dependencies
 */
import {
	RichText,
	useBlockProps,
	BlockControls,
} from '@wordpress/block-editor'
import { useRef, useEffect } from '@wordpress/element'

export default ( {
	attributes,
	onReplace,
	setAttributes,
} ) => {
	const { content } = attributes
	const blockProps = useBlockProps( {
		className: 'rank-math-content-ai-command',
	} )

	const contentEditableRef = useRef( null )
	useEffect( () => {
		const { current: contentEditable } = contentEditableRef
		contentEditable.focus()

		const range = document.createRange()
		const selection = window.getSelection()
		range.selectNodeContents( contentEditable )
		range.collapse( false )
		selection.removeAllRanges()
		selection.addRange( range )
	}, [] )

	return (
		<div { ...blockProps }>
			<BlockControls />
			<RichText
				tagName="div"
				allowedFormats={ [] }
				value={ content }
				onChange={ ( newContent ) => {
					return setAttributes( { content: newContent } )
				} }
				onSplit={ () => {
					return false
				} }
				onReplace={ onReplace }
				data-empty={ content ? false : true }
				isSelected={ true }
				ref={ contentEditableRef }
			/>
		</div>
	)
}
