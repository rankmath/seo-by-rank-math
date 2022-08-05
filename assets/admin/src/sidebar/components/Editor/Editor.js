/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { createElement, Fragment } from '@wordpress/element'
import { withDispatch, withSelect } from '@wordpress/data'
import { Button, Modal, TabPanel } from '@wordpress/components'

/**
 * Internal dependencies
 */
import General from './General/General'
import Social from './Social/Social'
import Review from './ReviewTab'
import ReportButton from './ReportButton'

const SnippetEditor = ( {
	isOpen,
	initialTab = '',
	toggleEditor,
} ) => {
	const tabs = [
		{
			name: 'general',
			title: (
				<Fragment>
					<i className="rm-icon rm-icon-settings"></i>
					<span>{ __( 'General', 'rank-math' ) }</span>
				</Fragment>
			),
			view: General,
		},
	]

	if ( rankMath.canUser.social ) {
		tabs.push( {
			name: 'social',
			title: (
				<Fragment>
					<i className="rm-icon rm-icon-social"></i>
					<span>{ __( 'Social', 'rank-math' ) }</span>
				</Fragment>
			),
			view: Social,
		} )
	}

	if ( rankMath.showReviewTab && false === rankMath.pluginReviewed ) {
		tabs.push( {
			name: 'review',
			className: 'review-tab',
			title: (
				<Fragment>
					<i className="rm-icon rm-icon-heart-filled"></i>
				</Fragment>
			),
			view: Review,
		} )
	}

	return (
		<Fragment>
			<Button
				isPrimary
				className="rank-math-edit-snippet"
				onClick={ toggleEditor }
			>
				{ __( 'Edit Snippet', 'rank-math' ) }
			</Button>
			{ initialTab === '' && <ReportButton /> }
			{ isOpen && (
				<Modal
					title={ __( 'Preview Snippet Editor', 'rank-math' ) }
					closeButtonLabel={ __( 'Close', 'rank-math' ) }
					shouldCloseOnClickOutside={ false }
					onRequestClose={ toggleEditor }
					className="rank-math-modal"
					overlayClassName="rank-math-modal-overlay"
				>
					<TabPanel
						className="rank-math-tabs rank-math-editor"
						activeClass="is-active"
						initialTabName={ initialTab }
						tabs={ tabs }
					>
						{ ( tab ) => createElement( tab.view ) }
					</TabPanel>
				</Modal>
			) }
		</Fragment>
	)
}

export default compose(
	withSelect( ( select ) => {
		return {
			isOpen: select( 'rank-math' ).isSnippetEditorOpen(),
		}
	} ),
	withDispatch( ( dispatch, props ) => {
		return {
			toggleEditor() {
				dispatch( 'rank-math' ).toggleSnippetEditor( ! props.isOpen )
			},
		}
	} )
)( SnippetEditor )
