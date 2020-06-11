/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Fragment } from '@wordpress/element'
import { withDispatch, withSelect } from '@wordpress/data'
import { CheckboxControl, PanelBody, Notice } from '@wordpress/components'
import RankMathAfterFocusKeyword from '@slots/AfterFocusKeyword'

/**
 * Internal dependencies
 */
import Tooltip from '@components/Tooltip'
import Interpolate from '@components/Interpolate'
import FocusKeywordField from './FocusKeywordField'

const FocusKeyword = ( { isLoaded, isPillarContent, togglePillarContent } ) => {
	if ( ! isLoaded ) {
		return null
	}

	return (
		<PanelBody initialOpen={ true } className="rank-math-focus-keyword">
			<h2 className="components-panel__body-title">
				{ __( 'Focus Keyword', 'rank-math' ) }
				<Tooltip>
					<Interpolate
						components={ {
							link: (
								<a
									href={ rankMath.assessor.hundredScoreLink }
									target="_blank"
									rel="noopener noreferrer"
								/>
							),
						} }
					>
						{ __(
							'Insert keywords you want to rank for. Try to {{link}}attain 100/100 points{{/link}} for better chances of ranking.',
							'rank-math'
						) }
					</Interpolate>
				</Tooltip>
			</h2>

			<FocusKeywordField />

			{ false === rankMath.isUserRegistered && (
				<Notice status="warning" isDismissible={ false }>
					<Interpolate
						components={ {
							link: (
								<a
									href={ rankMath.assessor.registrationUrl }
									target="_blank"
									rel="noopener noreferrer"
								/>
							),
						} }
					>
						{ __(
							'Get keyword suggestions from Google by {{link}}connecting your Rank Math account{{/link}}.',
							'rank-math'
						) }
					</Interpolate>
				</Notice>
			) }

			<CheckboxControl
				className="pillar-content"
				label={
					<Fragment>
						<strong>
							{ __( 'This post is Pillar Content', 'rank-math' ) }
						</strong>
						<Tooltip>
							{ __(
								'Select one or more Pillar Content posts for each post tag or category to show them in the Link Suggestions meta box.',
								'rank-math'
							) }
						</Tooltip>
					</Fragment>
				}
				checked={ isPillarContent }
				onChange={ togglePillarContent }
			/>

			<RankMathAfterFocusKeyword.Slot>
				{ ( fills ) => {
					if ( fills.length > 0 ) {
						return fills
					}

					return []
				} }
			</RankMathAfterFocusKeyword.Slot>
		</PanelBody>
	)
}

export default compose(
	withSelect( ( select ) => {
		const repo = select( 'rank-math' )
		return {
			isLoaded: repo.isLoaded(),
			isPillarContent: repo.getPillarContent(),
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			togglePillarContent( value ) {
				dispatch( 'rank-math' ).updatePillarContent( value )
			},
		}
	} )
)( FocusKeyword )
