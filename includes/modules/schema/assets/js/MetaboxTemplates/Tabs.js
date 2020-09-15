/**
 * External dependencies
 */
import jQuery from 'jquery'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'
import { TabPanel } from '@wordpress/components'
import { createElement, Fragment } from '@wordpress/element'

/**
 * Internal dependencies
 */
import SchemaBuilder from '@schema/Builder/SchemaBuilder'
import CodeValidation from '@schema/Editor/code-validation/CodeValidation'

const form = jQuery( '#post' )
const textarea = jQuery( '.rank-math-schema' )

/**
 * On Save callback.
 *
 * @param  {string} id     Form id.
 * @param  {Object} schema Schema data.
 */
const onSave = ( id, schema ) => {
	textarea.val( JSON.stringify( schema ) )
	setTimeout( () => {
		form.submit()
	}, 1000 )
}

/**
 * Get Tabs.
 *
 * @return {Array} Array for Metabox.
 */
const getTabs = () => {
	const tabs = {
		builder: {
			name: 'builder',
			title: (
				<Fragment>
					<span>{ __( 'Edit', 'rank-math' ) }</span>
				</Fragment>
			),
			view: () => {
				return <SchemaBuilder onSave={ onSave } />
			},
			className: 'rank-math-tab-builder',
		},
		codeValidation: {
			name: 'codeValidation',
			title: (
				<Fragment>
					<span>{ __( 'Code Validation', 'rank-math' ) }</span>
				</Fragment>
			),
			view: CodeValidation,
			className: 'rank-math-tab-code-validation',
		},
	}

	return applyFilters( 'rank_math_schema_editor_tabs', tabs )
}

/**
 * Editor tab panel component.
 */
const EditorTabPanel = () => {
	return (
		<TabPanel
			className="rank-math-tabs rank-math-editor rank-math-schema-tabs"
			activeClass="is-active"
			tabs={ Object.values( getTabs() ) }
		>
			{ ( tab ) => {
				return (
					<div
						className={ 'components-panel__body rank-math-schema-tab-content-' + tab.name }
					>
						{ createElement( tab.view ) }
					</div>
				)
			} }
		</TabPanel>
	)
}

export default EditorTabPanel
