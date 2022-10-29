/**
 * External dependencies
 */
import { truncate, isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Fragment } from '@wordpress/element'
import { withDispatch, withSelect } from '@wordpress/data'
import {
	SelectControl,
	ToggleControl,
	TextControl,
	TextareaControl,
} from '@wordpress/components'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'
import Preview from './Preview'
import TwitterApp from './TwitterApp'
import TwitterPlayer from './TwitterPlayer'
import { getOverlayChoices } from '@helpers/overlayImages'
import SocialMediaUpload from './MediaUpload'
import VariableInserter from '@components/VariableInserter'
import Interpolate from '@components/Interpolate'

const TwitterTab = ( props ) => (
	<Fragment>
		<Preview
			{ ...props }
			network="twitter"
			siteurl={ rankMath.parentDomain }
			classes={ props.cardType }
			description={ truncate(
				props.description ? props.description : props.serpDescription,
				{ length: 240, separator: ' ' }
			) }
		>
			<div className="social-name">
				{ rankMath.assessor.serpData.authorName }
				<span className="social-username">@{ props.author }</span>
				<span className="social-time">{ __( '2h', 'rank-math' ) }</span>
			</div>
			<div className="social-text">
				{ __(
					'The card for your website will look little something like this!',
					'rank-math'
				) }
			</div>
		</Preview>

		<div className="field-group">
			<ToggleControl
				label={ __( 'Use Data from Facebook Tab', 'rank-math' ) }
				checked={ props.useFacebook }
				onChange={ props.toggleUseFacebook }
			/>
		</div>

		<div className="field-group">
			<SelectControl
				value={ props.cardType }
				label={ __( 'Card Type', 'rank-math' ) }
				options={ [
					{
						value: 'summary_large_image',
						label: __(
							'Summary Card with Large Image',
							'rank-math'
						),
					},
					{
						value: 'summary_card',
						label: __( 'Summary Card', 'rank-math' ),
					},
					{
						value: 'app',
						label: __( 'App Card', 'rank-math' ),
					},
					{
						value: 'player',
						label: __( 'Player Card', 'rank-math' ),
					},
				] }
				onChange={ props.updateCardType }
			/>
		</div>

		{ 'player' === props.cardType && (
			<div className="notice notice-alt notice-info">
				<p>
					{
						__(
							'Video clips and audio streams have a special place on the Twitter platform thanks to the Player Card. Player Cards must be submitted for approval before they can be used. More information: ',
							'rank-math'
						)
					}
					<a href="https://dev.twitter.com/cards/types/player" target="blank">https://dev.twitter.com/cards/types/player</a>
				</p>
			</div>
		) }

		{ 'app' === props.cardType && (
			<div className="notice notice-alt notice-info">
				<p>
					{
						__(
							'The App Card is a great way to represent mobile applications on Twitter and to drive installs. More information: ',
							'rank-math'
						)
					}
					<a href="https://dev.twitter.com/cards/types/app" target="blank"> https://dev.twitter.com/cards/types/app</a>
				</p>
			</div>
		) }

		{ ! props.useFacebook && 'app' !== props.cardType && (
			<SocialMediaUpload { ...props } />
		) }

		{ ! props.useFacebook && 'app' !== props.cardType && (
			<div className="field-group">
				<label htmlFor="rank-math-twitter-title">
					{ __( 'Title', 'rank-math' ) }
				</label>

				<div className="variable-group">
					<TextControl
						id="rank-math-twitter-title"
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
		) }

		{ ! props.useFacebook && 'app' !== props.cardType && (
			<div className="field-group">
				<label htmlFor="rank-math-twitter-description">
					{ __( 'Description', 'rank-math' ) }
				</label>

				<div className="variable-group">
					<TextareaControl
						id="rank-math-twitter-description"
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
		) }

		{ ! props.useFacebook && 'app' !== props.cardType && (
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
												href={ getLink( 'pro', 'Gutenberg Social Tab' ) }
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
		) }

		{ 'player' === props.cardType && <TwitterPlayer /> }
		{ 'app' === props.cardType && <TwitterApp /> }
	</Fragment>
)

export default compose(
	withSelect( ( select ) => {
		const repo = select( 'rank-math' ),
			useFacebook = repo.getTwitterUseFacebook()

		const image = ( () => {
			if ( useFacebook && repo.getFacebookImage() ) {
				return repo.getFacebookImage()
			}

			if ( repo.getTwitterImage() ) {
				return repo.getTwitterImage()
			}

			const featured = repo.getFeaturedImage()
			return ! isUndefined( featured ) && '' !== featured
				? repo.getFeaturedImage().source_url
				: rankMath.defautOgImage
		} )()

		return {
			useFacebook,
			cardType: repo.getTwitterCardType(),
			title: useFacebook
				? repo.getFacebookTitle()
				: repo.getTwitterTitle(),
			description: useFacebook
				? repo.getFacebookDescription()
				: repo.getTwitterDescription(),
			serpTitle: repo.getSerpTitle(),
			serpDescription: repo.getSerpDescription(),
			author: repo.getTwitterAuthor(),
			image,
			imageID: useFacebook
				? repo.getFacebookImageID()
				: repo.getTwitterImageID(),
			hasOverlay: useFacebook
				? repo.getFacebookHasOverlay()
				: repo.getTwitterHasOverlay(),
			imageOverlay: useFacebook
				? repo.getFacebookImageOverlay()
				: repo.getTwitterImageOverlay(),
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			removeImage() {
				dispatch( 'rank-math' ).updateTwitterImage( '' )
				dispatch( 'rank-math' ).updateTwitterImageID( 0 )
				dispatch( 'rank-math' ).updateTwitterHasOverlay( false )
			},

			updateImage( attachment ) {
				dispatch( 'rank-math' ).updateTwitterImage( attachment.url )
				dispatch( 'rank-math' ).updateTwitterImageID( attachment.id )
			},

			updateTitle( title ) {
				dispatch( 'rank-math' ).updateTwitterTitle( title )
			},

			updateDescription( description ) {
				dispatch( 'rank-math' ).updateTwitterDescription( description )
			},

			updateImageOverlay( value ) {
				dispatch( 'rank-math' ).updateTwitterImageOverlay( value )
			},

			toggleUseFacebook( value ) {
				dispatch( 'rank-math' ).updateTwitterUseFacebook( value )
			},

			updateCardType( value ) {
				dispatch( 'rank-math' ).updateTwitterCardType( value )
			},

			toggleOverlay( value ) {
				dispatch( 'rank-math' ).updateTwitterHasOverlay( value )
			},
		}
	} )
)( TwitterTab )
