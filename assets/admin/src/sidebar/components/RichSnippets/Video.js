/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { withDispatch, withSelect } from '@wordpress/data'
import { PanelBody, TextControl } from '@wordpress/components'

const VideoSnippet = ( props ) => (
	<PanelBody initialOpen={ true }>
		<TextControl
			type="url"
			label={ __( 'Content URL', 'rank-math' ) }
			help={ __(
				'A URL pointing to the actual video media file.',
				'rank-math'
			) }
			value={ props.videoUrl }
			onChange={ props.updateUrl }
		/>

		<TextControl
			type="url"
			label={ __( 'Embed URL', 'rank-math' ) }
			help={ __(
				'A URL pointing to the embeddable player for the video.',
				'rank-math'
			) }
			value={ props.videoEmbedUrl }
			onChange={ props.updateEmbedUrl }
		/>

		<TextControl
			label={ __( 'Duration', 'rank-math' ) }
			help={ __(
				'ISO 8601 duration format. Example: 1H30M',
				'rank-math'
			) }
			value={ props.videoDuration }
			onChange={ props.updateDuration }
		/>

		<TextControl
			type="number"
			label={ __( 'Views', 'rank-math' ) }
			help={ __( 'Number of views.', 'rank-math' ) }
			value={ props.videoViews }
			onChange={ props.updateViews }
		/>
	</PanelBody>
)

export default compose(
	withSelect( ( select ) => {
		const data = select( 'rank-math' ).getRichSnippets()

		return {
			videoUrl: data.videoUrl,
			videoEmbedUrl: data.videoEmbedUrl,
			videoDuration: data.videoDuration,
			videoViews: data.videoViews,
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updateUrl( url ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'videoUrl',
					'video_url',
					url
				)
			},

			updateEmbedUrl( url ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'videoEmbedUrl',
					'video_embed_url',
					url
				)
			},

			updateDuration( duration ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'videoDuration',
					'video_duration',
					duration
				)
			},

			updateViews( views ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'videoViews',
					'video_views',
					views
				)
			},
		}
	} )
)( VideoSnippet )
