/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { withDispatch, withSelect } from '@wordpress/data'
import { PanelBody, TextControl } from '@wordpress/components'

const ServiceSnippet = ( props ) => (
	<PanelBody initialOpen={ true }>
		<TextControl
			label={ __( 'Service Type', 'rank-math' ) }
			help={ __(
				"The type of service being offered, e.g. veterans' benefits, emergency relief, etc.",
				'rank-math'
			) }
			value={ props.serviceType }
			onChange={ props.updateType }
		/>

		<TextControl
			label={ __( 'Price', 'rank-math' ) }
			onChange={ props.updatePrice }
			value={ props.servicePrice }
		/>

		<TextControl
			label={ __( 'Price Currency', 'rank-math' ) }
			help={ __( 'ISO 4217 Currency code. Example: EUR', 'rank-math' ) }
			value={ props.servicePriceCurrency }
			onChange={ props.updatePriceCurrency }
		/>
	</PanelBody>
)

export default compose(
	withSelect( ( select ) => {
		const data = select( 'rank-math' ).getRichSnippets()

		return {
			serviceType: data.serviceType,
			servicePrice: data.servicePrice,
			servicePriceCurrency: data.servicePriceCurrency,
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updateType( type ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'serviceType',
					'service_type',
					type
				)
			},

			updatePrice( price ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'servicePrice',
					'service_price',
					price
				)
			},

			updatePriceCurrency( currency ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'servicePriceCurrency',
					'service_price_currency',
					currency
				)
			},
		}
	} )
)( ServiceSnippet )
