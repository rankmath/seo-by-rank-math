/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Component } from '@wordpress/element'
import { PanelBody, TextControl } from '@wordpress/components'

class Address extends Component {
	static defaultProps = {
		label: '',
	}

	render() {
		const {
			streetAddress,
			addressLocality,
			addressRegion,
			postalCode,
			addressCountry,
		} = this.props.value

		return (
			<PanelBody
				title={ this.props.label }
				initialOpen={ this.props.initialOpen }
			>
				<TextControl
					label={ __( 'Street Address', 'rank-math' ) }
					value={ streetAddress }
					onChange={ ( value ) =>
						this.onChange( 'streetAddress', value )
					}
				/>

				<TextControl
					label={ __( 'Locality', 'rank-math' ) }
					value={ addressLocality }
					onChange={ ( value ) =>
						this.onChange( 'addressLocality', value )
					}
				/>

				<TextControl
					label={ __( 'Region', 'rank-math' ) }
					value={ addressRegion }
					onChange={ ( value ) =>
						this.onChange( 'addressRegion', value )
					}
				/>

				<TextControl
					label={ __( 'Postal Code', 'rank-math' ) }
					value={ postalCode }
					onChange={ ( value ) =>
						this.onChange( 'postalCode', value )
					}
				/>

				<TextControl
					label={ __( 'Country', 'rank-math' ) }
					value={ addressCountry }
					onChange={ ( value ) =>
						this.onChange( 'addressCountry', value )
					}
				/>
			</PanelBody>
		)
	}

	onChange( key, value ) {
		this.props.value[ key ] = value
		this.props.onChange( this.props.value )
		this.forceUpdate()
	}
}

export default Address
