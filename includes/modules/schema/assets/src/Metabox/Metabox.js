/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { dispatch } from '@wordpress/data'
import { Fragment } from '@wordpress/element'
import { Button } from '@wordpress/components'

/**
 * Internal dependencies
 */
import '@schema/Functions/cache'
import SchemaList from '@schema/SchemaList'
import EditorModal from '@schema/Editor/EditorModal'
import TemplatesModal from '@schema/Templates/TemplatesModal'

/**
 * Meabox component.
 */
const Metabox = () => {
	return (
		<Fragment>
			<p className="cmb2-metabox-description">{ __( 'Configure Schema Markup for your pages. Search engines, use structured data to display rich results in SERPs.', 'rank-math' ) } <a href="https://rankmath.com/kb/rich-snippets/?utm_source=Plugin&utm_medium=Schema%20G%20Tab&utm_campaign=WP" target="_blank" rel="noopener noreferrer">{ __( 'Learn more.', 'rank-math' ) }</a></p>
			<SchemaList />
			<Button
				className="button button-primary"
				onClick={ () =>
					dispatch( 'rank-math' ).toggleSchemaTemplates( true )
				}
			>
				{ __( 'Schema Generator', 'rank-math' ) }
			</Button>
			<EditorModal />
			<TemplatesModal />
		</Fragment>
	)
}

export default Metabox
