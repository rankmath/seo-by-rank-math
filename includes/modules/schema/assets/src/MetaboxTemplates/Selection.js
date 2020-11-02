/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Modal, Dashicon } from '@wordpress/components'
import { withSelect } from '@wordpress/data'

/**
 * Internal dependencies
 */
import TabPanel from '@schema/Templates/Tabs'

/**
 * Selection form template component.
 */
const Selection = ( { isOpen } ) => {
	if ( ! isOpen ) {
		return null
	}

	return (
		<Modal
			title={ __( 'Schema Generator', 'rank-math' ) }
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
		</Modal>
	)
}

export default compose(
	withSelect( ( select, props ) => {
		return {
			...props,
		}
	} )
)( Selection )
