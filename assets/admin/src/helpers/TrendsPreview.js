/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n'
import { Fragment, useState } from '@wordpress/element'
import { Modal, withFilters, Button } from '@wordpress/components'

/**
 * Internal dependencies.
 */

const TrendsPreview = () => {
	const [ isOpen, toggleModal ] = useState( false )

	return (
		<Fragment>
			<Button
				title={ __( 'Trends', 'rank-math' ) }
				rel="noreferrer noopener"
				id="rank-math-compare-keywords-trigger"
				className="button button-icon rank-math-compare-keywords-trigger"
				onClick={ () => {
					toggleModal( true )
				} }
			>
				<span className="button-icon" dangerouslySetInnerHTML={ { __html: rankMath.trendsIcon } }></span>
			</Button>
			{ isOpen && (
				<Modal
					title={ __( 'Google Trends', 'rank-math' ) }
					closeButtonLabel={ __( 'Close', 'rank-math' ) }
					shouldCloseOnClickOutside={ true }
					onRequestClose={ () => {
						toggleModal( false )
					} }
					className="rank-math-modal rank-math-trends-cta-modal"
					overlayClassName="rank-math-modal-overlay"
				>
					<div className="components-panel__body rank-math-trends-cta-wrapper">
						<img src={ rankMath.trendsPreviewImage } alt="" className="trends-cta blurred" />

						<div id="rank-math-pro-cta" className="center">
							<div className="rank-math-cta-box blue-ticks width-50">
								<h3>{ __( 'Track Keyword Trends', 'rank-math' ) }</h3>
								<ul>
									<li>{ __( 'Data fetched directly from Google', 'rank-math' ) }</li>
									<li>{ __( 'Analyze search trends and compare keywords', 'rank-math' ) }</li>
									<li>{ __( 'See data from a particular Country or timeframe', 'rank-math' ) }</li>
								</ul>
								<a className="button button-primary is-green" href={ rankMath.trendsUpgradeLink } rel="noreferrer noopener" target="_blank">{ rankMath.trendsUpgradeLabel }</a>
							</div>
						</div>
					</div>
				</Modal>
			) }
		</Fragment>
	)
}

export default withFilters( 'rankMath.focusKeywords.Trends' )( TrendsPreview )
