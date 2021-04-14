/**
 * External dependencies
 */
import { isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import Schema from '@schema/Schema'
import SchemaTabIcon from '@schema/TabIcon'

/**
 * Filter to add Schema Tab in the Sidebar.
 */
addFilter( 'rank_math_sidebar_tabs', 'rank-math', ( tabs ) => {
	if ( rankMath.canUser.snippet && ! isUndefined( rankMath.schemas ) ) {
		tabs.splice( 2, 0, {
			name: 'schema',
			title: <SchemaTabIcon />,
			view: Schema,
			className: 'rank-math-schema-tab',
		} )
	}

	return tabs
} )
