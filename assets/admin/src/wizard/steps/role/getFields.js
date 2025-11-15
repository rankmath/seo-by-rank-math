/**
 * External Dependencies
 */
import { forEach, keys, map } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

export default ( data ) => {
	const { roles, capabilities } = data

	const caps = map( keys( capabilities ), ( cap ) => ( {
		id: cap,
		label: capabilities[ cap ],
	} ) )

	const fields = [
		{
			id: 'role_manager',
			type: 'toggle',
			name: __( 'Role Manager', 'rank-math' ),
			desc: __(
				"The Role Manager allows you to use WordPress roles to control which of your site users can have edit or view access to Rank Math's settings.",
				'rank-math'
			),
		},
	]

	forEach( keys( roles ), ( role ) => {
		const isAllChecked =
			data[ role ]?.length === keys( capabilities ).length

		fields.push( {
			options: caps,
			id: role,
			name: roles[ role ],
			type: 'multicheck_inline',
			toggleAll: true,
			dep: { role_manager: true },
			classes: `field-big-labels ${ isAllChecked ? 'multicheck-checked' : '' }`,
		} )
	} )

	return fields
}
