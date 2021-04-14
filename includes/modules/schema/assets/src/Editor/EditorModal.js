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
import { Dashicon, Modal, KeyboardShortcuts } from '@wordpress/components'
import { withSelect, withDispatch } from '@wordpress/data'
import { rawShortcut } from '@wordpress/keycodes'

/**
 * Internal dependencies
 */
import TabPanel from './Tabs'

/**
 * Schema editor modal popup component.
 *
 * @param {Object}     props               The props data.
 * @param {boolean}    props.isOpen        Whether the schema modal is open.
 * @param {function()} props.toggleModal   A callback to run when clicked on the close modal button.
 * @param {string}     props.selectedTab   The selected tab name.
 * @param {string}     props.isCutomSchema Current schema type.
 */
const EditorModal = ( { isOpen = false, toggleModal, selectedTab, isCutomSchema } ) => {
	if ( ! isOpen ) {
		return null
	}

	const containerClasses = classnames( 'rank-math-modal rank-math-schema-generator rank-math-schema-modal', {
		'rank-math-schema-modal-no-map': 'custom' === isCutomSchema,
	} )

	return (
		<Modal
			title={ __( 'Schema Builder', 'rank-math' ) }
			closeButtonLabel={ __( 'Close', 'rank-math' ) }
			shouldCloseOnClickOutside={ false }
			onRequestClose={ toggleModal }
			className={ containerClasses }
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
					href="https://rankmath.com/kb/rich-snippets/?utm_source=Plugin&utm_medium=Schema%20Builder%20Header&utm_campaign=WP"
					rel="noopener noreferrer"
					target="_blank"
					title={ __( 'More Info', 'rank-math' ) }
					className={ 'rank-math-schema-info' }
				>
					<Dashicon icon="info" />
				</a>
				<TabPanel selectedTab={ selectedTab } />
			</KeyboardShortcuts>
		</Modal>
	)
}

export default compose(
	withSelect( ( select ) => {
		const selected = select( 'rank-math' ).getEditingSchema()
		return {
			isOpen: select( 'rank-math' ).isSchemaEditorOpen(),
			selectedTab: select( 'rank-math' ).getEditorTab(),
			isCutomSchema: get( selected, 'data.metadata.type', false ),
		}
	} ),
	withDispatch( ( dispatch, props ) => {
		return {
			toggleModal() {
				dispatch( 'rank-math' ).setEditorTab( '' )
				dispatch( 'rank-math' ).toggleSchemaEditor( ! props.isOpen )
			},
		}
	} )
)( EditorModal )
