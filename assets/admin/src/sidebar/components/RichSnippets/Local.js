/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Fragment } from '@wordpress/element'
import { withDispatch, withSelect } from '@wordpress/data'
import { PanelBody, TextControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import Address from '@components/Address'
import TimePicker from '@components/TimePicker'

const LocalSnippet = ( props ) => (
	<Fragment>
		<Address
			label={ __( 'Address', 'rank-math' ) }
			initialOpen={ true }
			value={ '' !== props.localAddress ? props.localAddress : {} }
			onChange={ props.updateAddress }
		/>

		<PanelBody initialOpen={ true }>
			<TextControl
				label={ __( 'Geo Coordinates', 'rank-math' ) }
				value={ props.localGeo }
				onChange={ props.updateGeo }
			/>

			<TextControl
				label={ __( 'Phone Number', 'rank-math' ) }
				value={ props.localPhone }
				onChange={ props.updatePhone }
			/>

			<TextControl
				label={ __( 'Price Range', 'rank-math' ) }
				value={ props.localPriceRange }
				onChange={ props.updatePriceRange }
			/>

			<TimePicker
				isTimestamp={ true }
				value={ props.localOpens }
				onChange={ props.updateOpens }
			>
				<TextControl
					autoComplete="off"
					label={ __( 'Opening Time', 'rank-math' ) }
					value={ props.localOpens }
					onChange={ props.updateOpens }
				/>
			</TimePicker>

			<TimePicker
				isTimestamp={ true }
				value={ props.localCloses }
				onChange={ props.updateCloses }
			>
				<TextControl
					autoComplete="off"
					label={ __( 'Closing Time', 'rank-math' ) }
					value={ props.localCloses }
					onChange={ props.updateCloses }
				/>
			</TimePicker>
		</PanelBody>
	</Fragment>
)

export default compose(
	withSelect( ( select ) => {
		const data = select( 'rank-math' ).getRichSnippets()

		return {
			localGeo: data.localGeo,
			localAddress: data.localAddress,
			localPhone: data.localPhone,
			localPriceRange: data.localPriceRange,
			localOpens: data.localOpens,
			localCloses: data.localCloses,
			localOpendays: data.localOpendays,
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updateGeo( geo ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'localGeo',
					'local_geo',
					geo
				)
			},

			updateAddress( address ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'localAddress',
					'local_address',
					address
				)
			},

			updatePhone( phone ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'localPhone',
					'local_phone',
					phone
				)
			},

			updatePriceRange( priceRange ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'localPriceRange',
					'local_price_range',
					priceRange
				)
			},

			updateOpens( opens ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'localOpens',
					'local_opens',
					opens
				)
			},

			updateCloses( closes ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'localCloses',
					'local_closes',
					closes
				)
			},

			updateOpendays( opendays ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'localOpendays',
					'local_opendays',
					opendays
				)
			},
		}
	} )
)( LocalSnippet )
