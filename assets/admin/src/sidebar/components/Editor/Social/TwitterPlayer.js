/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Fragment } from '@wordpress/element'
import { TextControl } from '@wordpress/components'
import { withDispatch, withSelect } from '@wordpress/data'

const TwitterPlayer = ( props ) => (
	<Fragment>
		<TextControl
			value={ props.url }
			label={ __( 'Player URL', 'rank-math' ) }
			help={ __(
				'HTTPS URL to iFrame player. This must be a HTTPS URL which does not generate active mixed content warnings in a web browser. The audio or video player must not require plugins such as Adobe Flash.',
				'rank-math'
			) }
			onChange={ props.updatePlayerUrl }
		/>

		<TextControl
			value={ props.size }
			label={ __( 'Player Size', 'rank-math' ) }
			help={ __(
				'iFrame width and height, specified in pixels in the following format: 600x400.',
				'rank-math'
			) }
			onChange={ props.updatePlayerSize }
		/>

		<TextControl
			value={ props.stream }
			label={ __( 'Stream URL', 'rank-math' ) }
			help={ __(
				'Optional URL to raw stream that will be rendered in Twitter’s mobile applications directly. If provided, the stream must be delivered in the MPEG-4 container format (the .mp4 extension). The container can store a mix of audio and video with the following codecs: Video: H.264, Baseline Profile (BP), Level 3.0, up to 640 x 480 at 30 fps. Audio: AAC, Low Complexity Profile (LC).',
				'rank-math'
			) }
			onChange={ props.updatePlayerStreamUrl }
		/>

		<TextControl
			value={ props.ctype }
			label={ __( 'Stream Content Type', 'rank-math' ) }
			help={ __(
				'The MIME type/subtype combination that describes the content contained in twitter:player:stream. Takes the form specified in RFC 6381. Currently supported content_type values are those defined in RFC 4337 (MIME Type Registration for MP4).',
				'rank-math'
			) }
			onChange={ props.updatePlayerStreamCtype }
		/>
	</Fragment>
)

export default compose(
	withSelect( ( select ) => {
		const repo = select( 'rank-math' )

		return {
			url: repo.getTwitterPlayerUrl(),
			size: repo.getTwitterPlayerSize(),
			stream: repo.getTwitterPlayerStream(),
			ctype: repo.getTwitterPlayerStreamCtype(),
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updatePlayerUrl( value ) {
				dispatch( 'rank-math' ).updateTwitterPlayerUrl( value )
			},

			updatePlayerSize( value ) {
				dispatch( 'rank-math' ).updateTwitterPlayerSize( value )
			},

			updatePlayerStreamUrl( value ) {
				dispatch( 'rank-math' ).updateTwitterPlayerStreamUrl( value )
			},

			updatePlayerStreamCtype( value ) {
				dispatch( 'rank-math' ).updateTwitterPlayerStreamCtype( value )
			},
		}
	} )
)( TwitterPlayer )
