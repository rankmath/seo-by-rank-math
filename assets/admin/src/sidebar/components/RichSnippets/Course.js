/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { withDispatch, withSelect } from '@wordpress/data'
import { PanelBody, TextControl, SelectControl } from '@wordpress/components'

const CourseSnippet = ( props ) => (
	<PanelBody initialOpen={ true }>
		<SelectControl
			label={ __( 'Course Provider', 'rank-math' ) }
			value={ props.courseProviderType }
			options={ [
				{
					value: 'Organization',
					label: __( 'Organization', 'rank-math' ),
				},
				{
					value: 'Person',
					label: __( 'Person', 'rank-math' ),
				},
			] }
			onChange={ props.updateType }
		/>

		<TextControl
			label={ __( 'Course Provider Name', 'rank-math' ) }
			autoComplete="off"
			value={ props.courseProvider }
			onChange={ props.updateProviderName }
		/>

		<TextControl
			type="url"
			label={ __( 'Course Provider URL', 'rank-math' ) }
			autoComplete="off"
			value={ props.courseProviderUrl }
			onChange={ props.updateProviderUrl }
		/>

		<TextControl
			type="number"
			label={ __( 'Rating', 'rank-math' ) }
			help={ __( 'Rating score of the course. Optional.', 'rank-math' ) }
			autoComplete="off"
			step="any"
			value={ props.courseRating }
			onChange={ props.updateRating }
		/>

		<TextControl
			type="number"
			label={ __( 'Rating Minimum', 'rank-math' ) }
			help={ __( 'Rating minimum score of the course.', 'rank-math' ) }
			autoComplete="off"
			value={ props.courseRatingMin }
			onChange={ props.updateRatingMin }
		/>

		<TextControl
			type="number"
			label={ __( 'Rating Maximum', 'rank-math' ) }
			help={ __( 'Rating maximum score of the course.', 'rank-math' ) }
			autoComplete="off"
			value={ props.courseRatingMax }
			onChange={ props.updateRatingMax }
		/>
	</PanelBody>
)

export default compose(
	withSelect( ( select ) => {
		const data = select( 'rank-math' ).getRichSnippets()

		return {
			courseProviderType: data.courseProviderType,
			courseProvider: data.courseProvider,
			courseProviderUrl: data.courseProviderUrl,
			courseRating: data.courseRating,
			courseRatingMin: data.courseRatingMin,
			courseRatingMax: data.courseRatingMax,
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updateType( type ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'courseProviderType',
					'course_provider_type',
					type
				)
			},

			updateProviderName( name ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'courseProvider',
					'course_provider',
					name
				)
			},

			updateProviderUrl( url ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'courseProviderUrl',
					'course_provider_url',
					url
				)
			},

			updateRating( rating ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'courseRating',
					'course_rating',
					rating
				)
			},

			updateRatingMin( rating ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'courseRatingMin',
					'course_rating_min',
					rating
				)
			},

			updateRatingMax( rating ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'courseRatingMax',
					'course_rating_max',
					rating
				)
			},
		}
	} )
)( CourseSnippet )
