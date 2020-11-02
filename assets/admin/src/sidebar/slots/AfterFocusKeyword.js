/**
 * WordPress dependencies
 */
import { createSlotFill } from '@wordpress/components'

export const { Fill, Slot } = createSlotFill( 'RankMathAfterFocusKeyword' )

const RankMathAfterFocusKeyword = ( { children, className } ) => (
	<Fill>
		<div className={ className }>{ children }</div>
	</Fill>
)

RankMathAfterFocusKeyword.Slot = Slot

export default RankMathAfterFocusKeyword
