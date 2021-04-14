/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Dashicon, Modal, KeyboardShortcuts } from '@wordpress/components'
import { withSelect, withDispatch } from '@wordpress/data'
import { rawShortcut } from '@wordpress/keycodes'

/**
 * Internal dependencies
 */
import TabPanel from './Tabs'

/**
 * Show the lists of schemas used in the Current Post/Term.
 *
 * @param {Object}     props             The props data.
 * @param {boolean}    props.isOpen      Whether the schema modal is open.
 * @param {function()} props.toggleModal A callback to run when clicked on the close modal button.
 */
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
			<KeyboardShortcuts
				shortcuts={ {
					[ rawShortcut.ctrl( 'z' ) ]: ( e ) => e.stopImmediatePropagation(),
					[ rawShortcut.ctrlShift( 'z' ) ]: ( e ) => e.stopImmediatePropagation(),
					[ rawShortcut.primary( 'z' ) ]: ( e ) => e.stopImmediatePropagation(),
					[ rawShortcut.primaryShift( 'z' ) ]: ( e ) => e.stopImmediatePropagation(),
				} }
			>
				<a
					href="https://rankmath.com/kb/rich-snippets/?utm_source=Plugin&utm_medium=Schema%20Generator%20Header&utm_campaign=WP"
					rel="noopener noreferrer"
					target="_blank"
					title={ __( 'More Info', 'rank-math' ) }
					className={ 'rank-math-schema-info' }
				>
					<Dashicon icon="info" />
				</a>
				<TabPanel />
			</KeyboardShortcuts>
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
