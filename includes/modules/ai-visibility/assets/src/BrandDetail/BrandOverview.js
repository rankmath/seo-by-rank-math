/**
 * BrandOverview — Brand detail Overview sub-tab.
 *
 * @since 1.0.273
 */

/**
 * External dependencies
 */
import { round } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { StatCard } from '../shared/components'
import './BrandOverview.scss'

/**
 * @param {Object}      props
 * @param {Object}      props.brand
 * @param {Object|null} props.insights
 * @param {boolean}     props.loading
 * @return {JSX.Element|null} Rendered component, or null when brand is falsy.
 */
const BrandOverview = ( { brand, insights = null, loading = false } ) => {
	if ( ! brand ) {
		return null
	}

	const placeholder = loading ? '…' : '—'
	const topCompetitor = insights?.competitors?.[ 0 ]?.name ?? null

	const ns = 'rank-math-ai-visibility-brand-overview'

	return (
		<div className={ ns }>

			<div className={ `${ ns }__stats` }>
				<StatCard
					className={ `${ ns }__stat-card ${ ns }__stat-card--score` }
					icon=" rm-icon-ai-visibility"
					label={ __( 'AI Visibility Score', 'seo-by-rank-math' ) }
					value={ insights?.score !== null && insights?.score !== undefined
						? `${ insights.score }/100`
						: placeholder
					}
					tooltip={ __( 'AI Visibility Score for this brand across AI platforms.', 'seo-by-rank-math' ) }
					analysis={ insights?.analysis }
				/>

				<StatCard
					className={ `${ ns }__stat-card ${ ns }__stat-card--mentions` }
					icon=" rm-icon-comments"
					label={ __( 'Recent Mentions', 'seo-by-rank-math' ) }
					value={ insights?.mentions ?? placeholder }
					tooltip={ __( 'Total brand mentions across AI-generated content.', 'seo-by-rank-math' ) }
					analysis={ insights?.analysis }
				/>

				<StatCard
					className={ `${ ns }__stat-card ${ ns }__stat-card--sentiment` }
					icon="smiley"
					label={ __( 'Avg Sentiments', 'seo-by-rank-math' ) }
					value={ insights?.avg_sentiment !== null && insights?.avg_sentiment !== undefined
						? `${ round( insights.avg_sentiment ) }%`
						: placeholder
					}
					tooltip={ __( 'Average sentiment score across all AI mentions of this brand.', 'seo-by-rank-math' ) }
					analysis={ insights?.analysis }
				/>

				<StatCard
					className={ `${ ns }__stat-card ${ ns }__stat-card--competitor` }
					icon="awards"
					label={ __( 'Top Competitor', 'seo-by-rank-math' ) }
					value={ topCompetitor || placeholder }
					tooltip={ __( 'The competitor brand with the highest share of voice in AI mentions.', 'seo-by-rank-math' ) }
					analysis={ insights?.analysis }
				/>
			</div>

		</div>
	)
}

export default BrandOverview
