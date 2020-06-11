/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { withDispatch, withSelect } from '@wordpress/data'
import { PanelBody, SelectControl } from '@wordpress/components'

const MusicSnippet = ( props ) => (
	<PanelBody initialOpen={ true }>
		<SelectControl
			label={ __( 'Type', 'rank-math' ) }
			value={ props.musicType }
			options={ [
				{ value: 'MusicGroup', label: __( 'MusicGroup', 'rank-math' ) },
				{ value: 'MusicAlbum', label: __( 'MusicAlbum', 'rank-math' ) },
			] }
			onChange={ props.updateType }
		/>
	</PanelBody>
)

export default compose(
	withSelect( ( select ) => {
		const data = select( 'rank-math' ).getRichSnippets()

		return {
			musicType: data.musicType,
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updateType( type ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'musicType',
					'music_type',
					type
				)
			},
		}
	} )
)( MusicSnippet )
