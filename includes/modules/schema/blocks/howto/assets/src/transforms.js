/**
 * WordPress dependencies
 */
import { createBlock } from '@wordpress/blocks'

/**
 * Internal dependencies
 */
import generateId from '@helpers/generateId'

/**
 * Transform yoast howto block.
 *
 * @type {Array}
 */
export default {
	from: [
		{
			type: 'block',
			blocks: [ 'yoast/how-to-block' ],
			transform: ( yoast ) => {
				const steps = yoast.steps.map( ( step ) => {
					return {
						visible: true,
						id: generateId( 'howto-step' ),
						title: step.jsonName,
						content: step.jsonText,
					}
				} )

				const attributes = {
					steps,
					titleWrapper: 'h3',
					hasDuration: yoast.hasDuration,
					days: yoast.days,
					hours: yoast.hours,
					minutes: yoast.minutes,
					description: yoast.jsonDescription,
					className: yoast.className,
					listStyle: yoast.unorderedList ? 'unordered' : '',
				}

				return createBlock( 'rank-math/howto-block', attributes )
			},
		},
	],
}
