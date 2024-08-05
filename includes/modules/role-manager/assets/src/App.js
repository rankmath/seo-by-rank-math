// eslint-disable-next-line eslint-comments/disable-enable-pair
/* eslint-disable import/no-unresolved */

/**
 * External dependencies
 */
import { map, keys } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'
import TabContent from '@rank-math-settings/components/TabContent'
import './role-manager.scss'

// Role Manager fields.
const getFields = () => {
	const { roles, capabilities } = rankMath
	const capabilityOptions = map( keys( capabilities ), ( cap ) => ( {
		id: cap,
		label: capabilities[ cap ],
	} ) )

	return map( keys( roles ), ( role ) => {
		const roleOptions = map( capabilityOptions, ( option ) => ( {
			...option,
			...( role === 'administrator' && option.id === 'rank_math_role_manager'
				? { disabled: true }
				: {} ),
		} ) )

		return {
			id: role,
			name: roles[ role ],
			options: roleOptions,
			type: 'multicheck_inline',
			classes: 'field-big-labels',
			toggleAll: {
				size: 'small',
				className: 'toggle-all-capabilities',
				children: __( 'Toggle All', 'rank-math' ),
			},
		}
	} )
}

// Role Manager page content.
export default () => (
	<div className="wrap rank-math-wrap">
		<div className="rank-math-box container">
			<span className="wp-header-end"></span>

			<TabContent
				type="roleCapabilities"
				fields={ getFields() }
				header={ {
					title: __( 'Role Manager', 'rank-math' ),
					link: getLink( 'role-manager', 'Role Manager Page' ),
					description: __( 'Control which user has access to which options of Rank Math.', 'rank-math' ),
				} }
				footer={ {
					discardButton: {
						children: __( 'Reset', 'rank-math' ),
					},
					applyButton: {
						children: __( 'Update Capabilities', 'rank-math' ),
					},
				} }
			/>
		</div>
	</div>
)
