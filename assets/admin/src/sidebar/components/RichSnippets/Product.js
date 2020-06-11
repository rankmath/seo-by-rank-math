/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { withDispatch, withSelect } from '@wordpress/data'
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import DatePicker from '@components/DateTimePicker'

const ProductSnippet = ( props ) => (
	<PanelBody initialOpen={ true }>
		{ 'on' === props.productInstock && props.updateInstock( 'on' ) }

		<TextControl
			label={ __( 'Product SKU', 'rank-math' ) }
			value={ props.productSku }
			onChange={ props.updateSku }
		/>

		<TextControl
			label={ __( 'Product Brand', 'rank-math' ) }
			value={ props.productBrand }
			onChange={ props.updateBrand }
		/>

		<TextControl
			label={ __( 'Product Currency', 'rank-math' ) }
			help={ __( 'ISO 4217 Currency Code', 'rank-math' ) }
			value={ props.productCurrency }
			onChange={ props.updateCurrency }
		/>

		<TextControl
			type="number"
			step="any"
			label={ __( 'Product Price', 'rank-math' ) }
			value={ props.productPrice }
			onChange={ props.updatePrice }
		/>

		<DatePicker
			value={ props.productPriceValid }
			onChange={ props.updatePriceValid }
		>
			<TextControl
				autoComplete="off"
				label={ __( 'Price Valid Until', 'rank-math' ) }
				help={ __(
					'The date after which the price will no longer be available.',
					'rank-math'
				) }
				value={ props.productPriceValid }
				onChange={ props.updatePriceValid }
			/>
		</DatePicker>

		<ToggleControl
			label={ __( 'Product In-Stock', 'rank-math' ) }
			checked={ props.productInstock }
			onChange={ props.updateInstock }
		/>

		<TextControl
			type="number"
			label={ __( 'Rating', 'rank-math' ) }
			help={ __( 'Rating score of the product. Optional.', 'rank-math' ) }
			autoComplete="off"
			step="any"
			value={ props.productRating }
			onChange={ props.updateRating }
		/>

		<TextControl
			type="number"
			label={ __( 'Rating Minimum', 'rank-math' ) }
			help={ __( 'Rating minimum score of the product.', 'rank-math' ) }
			autoComplete="off"
			value={ props.productRatingMin }
			onChange={ props.updateRatingMin }
		/>

		<TextControl
			type="number"
			label={ __( 'Rating Maximum', 'rank-math' ) }
			help={ __( 'Rating maximum score of the product.', 'rank-math' ) }
			autoComplete="off"
			value={ props.productRatingMax }
			onChange={ props.updateRatingMax }
		/>
	</PanelBody>
)

export default compose(
	withSelect( ( select ) => {
		const data = select( 'rank-math' ).getRichSnippets()

		return {
			productSku: data.productSku,
			productBrand: data.productBrand,
			productCurrency: data.productCurrency,
			productPrice: data.productPrice,
			productPriceValid: data.productPriceValid,
			productInstock: data.productInstock,
			productRating: data.productRating,
			productRatingMin: data.productRatingMin,
			productRatingMax: data.productRatingMax,
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updateSku( sku ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'productSku',
					'product_sku',
					sku
				)
			},

			updateBrand( brand ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'productBrand',
					'product_brand',
					brand
				)
			},

			updateCurrency( currency ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'productCurrency',
					'product_currency',
					currency
				)
			},

			updatePrice( price ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'productPrice',
					'product_price',
					price
				)
			},

			updatePriceValid( isValid ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'productPriceValid',
					'product_price_valid',
					isValid
				)
			},

			updateInstock( inStock ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'productInstock',
					'product_instock',
					inStock
				)
			},

			updateRating( rating ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'productRating',
					'product_rating',
					rating
				)
			},

			updateRatingMin( rating ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'productRatingMin',
					'product_rating_min',
					rating
				)
			},

			updateRatingMax( rating ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'productRatingMax',
					'product_rating_max',
					rating
				)
			},
		}
	} )
)( ProductSnippet )
