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
 */
const MetaboxModal = ( { isOpen = false } ) => {
	if ( ! isOpen ) {
		return null
	}

	return (
		<Modal
			title="Select Schema"
			closeButtonLabel={ __( 'Close', 'rank-math' ) }
			shouldCloseOnClickOutside={ false }
			className="rank-math-modal rank-math-schema-generator rank-math-schema-modal"
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
		return {
			isOpen: select( 'rank-math' ).isSchemaEditorOpen(),
		}
	} ),
)( MetaboxModal )
