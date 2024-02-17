/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Fragment } from '@wordpress/element'
import { withDispatch, withSelect } from '@wordpress/data'
import { CheckboxControl, PanelBody } from '@wordpress/components'
import RankMathAfterFocusKeyword from '@slots/AfterFocusKeyword'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'
import ProNotice from './ProNotice'
import Tooltip from '@components/Tooltip'
import Interpolate from '@components/Interpolate'
import FocusKeywordField from './FocusKeywordField'
import TrendsPreview from '@helpers/TrendsPreview'
import isGutenbergAvailable from '@helpers/isGutenbergAvailable'
import ContentAI from './ContentAI'

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
									href={ getLink( 'score-100', 'General Focus Keyword' ) }
									target="_blank"
									rel="noopener noreferrer"
								>
								</a>
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

			<TrendsPreview></TrendsPreview>
			{ rankMath.currentEditor && ( 'classic' !== rankMath.currentEditor || isGutenbergAvailable() ) && <ContentAI></ContentAI> }
			<RankMathAfterFocusKeyword.Slot>
				{ ( fills ) => {
					if ( fills.length > 0 ) {
						return fills
					}

					return []
				} }
			</RankMathAfterFocusKeyword.Slot>

			<div>
				<FocusKeywordField />
			</div>

			<ProNotice />

			{
				'post' === rankMath.objectType && <CheckboxControl
					className="pillar-content"
					label={
						<Fragment>
							<strong>
								{ __( 'This post is Pillar Content', 'rank-math' ) }
							</strong>
							<a
								href={ getLink( 'pillar-content-internal-linking', 'Pillar Content' ) }
								rel="noreferrer"
								target="_blank"
								className="dashicons-before dashicons-editor-help rank-math-help-icon"
							>
							</a>
						</Fragment>
					}
					checked={ isPillarContent }
					onChange={ togglePillarContent }
				/>
			}
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
