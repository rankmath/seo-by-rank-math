/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Modal } from '@wordpress/components'

const FocusKeywordCTA = ( { onClick } ) => (
	<Modal
		title={ __( 'Upgrade to re-order Focus Keywords', 'rank-math' ) }
		closeButtonLabel={ __( 'Close', 'rank-math' ) }
		shouldCloseOnClickOutside={ true }
		onRequestClose={ () => ( onClick() ) }
		className="rank-math-modal rank-math-focus-keywords-cta-modal"
		overlayClassName="rank-math-modal-overlay"
	>
		<div className="components-panel__body rank-math-focus-keywords-cta-wrapper">
			<div id="rank-math-pro-cta" className="center">
				<div className="rank-math-cta-box blue-ticks">
					<ul>
						<li>{ __( 'Improve the SEO workflow', 'rank-math' ) }</li>
						<li>{ __( 'Set different Primary Focus Keyword', 'rank-math' ) }</li>
						<li>{ __( 'and many other premium SEO features', 'rank-math' ) }</li>
					</ul>
					<a className="button button-primary is-green" href={ rankMath.trendsUpgradeLink } rel="noreferrer noopener" target="_blank">{ rankMath.trendsUpgradeLabel }</a>
				</div>
			</div>
		</div>
	</Modal>
)

export default FocusKeywordCTA
