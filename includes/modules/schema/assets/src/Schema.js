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
 * Schema component.
 */
const Schema = () => {
	return (
		<PanelBody initialOpen={ true } className="rank-math-schema-in-use">
			<p className="cmb2-metabox-description">{ __( 'Configure Schema Markup for your pages. Search engines, use structured data to display rich results in SERPs.', 'rank-math' ) } <a href="https://rankmath.com/kb/rich-snippets/?utm_source=Plugin&utm_medium=Schema%20G%20Tab&utm_campaign=WP" target="_blank" rel="noopener noreferrer">{ __( 'Learn more.', 'rank-math' ) }</a></p>
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
