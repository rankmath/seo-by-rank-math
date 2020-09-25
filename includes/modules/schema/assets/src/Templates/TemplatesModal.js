/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Dashicon, Modal } from '@wordpress/components'
import { withSelect, withDispatch } from '@wordpress/data'

/**
 * Internal dependencies
 */
import TabPanel from './Tabs'

const TemplatesModal = ( { isOpen = false, toggleModal } ) => {
	if ( ! isOpen ) {
		return null
	}

	return (
		<Modal
			title={ __( 'Schema Generator', 'rank-math' ) }
			closeButtonLabel={ __( 'Close', 'rank-math' ) }
			shouldCloseOnClickOutside={ false }
			onRequestClose={ toggleModal }
			className="rank-math-modal rank-math-schema-generator rank-math-schema-template-modal"
			overlayClassName="rank-math-modal-overlay"
		>
			<a
				href="https://rankmath.com/kb/rich-snippets/?utm_source=Plugin&utm_campaign=WP"
				rel="noopener noreferrer"
				target="_blank"
				title={ __( 'More Info', 'rank-math' ) }
				className={ 'rank-math-schema-info' }
			>
				<Dashicon icon="info" />
			</a>
			<TabPanel />
		</Modal>
	)
}

export default compose(
	withSelect( ( select ) => {
		return {
			isOpen: select( 'rank-math' ).isSchemaTemplatesOpen(),
		}
	} ),
	withDispatch( ( dispatch, props ) => {
		return {
			toggleModal() {
				dispatch( 'rank-math' ).toggleSchemaTemplates( ! props.isOpen )
			},
		}
	} )
)( TemplatesModal )
