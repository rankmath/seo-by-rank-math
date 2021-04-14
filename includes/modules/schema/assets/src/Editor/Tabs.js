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
import SchemaBuilder from '../Builder/SchemaBuilder'
import CodeValidation from './code-validation/CodeValidation'

/**
 * Editor tabs.
 *
 * @return {Object} Editor tabs.
 */
const getTabs = () => {
	const tabs = {
		schemaBuilder: {
			name: 'schemaBuilder',
			title: (
				<Fragment>
					<span>{ __( 'Edit', 'rank-math' ) }</span>
				</Fragment>
			),
			view: SchemaBuilder,
			className: 'rank-math-tab-templates',
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
 *
 * @param {Object} props             The props data.
 * @param {string} props.selectedTab The selected tab name.
 */
const EditorTabPanel = ( { selectedTab } ) => {
	return (
		<TabPanel
			className="rank-math-tabs rank-math-editor rank-math-schema-tabs"
			activeClass="is-active"
			initialTabName={ selectedTab }
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
