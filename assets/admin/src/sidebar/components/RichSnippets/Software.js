/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { withDispatch, withSelect } from '@wordpress/data'
import { PanelBody, TextControl } from '@wordpress/components'

const SoftwareSnippet = ( props ) => (
	<PanelBody initialOpen={ true }>
		<TextControl
			type="number"
			step="any"
			label={ __( 'Price', 'rank-math' ) }
			value={ props.softwarePrice }
			onChange={ props.updatePrice }
		/>

		<TextControl
			label={ __( 'Price Currency', 'rank-math' ) }
			help={ __( 'ISO 4217 Currency code. Example: EUR', 'rank-math' ) }
			value={ props.softwarePriceCurrency }
			onChange={ props.updatePriceCurrency }
		/>

		<TextControl
			label={ __( 'Operating System', 'rank-math' ) }
			help={ __(
				'For example, "Windows 7", "OSX 10.6", "Android 1.6"',
				'rank-math'
			) }
			value={ props.softwareOperatingSystem }
			onChange={ props.updateOperatingSystem }
		/>

		<TextControl
			label={ __( 'Application Category', 'rank-math' ) }
			help={ __( 'For example, "Game", "Multimedia"', 'rank-math' ) }
			value={ props.softwareApplicationCategory }
			onChange={ props.updateApplicationCategory }
		/>

		<TextControl
			type="number"
			label={ __( 'Rating', 'rank-math' ) }
			help={ __(
				'Rating score of the software. Optional.',
				'rank-math'
			) }
			autoComplete="off"
			step="any"
			value={ props.softwareRating }
			onChange={ props.updateRating }
		/>

		<TextControl
			type="number"
			label={ __( 'Rating Minimum', 'rank-math' ) }
			help={ __( 'Rating minimum score of the software.', 'rank-math' ) }
			autoComplete="off"
			value={ props.softwareRatingMin }
			onChange={ props.updateRatingMin }
		/>

		<TextControl
			type="number"
			label={ __( 'Rating Maximum', 'rank-math' ) }
			help={ __( 'Rating maximum score of the software.', 'rank-math' ) }
			autoComplete="off"
			value={ props.softwareRatingMax }
			onChange={ props.updateRatingMax }
		/>
	</PanelBody>
)

export default compose(
	withSelect( ( select ) => {
		const data = select( 'rank-math' ).getRichSnippets()

		return {
			softwarePrice: data.softwarePrice,
			softwarePriceCurrency: data.softwarePriceCurrency,
			softwareOperatingSystem: data.softwareOperatingSystem,
			softwareApplicationCategory: data.softwareApplicationCategory,
			softwareRating: data.softwareRating,
			softwareRatingMin: data.softwareRatingMin,
			softwareRatingMax: data.softwareRatingMax,
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updatePrice( price ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'softwarePrice',
					'software_price',
					price
				)
			},

			updatePriceCurrency( currency ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'softwarePriceCurrency',
					'software_price_currency',
					currency
				)
			},

			updateOperatingSystem( os ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'softwareOperatingSystem',
					'software_operating_system',
					os
				)
			},

			updateApplicationCategory( category ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'softwareApplicationCategory',
					'software_application_category',
					category
				)
			},

			updateRating( rating ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'softwareRating',
					'software_rating',
					rating
				)
			},

			updateRatingMin( rating ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'softwareRatingMin',
					'software_rating_min',
					rating
				)
			},

			updateRatingMax( rating ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'softwareRatingMax',
					'software_rating_max',
					rating
				)
			},
		}
	} )
)( SoftwareSnippet )
