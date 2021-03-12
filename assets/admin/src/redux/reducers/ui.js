/**
 * External dependencies
 */
import { get, isEmpty } from 'lodash'

const hasRedirection = rankMath.assessor.hasRedirection

const DEFAULT_STATE = {
	isLoaded: false,
	isPro: false,
	selectedKeyword: {
		tag: '',
		index: 0,
		data: { value: '' },
	},
	hasRedirect:
		hasRedirection &&
		( ! isEmpty( get( rankMath.assessor, 'redirection.id', '' ) ) ||
			! isEmpty( get( rankMath.assessor, 'redirection.url_to', '' ) ) ),

	// Serp Preview.
	serpTitle: '',
	serpDescription: '',
	isSnippetEditorOpen: false,
	snippetPreviewType: '',
	refreshResults: '',
	redirectionItem: {},

	// Schema.
	editorTab: '',
	templateTab: '',
	editSchemas: {},
	editingSchemaId: '',
	isSchemaEditorOpen: false,
	isSchemaTemplatesOpen: false,
	schemaUpdated: false,
}

/**
 * Reduces the dispatched action for the app ui state.
 *
 * @param {Object} state  The current state.
 * @param {Object} action The action that was just dispatched.
 *
 * @return {Object} The new state.
 */
export function appUi( state = DEFAULT_STATE, action ) {
	if ( 'RANK_MATH_APP_UI' === action.type ) {
		return {
			...state,
			[ action.key ]: action.value,
		}
	}

	return state
}
