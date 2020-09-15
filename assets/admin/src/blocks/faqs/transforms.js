/**
 * WordPress dependencies
 */
import { createBlock } from '@wordpress/blocks'

/**
 * Transform yoast faq block.
 *
 * @type {Array}
 */
export default {
	from: [
		{
			type: 'block',
			blocks: [ 'yoast/faq-block' ],
			transform: ( yoast ) => {
				const questions = yoast.questions.map( ( question ) => {
					return {
						title: question.jsonQuestion,
						content: question.jsonAnswer,
						visible: true,
					}
				} )

				const attributes = {
					titleWrapper: 'h3',
					questions,
					className: yoast.className,
				}

				return createBlock( 'rank-math/faq-block', attributes )
			},
		},
	],
}
