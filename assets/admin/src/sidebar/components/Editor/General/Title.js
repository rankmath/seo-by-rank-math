/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { withDispatch, withSelect } from '@wordpress/data'
import { TextControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import LengthIndicator from '@components/LengthIndicator'
import VariableInserter from '@components/VariableInserter'

const EditorTitle = ( { title, serpTitle, updateTitle } ) => (
	<div className="field-group">
		<label htmlFor="rank-math-editor-title">
			{ __( 'Title', 'rank-math' ) }
		</label>

		<LengthIndicator source={ serpTitle } min={ 15 } max={ 60 } pixelWidth={ 580 } widthCheckerClass={ 'title' } />

		<div className="variable-group">
			<TextControl
				id="rank-math-editor-title"
				value={ title }
				placeholder={ rankMath.assessor.serpData.titleTemplate }
				help={ __(
					'This is what will appear in the first line when this post shows up in the search results.',
					'rank-math'
				) }
				onChange={ updateTitle }
			/>

			<VariableInserter
				exclude={ [ 'seo_title', 'seo_description' ] }
				onClick={ ( variable ) =>
					updateTitle( title + ' %' + variable.variable + '%' )
				}
			/>
		</div>
	</div>
)

export default compose(
	withSelect( ( select ) => {
		const repo = select( 'rank-math' )

		return {
			title: repo.getTitle(),
			serpTitle: repo.getSerpTitle(),
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updateTitle( title ) {
				dispatch( 'rank-math' ).updateSerpTitle( title )
				dispatch( 'rank-math' ).updateTitle( title )
			},
		}
	} )
)( EditorTitle )
