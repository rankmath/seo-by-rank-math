/**
 * External dependencies
 */
import jQuery from 'jquery'

/**
 * WordPress dependencies
 */
import { select, dispatch } from '@wordpress/data'
import { addAction, addFilter } from '@wordpress/hooks'
import { createElement, render } from '@wordpress/element'

/**
 * Internal dependencies
 */
import './helpers'
import { getStore } from '@root/redux/store'
import registerDefaultHooks from './defaultFilters'
import Metabox from '@schema/Metabox/Metabox'
import TabIcon from '@schema/TabIcon'
import SchemaBuilder from '@schema/Builder/SchemaBuilder'

const textarea = jQuery( '#rank-math-schemas' )
const deleteSchema = jQuery( '#rank-math-schemas-delete' )

const onSave = () => {
	textarea.val( JSON.stringify( select( 'rank-math' ).getSchemas() ) )
}

jQuery( document ).ready( () => {
	getStore()
	registerDefaultHooks()

	const schemaID = Object.keys( rankMath.schemas )
	dispatch( 'rank-math' ).setEditingSchemaId( schemaID[ 0 ] )

	// Add Save Hook.
	addFilter( 'rank_math_schema_editor_tabs', 'rank-math', ( tabs ) => {
		tabs.schemaBuilder.view = () => <SchemaBuilder onSave={ onSave } />

		return tabs
	} )

	addAction( 'rank_math_schema_trash', 'rank-math', ( metaID ) => {
		const ids = JSON.parse( deleteSchema.val() )
		ids.push( metaID )
		deleteSchema.val( JSON.stringify( ids ) )
	} )

	// Render App.
	setTimeout( () => {
		const classicTabIcon = jQuery( '[href="#setting-panel-schema"] > span.dashicons-schema' )
		render(
			createElement( Metabox ),
			document.getElementById( 'rank-math-schema-generator' )
		)
		render(
			createElement( TabIcon ),
			classicTabIcon.get( 0 )
		)
	}, 1000 )
} )
