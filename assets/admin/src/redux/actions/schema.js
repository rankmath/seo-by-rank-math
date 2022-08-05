/**
 * External dependencies
 */
import { get } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { select } from '@wordpress/data'
import { doAction } from '@wordpress/hooks'
import apiFetch from '@wordpress/api-fetch'

/**
 * Internal dependencies
 */
import { updateAppData, updateAppUi } from './metadata'

/**
 * Toggle schema editor state.
 *
 * @param {boolean} state The schema editor modal state.
 *
 * @return {Object} An action for redux.
 */
export function toggleSchemaEditor( state ) {
	return updateAppUi( 'isSchemaEditorOpen', state )
}

/**
 * Toggle schema templates state.
 *
 * @param {boolean} state The schema templates modal state.
 *
 * @return {Object} An action for redux.
 */
export function toggleSchemaTemplates( state ) {
	return updateAppUi( 'isSchemaTemplatesOpen', state )
}

/**
 * Toggle schema templates state.
 *
 * @param {boolean} state The schema templates modal state.
 *
 * @return {Object} An action for redux.
 */
export function schemaUpdated( state ) {
	return updateAppUi( 'schemaUpdated', state )
}

/**
 * Set current schema id.
 *
 * @param {string} id     Schema id.
 *
 * @return {Object} An action for redux.
 */
export function setEditingSchemaId( id ) {
	return updateAppUi( 'editingSchemaId', id )
}

/**
 * Set editor tab id.
 *
 * @param {string} id Tab id.
 *
 * @return {Object} An action for redux.
 */
export function setEditorTab( id ) {
	return updateAppUi( 'editorTab', id )
}

/**
 * Set editor tab id.
 *
 * @param {string} id Tab id.
 *
 * @return {Object} An action for redux.
 */
export function setTemplateTab( id ) {
	return updateAppUi( 'templateTab', id )
}

/**
 * Update edit schemas.
 *
 * @param {Object} schemas The schemas.
 *
 * @return {Object} An action for redux.
 */
export function updateEditSchemas( schemas ) {
	return updateAppUi( 'editSchemas', schemas )
}

/**
 * Update edit schemas.
 *
 * @param {Object} schemas The schemas.
 *
 * @return {Object} An action for redux.
 */
export function updateSchemas( schemas ) {
	return updateAppData( 'schemas', schemas )
}

/**
 * Update current schema.
 *
 * @param {string} id     Schema id.
 * @param {Object} schema The selected schema.
 *
 * @return {Object} An action for redux.
 */
export function updateEditSchema( id, schema ) {
	const schemas = select( 'rank-math' ).getEditSchemas()
	const newSchemas = { ...schemas }

	newSchemas[ id ] = schema

	return updateAppUi( 'editSchemas', newSchemas )
}

/**
 * Save schema.
 *
 * @param {string} id     The schema id.
 * @param {Object} schema The selected schema.
 *
 * @return {Object} An action for redux.
 */
export function saveSchema( id, schema ) {
	const schemas = select( 'rank-math' ).getSchemas()
	const newSchemas = { ...schemas }

	newSchemas[ id ] = schema

	return updateAppData( 'schemas', newSchemas )
}

/**
 * Delete schema.
 *
 * @param {number} index The index to delete.
 *
 * @return {Object} An action for redux.
 */
export function deleteSchema( index ) {
	const schemas = select( 'rank-math' ).getSchemas()
	const newSchemas = { ...schemas }
	delete newSchemas[ index ]

	doAction( 'rank_math_schema_trash', index )

	return updateAppData(
		'schemas',
		newSchemas,
		'rank_math_delete_' + index,
		''
	)
}

/**
 * Save template.
 *
 * @param {Object}   schema   The selected schema.
 * @param {Function} setState Set state.
 * @param {number}   postId   Post ID to update schema data.
 * @return {Object} An action for redux.
 */
export function saveTemplate( schema, setState, postId ) {
	apiFetch( {
		method: 'POST',
		path: 'rankmath/v1/saveTemplate',
		data: {
			schema,
			postId,
		},
	} ).then( ( response ) => {
		setState( { loading: false, showNotice: true, postId: response.id } )
		setTimeout( () => {
			setState( { showNotice: false } )

			if ( get( rankMath, 'isTemplateScreen', false ) ) {
				document.title = __( 'Edit Schema', 'rank-math' )
				window.history.pushState( null, '', response.link.replace( /&amp;/g, '&' ) )
			}
		}, 2000 )
		rankMath.schemaTemplates.push( {
			schema,
			title: schema.metadata.title,
			type: schema[ '@type' ],
		} )
	} )
	setState( { loading: true } )

	return { type: 'DONT_WANT_TO_DO_SOMETHING' }
}
