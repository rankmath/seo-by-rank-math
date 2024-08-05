/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { useState, useEffect } from '@wordpress/element'

/**
 * Internal dependencies
 */
import markdownConverter from '../helpers/markdownConverter'
import getTypingWorker from '../helpers/getTypingWorker'

/**
 * ContentAIText Component.
 *
 * @param {Object}  props                 Component props.
 * @param {string}  props.value           Content to add in the editor with typing effect.
 * @param {boolean} props.showWordCount   Whether to show word count.
 * @param {boolean} props.addTypingEffect Whether to show typing effect.
 */
export default ( { value, showWordCount = true, addTypingEffect = true } ) => {
	// value = markdownConverter( value )
	const words = value.split( ' ' )

	const [ typingEffect, setTypingEffect ] = useState( addTypingEffect ? '' : value )
	const [ typingWorker, setTypingWorker ] = useState( null )

	if ( addTypingEffect ) {
		useEffect( () => {
			const worker = getTypingWorker()
			setTypingWorker( worker )

			worker.onmessage = ( event ) => {
				if ( event.data !== 'rank_math_process_complete' ) {
					setTypingEffect( ( prevTyping ) => prevTyping + event.data )
				}
			}

			return () => {
				worker.terminate()
			}
		}, [] )

		useEffect( () => {
			if ( typingWorker ) {
				setTypingEffect( '' )
				typingWorker.postMessage( value )
			}
		}, [ value, typingWorker ] )
	}

	const contentClass = typingEffect.length < words.length ? 'content typing' : 'content'
	return (
		<>
			{
				showWordCount &&
				<div className="word-count">
					{
						// Translators: placeholder is the content length.
						sprintf( __( 'Words: %d', 'rank-math' ), typingEffect.split( ' ' ).length )
					}
				</div>
			}
			<div
				className={ contentClass }
				dangerouslySetInnerHTML={ {
					__html: markdownConverter( typingEffect ),
				} }
			></div>
		</>
	)
}
