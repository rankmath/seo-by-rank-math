/**
 * WordPress dependencies
 */
import { createSlotFill, PanelRow } from '@wordpress/components'

export const { Fill, Slot } = createSlotFill( 'RankMathAfterFocusKeyword' )

const RankMathAfterFocusKeyword = ( { children, className } ) => (
	<Fill>
		<PanelRow className={ className }>{ children }</PanelRow>
	</Fill>
)

RankMathAfterFocusKeyword.Slot = Slot

export default RankMathAfterFocusKeyword
