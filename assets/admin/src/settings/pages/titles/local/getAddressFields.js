/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Local SEO address fields
 */
export default () => {
	return [
		{
			id: 'streetAddress',
			type: 'text',
			placeholder: __( 'Street Address', 'rank-math' ),
		},
		{
			id: 'addressLocality',
			type: 'text',
			placeholder: __( 'Locality', 'rank-math' ),
		},
		{
			id: 'addressRegion',
			type: 'text',
			placeholder: __( 'Region', 'rank-math' ),
		},
		{
			id: 'postalCode',
			type: 'text',
			placeholder: __( 'Postal Code', 'rank-math' ),
		},
		{
			id: 'addressCountry',
			type: 'text',
			placeholder: __( '2-letter Country Code (ISO 3166-1)', 'rank-math' ),
		},
	]
}
