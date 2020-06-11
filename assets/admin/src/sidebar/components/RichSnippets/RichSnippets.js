/**
 * External dependencies
 */
import { includes } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Fragment } from '@wordpress/element'
import { withSelect, withDispatch } from '@wordpress/data'
import {
	PanelBody,
	SelectControl,
	TextControl,
	TextareaControl,
} from '@wordpress/components'

/**
 * Internal dependencies
 */
import VariableInserter from '@components/VariableInserter'

/**
 * Internal dependencies
 */
import Interpolate from '@components/Interpolate'
import ArticleSnippet from './Article'
import BookSnippet from './Book'
import CourseSnippet from './Course'
import EventSnippet from './Event'
import JobPostingSnippet from './JobPosting'
import MusicSnippet from './Music'
import PersonSnippet from './Person'
import ProductSnippet from './Product'
import RecipeSnippet from './Recipe'
import RestaurantSnippet from './Restaurant'
import ServiceSnippet from './Service'
import SoftwareSnippet from './Software'
import VideoSnippet from './Video'
import decodeEntities from '@helpers/decodeEntities'

const canShowShortcode = ( { type, location } ) => {
	if (
		'custom' === location &&
		includes(
			[ 'book', 'course', 'event', 'product', 'recipe', 'software' ],
			type
		)
	) {
		return true
	}

	return false
}

const getRichSnippetTypes = () => {
	const types = [
		{ value: 'off', label: __( 'None', 'rank-math' ) },
		{ value: 'article', label: __( 'Article', 'rank-math' ) },
		{ value: 'book', label: __( 'Book', 'rank-math' ) },
		{ value: 'course', label: __( 'Course', 'rank-math' ) },
		{ value: 'event', label: __( 'Event', 'rank-math' ) },
		{ value: 'jobposting', label: __( 'Job Posting', 'rank-math' ) },
		{ value: 'music', label: __( 'Music', 'rank-math' ) },
		{ value: 'person', label: __( 'Person', 'rank-math' ) },
		{ value: 'product', label: __( 'Product', 'rank-math' ) },
		{ value: 'recipe', label: __( 'Recipe', 'rank-math' ) },
		{ value: 'restaurant', label: __( 'Restaurant', 'rank-math' ) },
		{ value: 'service', label: __( 'Service', 'rank-math' ) },
		{ value: 'software', label: __( 'Software Application', 'rank-math' ) },
		{ value: 'video', label: __( 'Video', 'rank-math' ) },
	]

	if ( 'product' === rankMath.postType ) {
		return [
			{ value: 'off', label: __( 'None', 'rank-math' ) },
			{ value: 'product', label: __( 'Product', 'rank-math' ) },
		]
	}

	if ( rankMath.hasReviewPosts ) {
		types.push( {
			value: 'review',
			label: __( 'Review (unsupported)', 'rank-math' ),
		} )
	}

	return types
}

