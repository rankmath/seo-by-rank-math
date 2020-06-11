/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data'

/**
 * Internal dependencies
 */
import EditorTitle from './Title'
import EditorPermalink from './Permalink'
import EditorDescription from './Description'
import SerpPreview from '@components/Editor/SerpPreview'

const EditorGeneralTab = () => (
	<div className="rank-math-editor-general">
		<SerpPreview showScore={ false } showDevices={ true } />

		{ <EditorTitle /> }

		{ <EditorPermalink /> }

		{ <EditorDescription /> }
	</div>
)

export default withSelect( ( select ) => {
	const repo = select( 'rank-math' )
	return {
		isNoIndex: 'noindex' in repo.getRobots(),
	}
} )( EditorGeneralTab )
