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
import TemplatesTab from './catalog/Templates'
import newSchema from './new-schema/newSchema'

const getTabs = () => {
	const tabs = {
		templates: {
			name: 'templates',
			title: (
				<Fragment>
					<i className="rm-icon rm-icon-schema"></i>
					<span>{ __( 'Schema Templates', 'rank-math' ) }</span>
				</Fragment>
			),
			view: TemplatesTab,
			className: 'rank-math-tab-templates',
		},
		newSchema: {
			name: 'new-schema',
			title: (
				<Fragment>
					<i className="rm-icon rm-icon-circle-plus"></i>
					<span>{ __( 'Custom Schema', 'rank-math' ) }</span>
				</Fragment>
			),
			view: newSchema,
			className: 'rank-math-tab-new-schema',
		},
	}

	return applyFilters( 'rank_math_schema_templates_tabs', tabs )
}

export default () => {
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
