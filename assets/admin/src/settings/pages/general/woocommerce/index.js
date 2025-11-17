/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'
import fields from './fields'

export default {
	name: 'woocommerce',
	header: {
		title: __( 'WooCommerce', 'rank-math' ),
		description: __(
			'Choose how you want Rank Math to handle your WooCommerce SEO.',
			'rank-math'
		),
		link: getLink( 'woocommerce-settings', 'Options Panel WooCommerce Tab' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-cart" />
			{ __( 'WooCommerce', 'rank-math' ) }
		</>
	),
	fields,
}
