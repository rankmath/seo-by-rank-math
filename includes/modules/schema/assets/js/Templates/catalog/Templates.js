/**
 * External dependencies
 */
import { useState } from 'react'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment } from '@wordpress/element'
import { applyFilters } from '@wordpress/hooks'
import { RadioControl, TextControl } from '@wordpress/components'
import { compose } from '@wordpress/compose'
import { withSelect } from '@wordpress/data'

/**
 * Internal dependencies
 */
import { mapCache } from '@schema/functions'
import TemplatesCatalog from './TemplatesCatalog'
import SchemaList from '@schema/SchemaList'

/**
 * Get template sources.
 *
 * @return {Array} Template sources.
 */
const getTemplateSources = () => {
	return applyFilters( 'rank_math_schema_template_sources', [
		{ value: 'global', label: __( 'Schema Catalog', 'rank-math' ) },
	] )
}

/**
 * Get templates by source.
 *
 * @param  {string} source Source name.
 * @return {Array} Template by source.
 */
const getTemplateBySource = ( source ) => {
	if ( 'global' === source ) {
		return mapCache.getTemplates()
	}

	return applyFilters( 'rank_math_schema_templates_by_source', [], source )
}

/**
 * Template panel.
 *
 * @param {string} props Properties.
 */
const TemplatesPanel = ( props ) => {
	const [ current, toggleCatalog ] = useState( 'global' )
	const [ search, onChange ] = useState( '' )
	return (
		<Fragment>
			{ props.isPro && <SchemaList /> }
			<h4 className="rank-math-schema-section-title">{ __( 'Available Schema Types', 'rank-math' ) }</h4>
			<div className="rank-math-schema-filter">
				<RadioControl
					selected={ current }
					options={ getTemplateSources() }
					onChange={ toggleCatalog }
				/>
				<div className="rank-math-schema-search">
					<TextControl
						value={ search }
						onChange={ onChange }
						placeholder={ __( 'Search…', 'rank-math' ) }
					/>
				</div>
			</div>
			<TemplatesCatalog templates={ getTemplateBySource( current ) } search={ search.toLowerCase() } />
		</Fragment>
	)
}

export default compose(
	withSelect( ( select ) => {
		return {
			isPro: select( 'rank-math' ).isPro(),
		}
	} ),
)( TemplatesPanel )
