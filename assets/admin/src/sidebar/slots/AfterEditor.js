/**
 * WordPress dependencies
 */
import { createSlotFill, PanelRow } from '@wordpress/components'

export const { Fill, Slot } = createSlotFill( 'RankMathAfterEditor' )

const RankMathAfterEditor = ( { children, className } ) => (
	<Fill>
		<PanelRow className={ className }>{ children }</PanelRow>
	</Fill>
)

RankMathAfterEditor.Slot = Slot

export default RankMathAfterEditor
