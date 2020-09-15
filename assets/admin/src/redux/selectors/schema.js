/**
 * Get schema editor state.
 *
 * @param {Object} state The app state.
 *
 * @return {boolean} Return schema editor state.
 */
export function isSchemaEditorOpen( state ) {
	return state.appUi.isSchemaEditorOpen
}

/**
 * Get schema tempaltes state.
 *
 * @param {Object} state The app state.
 *
 * @return {boolean} Return schema tempaltes state.
 */
export function isSchemaTemplatesOpen( state ) {
	return state.appUi.isSchemaTemplatesOpen
}

/**
 * Get schemas.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return rich snippet data.
 */
export function getSchemas( state ) {
	return state.appData.schemas
}

/**
 * Get selected schema data.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return rich snippet data.
 */
export function getEditSchemas( state ) {
	return state.appUi.editSchemas
}

/**
 * Get selected schema data.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return rich snippet data.
 */
export function getEditingSchema( state ) {
	return {
		id: state.appUi.editingSchemaId,
		data: state.appUi.editSchemas[ state.appUi.editingSchemaId ],
	}
}

/**
 * Get preview schema.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return data.
 */
export function getPreviewSchema( state ) {
	return state.appData.schemas[ state.appUi.editingSchemaId ]
}

/**
 * Get editor tab name.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return rich snippet data.
 */
export function getEditorTab( state ) {
	return state.appUi.editorTab
}

/**
 * Get template tab name.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return rich snippet data.
 */
export function getTemplateTab( state ) {
	return state.appUi.templateTab
}
