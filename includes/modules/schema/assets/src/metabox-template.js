/**
 * External dependencies
 */
import jQuery from 'jquery'
import { get } from 'lodash'

/**
 * WordPress dependencies
 */
import { dispatch } from '@wordpress/data'
import { addAction, doAction } from '@wordpress/hooks'
import { createElement, render, Fragment } from '@wordpress/element'

/**
 * Internal dependencies
 */
import { getStore } from '@root/redux/store'
import registerDefaultHooks from './defaultFilters'
import MetaboxModal from '@schema/MetaboxTemplates/MetaboxModal'
import SelectionModal from '@schema/MetaboxTemplates/Selection'

jQuery( document ).on( 'ready', () => {
	const schemaID = Object.keys( rankMath.schemas )
	const metaID = jQuery( '.rank-math-schema-meta-id' )

	metaID.val( get( schemaID, 0, '' ).replace( 'schema-', '' ) )

	getStore()
	registerDefaultHooks()
	dispatch( 'rank-math' ).setVersion()
	dispatch( 'rank-math' ).setEditingSchemaId( schemaID[ 0 ] )

	let isOpen = true
	if ( '' !== metaID.val() && 'new-9999' !== metaID.val() ) {
		isOpen = false
		dispatch( 'rank-math' ).toggleSchemaEditor( true )
	}

	// Render App.
	addAction(
		'rank_math_loaded',
		'rank-math',
		() => {
			render(
				createElement( () => (
					<Fragment>
						<SelectionModal isOpen={ isOpen } />
						<MetaboxModal />
					</Fragment>
				) ),
				document.getElementById( 'rank-math-schema-template' )
			)
		}
	)

	doAction( 'rank_math_loaded' )
} )
