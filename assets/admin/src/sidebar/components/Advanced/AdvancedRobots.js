/**
 * External dependencies
 */
import { defaults, isBoolean } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment } from '@wordpress/element'
import { compose } from '@wordpress/compose'
import { withSelect, withDispatch } from '@wordpress/data'
import {
	BaseControl,
	CheckboxControl,
	SelectControl,
	TextControl,
} from '@wordpress/components'

/**
 * Internal dependencies
 */
import Tooltip from '@components/Tooltip'

const defaultMeta = {
	'max-snippet': -1,
	'max-video-preview': -1,
	'max-image-preview': 'large',
}

const Robots = ( props ) => (
	<BaseControl
		className="rank-math-robots"
		id="rank-math-robots"
		label={ __( 'Advanced Robots Meta', 'rank-math' ) }
	>
		<div className="rank-math-robots-list advanced-robots">
			<CheckboxControl
				label={
					<Fragment>
						{ __( 'Max Snippet', 'rank-math' ) }
						<Tooltip>
							{ __(
								'Specify a maximum text-length, in characters, of a snippet for your page',
								'rank-math'
							) }
						</Tooltip>
					</Fragment>
				}
				checked={ props.isSnippet }
				onChange={ ( value ) =>
					props.updateRobots( 'max-snippet', value )
				}
			/>

			<TextControl
				type="number"
				value={ props.maxSnippet }
				onChange={ ( value ) =>
					props.updateRobots( 'max-snippet', value )
				}
			/>

			<CheckboxControl
				label={
					<Fragment>
						{ __( 'Max Video Preview', 'rank-math' ) }
						<Tooltip>
							{ __(
								'Specify a maximum duration in seconds of an animated video preview',
								'rank-math'
							) }
						</Tooltip>
					</Fragment>
				}
				checked={ props.isVideo }
				onChange={ ( value ) =>
					props.updateRobots( 'max-video-preview', value )
				}
			/>

			<TextControl
				type="number"
				value={ props.maxVideo }
				onChange={ ( value ) =>
					props.updateRobots( 'max-video-preview', value )
				}
			/>

			<CheckboxControl
				label={
					<Fragment>
						{ __( 'Max Image Preview', 'rank-math' ) }
						<Tooltip>
							{ __(
								'Specify a maximum size of image preview to be shown for images on this page',
								'rank-math'
							) }
						</Tooltip>
					</Fragment>
				}
				checked={ props.isImage }
				onChange={ ( value ) =>
					props.updateRobots( 'max-image-preview', value )
				}
			/>

			<SelectControl
				value={ props.maxImage }
				onChange={ ( value ) =>
					props.updateRobots( 'max-image-preview', value )
				}
				options={ [
					{ value: 'large', label: __( 'Large', 'rank-math' ) },
					{ value: 'standard', label: __( 'Standard', 'rank-math' ) },
					{ value: 'none', label: __( 'None', 'rank-math' ) },
				] }
			/>
		</div>
	</BaseControl>
)

export default compose(
	withSelect( ( select ) => {
		const meta = select( 'rank-math' ).getAdvancedRobots()
		const values = { ...meta }
		defaults( values, defaultMeta )

		let isSnippet = null
		let isImage = null
		let isVideo = null

		if ( 'max-snippet' in meta ) {
			isSnippet = values[ 'max-snippet' ]
		}
		if ( 'max-image-preview' in meta ) {
			isImage = values[ 'max-image-preview' ]
		}
		if ( 'max-video-preview' in meta ) {
			isVideo = values[ 'max-video-preview' ]
		}

		return {
			meta,
			isSnippet,
			isImage,
			isVideo,
			maxSnippet: isSnippet ? values[ 'max-snippet' ] : -1,
			maxImage: values[ 'max-image-preview' ],
			maxVideo: isVideo ? values[ 'max-video-preview' ] : -1,
		}
	} ),
	withDispatch( ( dispatch, props ) => {
		const meta = { ...props.meta }

		return {
			updateRobots( key, value ) {
				if ( isBoolean( value ) ) {
					if ( false === value ) {
						delete meta[ key ]
					} else {
						meta[ key ] = defaultMeta[ key ]
					}
				} else {
					meta[ key ] = value
				}

				dispatch( 'rank-math' ).updateAdvancedRobots( meta )
			},
		}
	} )
)( Robots )
