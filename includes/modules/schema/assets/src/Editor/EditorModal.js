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
import { Dashicon, Modal } from '@wordpress/components'
import { withSelect, withDispatch } from '@wordpress/data'

/**
 * Internal dependencies
 */
import TabPanel from './Tabs'

/**
 * Schema editor modal popup component.
 *
 * @param {Object} props This component's props.
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
			title="Schema Generator"
			closeButtonLabel={ __( 'Close', 'rank-math' ) }
			shouldCloseOnClickOutside={ false }
			onRequestClose={ toggleModal }
			className={ containerClasses }
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
			<TabPanel selectedTab={ selectedTab } />
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
