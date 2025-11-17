/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'
import Htaccess from './Htaccess'

export default {
	name: 'htaccess',
	header: {
		title: __( 'Edit .htaccess', 'rank-math' ),
		description: __(
			'Edit the contents of your .htaccess file easily.',
			'rank-math'
		),
		link: getLink( 'edit-htaccess', 'Options Panel htaccess Tab' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-htaccess" />
			{ __( 'Edit .htaccess', 'rank-math' ) }
		</>
	),
	fields: [
		{
			id: 'htaccess_accept_changes',
			type: 'component',
			Component: Htaccess,
			classes: 'field-type-notice',
		},
	],
}
