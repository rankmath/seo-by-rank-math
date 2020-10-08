/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { dispatch } from '@wordpress/data'
import { Button, PanelBody } from '@wordpress/components'

/**
 * Internal dependencies
 */
import './helpers'
import '@schema/Functions/cache'
import SchemaList from './SchemaList'
import registerDefaultHooks from './defaultFilters'
import EditorModal from './Editor/EditorModal'
import TemplatesModal from './Templates/TemplatesModal'

registerDefaultHooks()

/**
 * Main Schema component.
 */
const Schema = () => {
	return (
		<PanelBody initialOpen={ true } className="rank-math-schema-in-use">
			<p className="cmb2-metabox-description">{ __( 'Configure Schema with a lot of precision. Search Engines parse this information and use it to display rich results.', 'rank-math' ) }</p>
			<SchemaList />
			<Button
				isPrimary
				onClick={ () =>
					dispatch( 'rank-math' ).toggleSchemaTemplates( true )
				}
			>
				{ __( 'Schema Generator', 'rank-math' ) }
			</Button>
			<EditorModal />
			<TemplatesModal />
		</PanelBody>
	)
}

export default Schema
