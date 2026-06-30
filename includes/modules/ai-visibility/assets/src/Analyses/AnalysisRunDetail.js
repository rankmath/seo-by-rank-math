/**
 * AnalysisRunDetail — full-page run detail view.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { Notice, Tooltip } from '@wordpress/components'

/**
 * Internal dependencies
 */
import { TableSkeleton } from '@rank-math/components'
import { getInsights } from '../shared/services/api/aiVisibilityApi'
import { Button, SentimentBadge, TranscriptModal, EmptyState, StatCard } from '../shared/components'
import PageTopbar from '../shared/components/PageTopbar'
import useFetch from '../shared/hooks/useFetch'
import { formatShortDate } from '../utils/formatDate'
import { navigateToReportsTab } from '../utils/urlState'
import './AnalysisRunDetail.scss'

/**
 * Map an insights query result to the shape TranscriptModal expects.
 *
 * @param {Object} result   Query result from the insights payload.
 * @param {number} index    Fallback index.
 * @param {Object} analysis Parent analysis meta.
 * @return {Object} Normalised entry.
 */
const normalise = ( result, index, analysis ) => ( {
	...result,
	id: result.query_id ?? index,
	query: result.query_text ?? '',
	response: result.response ?? '',
	created_at: analysis?.finished_at ?? null,
	duration_seconds: analysis?.duration_seconds ?? null,
	model: null,
} )

/**
 * AnalysisRunDetail component.
 *
 * @param {Object}   props
 * @param {Object}   props.row    The analyses table row (brand_name, started_at, id).
 * @param {Function} props.onBack Called when ← Back is clicked.
 * @return {JSX.Element} Full-page analysis run detail.
 */
const AnalysisRunDetail = ( { row, onBack } ) => {
	const ns = 'rank-math-ai-visibility-analysis-run-detail'

	const { data: insightsData, loading, error } = useFetch(
		() => getInsights( row.id ),
		[ row?.id ],
		{ skip: ! row?.id, errorMessage: __( 'Failed to load run detail.', 'seo-by-rank-math' ) }
	)
	const insights = insightsData?.insights ?? null
	const [ modalEntry, setModalEntry ] = useState( null )

	const transcripts = ( insights?.query_results ?? [] ).map( ( result, i ) => normalise( result, i, insights?.analysis ) )
	const totalDuration = insights?.analysis?.duration_seconds ?? 0
	const durationLabel = totalDuration ? `${ totalDuration }s` : '—'
	const startedAt = row?.started_at ?? insights?.analysis?.started_at

	const handleExportReport = () => navigateToReportsTab( row.id )

	return (
		<div className={ ns }>

			<PageTopbar
				onBack={ onBack }
				title={ __( 'Analysis Run Detail', 'seo-by-rank-math' ) }
				actions={
					<Button
						variant="secondary"
						onClick={ handleExportReport }
					>
						{ __( 'Export Report', 'seo-by-rank-math' ) }
					</Button>
				}
			/>

			<div className={ `${ ns }__content` }>

				<div className={ `${ ns }__stat-cards` }>

					<StatCard
						compact
						icon=" rm-icon-target"
						label={ __( 'Target Brand', 'seo-by-rank-math' ) }
						value={ row?.brand_name || '—' }
						className="rank-math-ai-visibility-stat-card--target-brand"
						tooltip={ __( 'The brand this analysis run is for.', 'seo-by-rank-math' ) }
						analysis={ insights?.analysis }
					/>

					<StatCard
						compact
						icon="clock"
						label={ __( 'Duration & Timing', 'seo-by-rank-math' ) }
						value={ durationLabel }
						sub={ formatShortDate( startedAt ) }
						className="rank-math-ai-visibility-stat-card--duration-timing"
						tooltip={ __( 'Total duration of all queries in this run, and the date it was started.', 'seo-by-rank-math' ) }
						analysis={ insights?.analysis }
					/>

				</div>

				{ error && (
					<Notice status="error" isDismissible={ false }>{ error }</Notice>
				) }

				{ loading ? (
					<TableSkeleton columns={ 3 } rows={ 3 } />
				) : (
					<div className={ `${ ns }__table` }>
						<div className={ `${ ns }__table-row ${ ns }__table-row--header` }>
							<div className={ `${ ns }__col ${ ns }__col--query` }>
								{ __( 'Query text', 'seo-by-rank-math' ) }
							</div>
							<div className={ `${ ns }__col ${ ns }__col--sentiment` }>
								{ __( 'Sentiment', 'seo-by-rank-math' ) }
							</div>
							<div className={ `${ ns }__col ${ ns }__col--action` }>
								{ __( 'Action', 'seo-by-rank-math' ) }
							</div>
						</div>

						{ transcripts.length === 0 ? (
							<EmptyState
								heading={ __( 'No queries found', 'seo-by-rank-math' ) }
								description={ __( 'No queries were recorded for this analysis run.', 'seo-by-rank-math' ) }
							/>
						) : (
							transcripts.map( ( entry ) => (
								<div key={ entry.id } className={ `${ ns }__table-row` }>
									<div className={ `${ ns }__col ${ ns }__col--query` }>
										{ entry.query || '—' }
									</div>
									<div className={ `${ ns }__col ${ ns }__col--sentiment` }>
										{ ( entry.avg_sentiment ?? entry.sentiment ) !== null && ( entry.avg_sentiment ?? entry.sentiment ) !== undefined ? (
											<SentimentBadge score={ entry.avg_sentiment ?? entry.sentiment } />
										) : (
											<Tooltip text={ entry.found === false
												? __( 'Sentiment unavailable - this brand was not detected in the AI response for this query.', 'seo-by-rank-math' )
												: __( 'Sentiment data is not available for this query.', 'seo-by-rank-math' )
											}>
												<span>
													<SentimentBadge score={ null } />
												</span>
											</Tooltip>
										) }
									</div>
									<div className={ `${ ns }__col ${ ns }__col--action` }>
										<Button
											variant="secondary"
											iconLeft={ <span className="rm-icon-eye" /> }
											onClick={ () => setModalEntry( entry ) }
											className="rank-math-ai-visibility-view-transcript"
										>
											{ __( 'View Transcript', 'seo-by-rank-math' ) }
										</Button>
									</div>
								</div>
							) )
						) }
					</div>
				) }

				{ modalEntry && (
					<TranscriptModal
						entry={ modalEntry }
						onClose={ () => setModalEntry( null ) }
					/>
				) }

			</div>

		</div>
	)
}

AnalysisRunDetail.displayName = 'AnalysisRunDetail'

export default AnalysisRunDetail
