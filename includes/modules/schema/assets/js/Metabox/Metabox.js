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
			<p className="cmb2-metabox-description">{ __( 'Configure Schema with a lot of precision. Search Engines parse this information and use it to display rich results.', 'rank-math' ) }</p>
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
