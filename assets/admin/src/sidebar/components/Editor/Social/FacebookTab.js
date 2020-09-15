/**
 * External dependencies
 */
import { isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Fragment } from '@wordpress/element'
import { withDispatch, withSelect } from '@wordpress/data'
import {
	Dashicon,
	SelectControl,
	ToggleControl,
	TextControl,
	TextareaControl,
} from '@wordpress/components'

/**
 * Internal dependencies
 */
import Preview from './Preview'
import SocialMediaUpload from './MediaUpload'
import { getOverlayChoices } from '@helpers/overlayImages'

const FacebookTab = ( props ) => (
	<Fragment>
		<Preview
			{ ...props }
			network="facebook"
			siteurl={ rankMath.parentDomain }
		>
			<div className="social-name">
				{ rankMath.assessor.serpData.authorName }
			</div>
			<div className="social-time">
				<span>{ __( '2 hrs', 'rank-math' ) }</span>
				<span>
					<Dashicon icon="admin-site" size="12" />
				</span>
			</div>
		</Preview>

		<div className="notice notice-alt notice-info components-base-control">
			<p>
				{ __(
					'Customize the title, description and images of your post used while sharing on Facebook and Twitter.',
					'rank-math'
				) }{ ' ' }
				<a
					href="https://rankmath.com/kb/meta-box-social-tab/"
					target="_blank"
					rel="noreferrer noopener"
				>
					{ __( 'Read more', 'rank-math' ) }
				</a>
			</p>
		</div>

		<SocialMediaUpload { ...props } />

		<div className="field-group">
			<TextControl
				label={ __( 'Title', 'rank-math' ) }
				value={ props.title }
				placeholder={ props.serpTitle }
				onChange={ props.updateTitle }
			/>
		</div>

		<div className="field-group">
			<TextareaControl
				label={ __( 'Description', 'rank-math' ) }
				value={ props.description }
				placeholder={ props.serpDescription }
				onChange={ props.updateDescription }
			/>
		</div>

		<div className="field-group">
			<ToggleControl
				label={ __( 'Add icon overlay to thumbnail', 'rank-math' ) }
				checked={ props.hasOverlay }
				onChange={ props.toggleOverlay }
			/>

			<div
				className={
					props.hasOverlay ? 'components-base-control' : 'hidden'
				}
			>
				<SelectControl
					value={ props.imageOverlay }
					label={ __( 'Icon overlay', 'rank-math' ) }
					options={ getOverlayChoices() }
					onChange={ props.updateImageOverlay }
				/>

				<div className="notice notice-alt notice-warning">
					<p>
						{ __(
							'Please be careful with this option. Although this option will help increase CTR on Facebook, it might get you penalised if over-used.',
							'rank-math'
						) }
					</p>
				</div>
			</div>
		</div>
	</Fragment>
)

export default compose(
	withSelect( ( select ) => {
		const repo = select( 'rank-math' )
		const image = ( () => {
			if ( repo.getFacebookImage() ) {
				return repo.getFacebookImage()
			}

			const featured = repo.getFeaturedImage()
			return ! isUndefined( featured ) && '' !== featured
				? repo.getFeaturedImage().source_url
				: rankMath.defautOgImage
		} )()

		return {
			title: repo.getFacebookTitle(),
			description: repo.getFacebookDescription(),
			serpTitle: repo.getSerpTitle(),
			serpDescription: repo.getSerpDescription(),
			author: repo.getFacebookAuthor(),
			image,
			imageID: repo.getFacebookImageID(),
			hasOverlay: repo.getFacebookHasOverlay(),
			imageOverlay: repo.getFacebookImageOverlay(),
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			removeImage() {
				dispatch( 'rank-math' ).updateFacebookImage( '' )
				dispatch( 'rank-math' ).updateFacebookImageID( 0 )
				dispatch( 'rank-math' ).updateFacebookHasOverlay( false )
			},

			updateImage( attachment ) {
				dispatch( 'rank-math' ).updateFacebookImage( attachment.url )
				dispatch( 'rank-math' ).updateFacebookImageID( attachment.id )
			},

			updateTitle( title ) {
				dispatch( 'rank-math' ).updateFacebookTitle( title )
			},

			updateDescription( description ) {
				dispatch( 'rank-math' ).updateFacebookDescription( description )
			},

			updateImageOverlay( value ) {
				dispatch( 'rank-math' ).updateFacebookImageOverlay( value )
			},

			toggleOverlay( value ) {
				dispatch( 'rank-math' ).updateFacebookHasOverlay( value )
			},
		}
	} )
)( FacebookTab )
