/**
 * GatedPreview — blurred decorative dashboard shown behind access-gate modals.
 * Uses static sample data; no API calls are made.
 *
 * @since 1.0.281
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { trendingUp } from '@wordpress/icons'

/**
 * Internal dependencies
 */
import { StatCard } from '../shared/components'
import BrandsTableToolbar from './BrandsTableToolbar'
import BrandsTable from './BrandsTable'
import './GatedPreview.scss'

const noop = () => {}

// Display-only sample data — intentionally untranslated.
const SAMPLE_BRANDS = [
	{ id: 'preview-1', name: 'Playstation 5', url: 'sony.com', locale: 'US', score: 94, rank: 1, avg_sentiment: 81, mentions: 4, citations: 2, last_analyzed: '2026-03-16', status: 'active' },
	{ id: 'preview-2', name: 'iPad', url: 'apple.com', locale: 'US', score: 94, rank: 1, avg_sentiment: 76, mentions: 9, citations: 16, last_analyzed: '2026-03-16', status: 'active' },
	{ id: 'preview-3', name: 'MiniCRM', url: 'https://minicrm.hu/', locale: 'HU', score: null, rank: null, avg_sentiment: null, mentions: null, citations: null, last_analyzed: '2026-03-16', status: 'active' },
	{ id: 'preview-4', name: 'BackWPup', url: 'https://backwpup.com', locale: '', score: null, rank: null, avg_sentiment: null, mentions: null, citations: null, last_analyzed: '2026-03-16', status: 'active' },
	{ id: 'preview-5', name: 'GTmetrix', url: 'https://gtmetrix.com', locale: '', score: null, rank: null, avg_sentiment: null, mentions: null, citations: null, last_analyzed: '2026-03-16', status: 'active' },
	{ id: 'preview-6', name: 'Rank Math', url: 'https://rankmath.com/', locale: '', score: 23, rank: 3, avg_sentiment: 40, mentions: 2, citations: 1, last_analyzed: '2026-03-16', status: 'inactive' },
	{ id: 'preview-7', name: 'WP Rocket', url: 'https://wp-rocket.me', locale: '', score: 23, rank: 3, avg_sentiment: 40, mentions: 2, citations: 1, last_analyzed: '2026-03-16', status: 'active' },
]

// Reuses the Dashboard BEM namespace so Dashboard.scss layout applies.

const GatedPreview = () => {
	const ns = 'rank-math-ai-visibility-dashboard'

	return (
		<div
			className={ `${ ns } rank-math-ai-visibility-gated-preview` }
			aria-hidden="true"
		>
			<div className={ `${ ns }__stats` }>
				<StatCard
					className="rank-math-ai-visibility-stat-card--active-brands"
					icon=" rm-icon rm-icon-ai-visibility"
					label={ __( 'Global AI Visibility Score', 'seo-by-rank-math' ) }
					value="70/100"
				/>
				<StatCard
					className="rank-math-ai-visibility-stat-card--analyses"
					icon={ trendingUp }
					label={ __( 'Analyses in last 24h', 'seo-by-rank-math' ) }
					value={ 0 }
				/>
				<StatCard
					className="rank-math-ai-visibility-stat-card--mentions"
					icon="rm-icon rm-icon-comments"
					label={ __( 'Avg mentions per brand', 'seo-by-rank-math' ) }
					value={ 3.2 }
				/>
			</div>

			<div className={ `${ ns }__table-card` }>
				<BrandsTableToolbar
					searchValue=""
					onSearchChange={ noop }
					onAdd={ noop }
				/>

				<BrandsTable
					brands={ SAMPLE_BRANDS }
					loading={ false }
					onView={ noop }
					onEdit={ noop }
					onDisable={ noop }
				/>
			</div>
		</div>
	)
}

GatedPreview.displayName = 'GatedPreview'

export default GatedPreview
