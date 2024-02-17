/**
 * WordPress dependencies
 */
import {
	RichText,
	useBlockProps,
	BlockControls,
} from '@wordpress/block-editor'
import { useRef, useEffect } from '@wordpress/element'
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import hasError from '../../../../assets/src/page/helpers/hasError'

const getErrorMessage = () => {
	if ( ! rankMath.isUserRegistered ) {
		return (
			<>
				{
					__( 'Start using Content AI by connecting your RankMath account.', 'rank-math' )
				}
				<a href={ rankMath.connectSiteUrl }>{ __( 'Connect Now', 'rank-math' ) }</a>
			</>
		)
	}

	if ( ! rankMath.contentAIPlan ) {
		return (
			<>
				{
					__( 'You do not have a Content AI plan.', 'rank-math' )
				}
				<a href="https://rankmath.com/kb/how-to-use-content-ai/?play-video=ioPeVIntJWw&utm_source=Plugin&utm_medium=Buy+Plan+Button&utm_campaign=WP">{ __( 'Choose your plan', 'rank-math' ) }</a>
			</>
		)
	}

	return (
		<>
			{
				__( 'You have exhausted your Content AI Credits.', 'rank-math' )
			}
			<a href="https://rankmath.com/kb/how-to-use-content-ai/?play-video=ioPeVIntJWw&utm_source=Plugin&utm_medium=Buy+Credits+Button&utm_campaign=WP" target="_blank" rel="noreferrer">{ __( 'Get more', 'rank-math' ) }</a>
		</>
	)
}

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
			{
				hasError() &&
				<div className="rich-text" ref={ contentEditableRef }>
					{ getErrorMessage() }
				</div>
			}
			{
				! hasError() &&
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
			}
		</div>
	)
}
