/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element'
import { PanelBody } from '@wordpress/components'
import { withDispatch } from '@wordpress/data'

/**
 * Internal dependencies
 */
import CheckLists from './CheckLists'
import FocusKeyword from './FocusKeyword'
import SerpPreview from '@components/Editor/SerpPreview'
import SnippetEditor from '@components/Editor/Editor'
import RankMathAfterEditor from '@slots/AfterEditor'

const GeneralTab = ( { toggleEditor } ) => (
	<Fragment>
		<PanelBody initialOpen={ true }>
			<SerpPreview onClick={ toggleEditor } />

			<SnippetEditor />

			<RankMathAfterEditor.Slot>
				{ ( fills ) => {
					if ( fills.length > 0 ) {
						return fills
					}

					return []
				} }
			</RankMathAfterEditor.Slot>
		</PanelBody>

		<FocusKeyword />

		{ rankMath.canUser.analysis && <CheckLists /> }
	</Fragment>
)

export default withDispatch( ( dispatch ) => {
	return {
		toggleEditor() {
			dispatch( 'rank-math' ).toggleSnippetEditor( true )
		},
	}
} )( GeneralTab )
