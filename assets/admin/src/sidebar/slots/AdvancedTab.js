/**
 * WordPress dependencies
 */
import { createSlotFill, PanelRow } from '@wordpress/components'

export const { Fill, Slot } = createSlotFill( 'RankMathAdvancedTab' )

const RankMathAdvancedTab = ( { children, className } ) => (
	<Fill>
		<PanelRow className={ className }>{ children }</PanelRow>
	</Fill>
)

RankMathAdvancedTab.Slot = Slot

export default RankMathAdvancedTab
