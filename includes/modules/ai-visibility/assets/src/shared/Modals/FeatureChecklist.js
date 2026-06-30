/**
 * FeatureChecklist — green check-bullet benefit list shared by the
 * UpgradeToUnlockModal and ActivateTrialModal.
 *
 * @since 1.0.281
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import './FeatureChecklist.scss'

/**
 * Default benefit rows — copy matches the Figma designs verbatim.
 */
const DEFAULT_FEATURES = [
	__( 'Track how often AI mentions your brand', 'seo-by-rank-math' ),
	__( 'Monitor sentiment across AI search results', 'seo-by-rank-math' ),
	__( 'See which pages AI models cite as sources', 'seo-by-rank-math' ),
	__( 'Benchmark your AI visibility against competitors', 'seo-by-rank-math' ),
	__( 'Spot opportunities to grow your AI-driven traffic', 'seo-by-rank-math' ),
]

/**
 * FeatureChecklist component.
 *
 * @param {Object}   props
 * @param {string[]} [props.features] Feature labels. Defaults to the Figma list.
 * @return {JSX.Element} Unordered list with green check bullets.
 */
const FeatureChecklist = ( { features = DEFAULT_FEATURES } ) => {
	const ns = 'rank-math-ai-visibility-feature-checklist'

	return (
		<ul className={ ns }>
			{ features.map( ( feature ) => (
				<li key={ feature } className={ `${ ns }__item` }>
					<i className={ `${ ns }__icon dashicons dashicons-yes-alt` } aria-hidden="true" />
					<span className={ `${ ns }__label` }>{ feature }</span>
				</li>
			) ) }
		</ul>
	)
}

FeatureChecklist.displayName = 'FeatureChecklist'

export default FeatureChecklist