const RichSnippet = ( props ) => {
	if ( 'product' === rankMath.postType ) {
		return (
			<Fragment>
				<PanelBody initialOpen={ true }>

					<SelectControl
						label={ __( 'Schema Type', 'rank-math' ) }
						help={ <Interpolate components={
							{ link: <a href={ rankMath.assessor.richSnippetsKBLink } target="_blank" rel="noopener noreferrer" /> }
						}>{ __( 'Schema help you stand out in SERPs. {{link}}Learn more{{/link}}.', 'rank-math' ) }
						</Interpolate> }
						className="rank-math-rich-snippet-type"
						value={ props.type }
						options={ getRichSnippetTypes() }
						onChange={ ( type ) => {
							props.updateSnippetType( type )
						} }
					/>
				</PanelBody>
			</Fragment>
		)
	}

	return (
		<Fragment>
			<PanelBody initialOpen={ true }>
				{ '' !== props.type && props.updateSnippetType( props.type ) }
				<SelectControl
					label={ __( 'Schema Type', 'rank-math' ) }
					help={ <Interpolate components={
						{ link: <a href={ rankMath.assessor.richSnippetsKBLink } target="_blank" rel="noopener noreferrer" /> }
					}>{ __( 'Schema help you stand out in SERPs. {{link}}Learn more{{/link}}.', 'rank-math' ) }
					</Interpolate> }
					className="rank-math-rich-snippet-type"
					value={ props.type }
					options={ getRichSnippetTypes() }
					onChange={ ( type ) => {
						props.updateSnippetType( type )
					} }
				/>

				{ 'review' === props.type && (
					<div className="components-base-control__help rank-math-notice notice notice-alt notice-warning">
						<p>
							<Interpolate components={ { link: <a href={ rankMath.assessor.reviewConverterLink } target="_blank" rel="noopener noreferrer" /> } }>
								{ __(
									'Google does not support this Schema type anymore, please use different type or use {{link}}this tool{{/link}} to convert all the old posts.',
									'rank-math'
								) }
							</Interpolate>
						</p>
					</div>
				) }

				{ true ===
					includes(
						[
							'book',
							'course',
							'event',
							'product',
							'recipe',
							'software',
						],
						props.type
					) && (
					<SelectControl
						label={ __( 'Review Location', 'rank-math' ) }
						help={ __(
							"The review or rating must be displayed on the page to comply with Google's Schema guidelines.",
							'rank-math'
						) }
						value={ props.location }
						options={ [
							{
								value: 'bottom',
								label: __( 'Below Content', 'rank-math' ),
							},
							{
								value: 'top',
								label: __( 'Above Content', 'rank-math' ),
							},
							{
								value: 'both',
								label: __(
									'Above & Below Content',
									'rank-math'
								),
							},
							{
								value: 'custom',
								label: __(
									'Custom (use shortcode)',
									'rank-math'
								),
							},
						] }
						onChange={ ( location ) => {
							props.updateSnippetLocation( location )
						} }
					/>
				) }

				{ canShowShortcode( props ) && (
					<TextControl
						label={ __( 'Shortcode', 'rank-math' ) }
						help={ __(
							'Copy & paste this shortcode in the content',
							'rank-math'
						) }
						value="[rank_math_rich_snippet]"
						readOnly="readonly"
					/>
				) }

				{ 'off' !== props.type && (
					<div className="variable-group">
						<TextControl
							label={ __( 'Headline', 'rank-math' ) }
							value={ decodeEntities( props.name ) }
							placeholder={ props.defaultName }
							onChange={ ( value ) => {
								props.updateSnippet( 'name', value )
							} }
						/>

						<VariableInserter
							onClick={ ( variable ) =>
								props.updateSnippet(
									'name',
									props.name + ' %' + variable.variable + '%'
								)
							}
						/>
					</div>
				) }

				{ false ===
					includes( [ 'off', 'book', 'local' ], props.type ) && (
					<div className="variable-group">
						<TextareaControl
							label={ __( 'Description', 'rank-math' ) }
							value={ decodeEntities( props.desc ) }
							placeholder={ props.defaultDescription }
							onChange={ ( value ) => {
								props.updateSnippet( 'desc', value )
							} }
						/>

						<VariableInserter
							onClick={ ( variable ) =>
								props.updateSnippet(
									'desc',
									props.desc + ' %' + variable.variable + '%'
								)
							}
						/>
					</div>
				) }

				{ true ===
					includes( [ 'book', 'local', 'music' ], props.type ) && (
					<TextControl
						label={ __( 'URL', 'rank-math' ) }
						value={ props.url }
						onChange={ ( value ) => {
							props.updateSnippet( 'url', value )
						} }
					/>
				) }

				{ 'book' === props.type && (
					<TextControl
						label={ __( 'Author', 'rank-math' ) }
						value={ props.author }
						onChange={ ( value ) => {
							props.updateSnippet( 'author', value )
						} }
					/>
				) }
			</PanelBody>

			{ 'article' === props.type && <ArticleSnippet /> }
			{ 'book' === props.type && <BookSnippet /> }
			{ 'course' === props.type && <CourseSnippet /> }
			{ 'event' === props.type && <EventSnippet /> }
			{ 'jobposting' === props.type && <JobPostingSnippet /> }
			{ 'music' === props.type && <MusicSnippet /> }
			{ 'person' === props.type && <PersonSnippet /> }
			{ 'product' === props.type && <ProductSnippet /> }
			{ 'recipe' === props.type && <RecipeSnippet /> }
			{ 'restaurant' === props.type && <RestaurantSnippet /> }
			{ 'service' === props.type && <ServiceSnippet /> }
			{ 'software' === props.type && <SoftwareSnippet /> }
			{ 'video' === props.type && <VideoSnippet /> }
		</Fragment>
	)
}

export default compose(
	withSelect( ( select ) => {
		const data = select( 'rank-math' ).getRichSnippets()

		return {
			type: data.snippetType,
			name: data.name,
			desc: data.desc,
			url: data.url,
			author: data.author,
			location: data.location,
			defaultName: data.defaultName,
			defaultDescription: data.defaultDescription,
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updateSnippet( key, value ) {
				dispatch( 'rank-math' ).updateRichSnippet( key, key, value )
			},

			updateSnippetType( type ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'snippetType',
					'rank_math_rich_snippet',
					type
				)
			},

			updateSnippetLocation( location ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'location',
					'location',
					location
				)
			},
		}
	} )
)( RichSnippet )
