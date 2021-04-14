/**
 * External dependencies
 */
import { get } from 'lodash'
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Modal } from '@wordpress/components'
import { withSelect } from '@wordpress/data'

/**
 * Internal dependencies
 */
import TabPanel from './Tabs'

/**
 * Meabox modal popup component.
 *
 * @param {Object}  props               The props data.
 * @param {boolean} props.isOpen        Whether the schema modal is open.
 * @param {string}  props.isCutomSchema Current schema type.
 */
const MetaboxModal = ( { isOpen = false, isCutomSchema } ) => {
	if ( ! isOpen ) {
		return null
	}

	const containerClasses = classnames( 'rank-math-modal rank-math-schema-generator rank-math-schema-modal', {
		'rank-math-schema-modal-no-map': 'custom' === isCutomSchema,
	} )

	return (
		<Modal
			title={ __( 'Select Schema', 'rank-math' ) }
			closeButtonLabel={ __( 'Close', 'rank-math' ) }
			shouldCloseOnClickOutside={ false }
			className={ containerClasses }
			onRequestClose={ () => {
				const { origin, pathname } = window.location
				window.location = origin + ( pathname
					.replace( 'post.php', 'edit.php?post_type=rank_math_schema' )
					.replace( 'post-new.php', 'edit.php?post_type=rank_math_schema' ) )
			} }
			overlayClassName="rank-math-modal-overlay"
		>
			<TabPanel />
		</Modal>
	)
}

export default compose(
	withSelect( ( select ) => {
		const selected = select( 'rank-math' ).getEditingSchema()
		return {
			isOpen: select( 'rank-math' ).isSchemaEditorOpen(),
			isCutomSchema: get( selected, 'data.metadata.type', false ),
		}
	} ),
)( MetaboxModal )
