/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { withDispatch, withSelect } from '@wordpress/data'
import { TextareaControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import LengthIndicator from '@components/LengthIndicator'
import VariableInserter from '@components/VariableInserter'
import decodeEntities from '@helpers/decodeEntities'

const EditorDescription = ( {
	description,
	serpDescription,
	updateDescription,
} ) => (
	<div className="field-group">
		<label htmlFor="rank-math-editor-description">
			{ __( 'Description', 'rank-math' ) }
		</label>

		<LengthIndicator source={ serpDescription } min={ 80 } max={ 160 } pixelWidth={ 920 } widthCheckerClass={ 'description' } />

		<div className="variable-group">
			<TextareaControl
				id="rank-math-editor-description"
				value={ decodeEntities( description ) }
				placeholder={
					serpDescription
						? serpDescription
						: rankMath.assessor.serpData.descriptionTemplate
				}
				help={ __(
					'This is what will appear as the description when this post shows up in the search results.',
					'rank-math'
				) }
				onChange={ updateDescription }
			/>

			<VariableInserter
				exclude={ [ 'seo_title', 'seo_description' ] }
				onClick={ ( variable ) =>
					updateDescription(
						description + ' %' + variable.variable + '%'
					)
				}
			/>
		</div>
	</div>
)

export default compose(
	withSelect( ( select ) => {
		const repo = select( 'rank-math' )
		return {
			description: repo.getDescription(),
			serpDescription: repo.getSerpDescription(),
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updateDescription( description ) {
				dispatch( 'rank-math' ).updateSerpDescription( description )
				dispatch( 'rank-math' ).updateDescription( description )
			},
		}
	} )
)( EditorDescription )
