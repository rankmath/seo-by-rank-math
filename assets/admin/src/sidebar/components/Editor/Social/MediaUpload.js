/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment } from '@wordpress/element'
import { Button } from '@wordpress/components'
import { MediaUpload } from '@wordpress/block-editor'

const SocialMediaUpload = ( props ) => (
	<div className="components-base-control field-group">
		<MediaUpload
			allowedTypes={ [ 'image' ] }
			multiple={ false }
			value={ props.imageID }
			render={ ( { open } ) => (
				<Fragment>
					<Button
						onClick={ open }
						className="button"
						isPrimary
						isLarge
					>
						{ props.imageID > 0
							? __( 'Replace Image', 'rank-math' )
							: __( 'Add Image', 'rank-math' ) }
					</Button>

					{ props.imageID > 0 && (
						<Button
							className="button"
							isDestructive
							isLink
							onClick={ props.removeImage }
						>
							{ __( 'Remove Image', 'rank-math' ) }
						</Button>
					) }
				</Fragment>
			) }
			onSelect={ props.updateImage }
		/>

		<p className="components-base-control__help">
			{ __(
				'Upload at least 600x315px image. Recommended size is 1200x630px.',
				'rank-math'
			) }
		</p>

		<div className="notice notice-warning inline hidden">
			<p>
				{ __(
					'Image is smaller than the minimum size, please select a different image.',
					'rank-math'
				) }
			</p>
		</div>
	</div>
)

export default SocialMediaUpload
