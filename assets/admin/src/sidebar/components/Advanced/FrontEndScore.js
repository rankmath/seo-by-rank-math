/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { withDispatch, withSelect } from '@wordpress/data'
import { BaseControl, ToggleControl } from '@wordpress/components'

const FrontEndScore = ( { showScore, toggleScore } ) => (
	<BaseControl className="rank-math-frontend-score">
		<ToggleControl
			label={ __( 'Show SEO Score on Front-end', 'rank-math' ) }
			checked={ showScore }
			onChange={ toggleScore }
		/>
	</BaseControl>
)

export default compose(
	withSelect( ( select ) => {
		return {
			showScore: select( 'rank-math' ).getShowScoreFrontend(),
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			toggleScore( value ) {
				dispatch( 'rank-math' ).toggleFrontendScore( value )
			},
		}
	} )
)( FrontEndScore )
