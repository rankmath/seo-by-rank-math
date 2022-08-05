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
import VariableInserter from '@components/VariableInserter'
import Interpolate from '@components/Interpolate'

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
					href="https://rankmath.com/kb/meta-box-social-tab/?utm_source=Plugin&utm_medium=Gutenberg%20Social%20Tab&utm_campaign=WP"
					target="_blank"
					rel="noreferrer noopener"
				>
					{ __( 'Read more', 'rank-math' ) }
				</a>
			</p>
		</div>

		<SocialMediaUpload { ...props } />

		<div className="field-group">
			<label htmlFor="rank-math-facebook-title">
				{ __( 'Title', 'rank-math' ) }
			</label>
			<div className="variable-group">
				<TextControl
					id="rank-math-facebook-title"
					value={ props.title }
					placeholder={ props.serpTitle }
					onChange={ props.updateTitle }
				/>

				<VariableInserter
					onClick={ ( variable ) =>
						props.updateTitle( props.title + ' %' + variable.variable + '%' )
					}
				/>
			</div>
		</div>

		<div className="field-group">
			<label htmlFor="rank-math-facebook-description">
				{ __( 'Description', 'rank-math' ) }
			</label>

			<div className="variable-group">
				<TextareaControl
					id="rank-math-facebook-description"
					value={ props.description }
					placeholder={ props.serpDescription }
					onChange={ props.updateDescription }
				/>

				<VariableInserter
					onClick={ ( variable ) =>
						props.updateDescription( props.description + ' %' + variable.variable + '%' )
					}
				/>
			</div>
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

				{ ! rankMath.isPro && (
					<div className="notice notice-alt notice-warning">
						<p>
							<Interpolate
								components={ {
									link: (
										<a
											href="https://rankmath.com/pricing/?utm_source=Plugin&utm_medium=Gutenberg%20Social%20Tab&utm_campaign=WP"
											target="_blank"
											rel="noopener noreferrer"
										/>
									),
								} }>
								{ __(
									'You can add custom thumbnail overlays with {{link}}Rank Math Pro{{/link}}.',
									'rank-math'
								) }
							</Interpolate>
						</p>
					</div>
				) }
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
