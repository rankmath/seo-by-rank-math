/**
 * External dependencies
 */
import { get, isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { withSelect } from '@wordpress/data'
import { Fragment } from '@wordpress/element'

/**
 * Internal dependencies
 */
import { getSnippetIcon } from '@helpers/snippetIcon'

/**
 * SchemaTab icon.
 *
 * @param {string} type The schema type.
 */
const SchemaTabIcon = ( { type } ) => {
	return (
		<Fragment>
			<i className={ getSnippetIcon( type ) }></i>
			<span>{ __( 'Schema', 'rank-math' ) }</span>
		</Fragment>
	)
}

export default withSelect( ( select ) => {
	const schemas = select( 'rank-math' ).getSchemas()
	const type = ( () => {
		if ( isEmpty( schemas ) ) {
			return 'off'
		}

		const schemaID = Object.keys( schemas )

		return get( schemas, [ schemaID[ 0 ], '@type' ] )
	} )()

	return { type }
} )( SchemaTabIcon )
