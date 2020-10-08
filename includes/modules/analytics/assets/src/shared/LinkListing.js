/**
 * External dependencies
 */
import { defaults, map, uniqueId } from 'lodash'

/**
 * WordPress dependencies
 */
import { Dashicon } from '@wordpress/components'
import { __ } from '@wordpress/i18n'

const getIcon = ( type ) => {
	if ( 'internal' === type ) {
		return <Dashicon icon="admin-links" title={ __( 'Internal Links', 'rank-math' ) } />
	}

	if ( 'external' === type ) {
		return <Dashicon icon="external"  title={ __( 'External Links', 'rank-math' ) } />
	}

	if ( 'incoming' === type ) {
		return <Dashicon icon="external" title={ __( 'Incoming Links', 'rank-math' ) } />
	}
}

const LinkListing = ( { links } ) => {
	const data = defaults( links, {
		internal: 0,
		external: 0,
		incoming: 0,
	} )

	return (
		<div className="link-listing">
			{ map( data, ( count, type ) => {
				return (
					<div className="link-item" key={ uniqueId( 'links-' ) }>
						{ getIcon( type ) } { count }
					</div>
				)
			} ) }
		</div>
	)
}

export default LinkListing
