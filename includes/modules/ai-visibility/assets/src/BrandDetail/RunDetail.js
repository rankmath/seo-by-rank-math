/**
 * RunDetail — transcript viewer for the brand's latest analysis.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { useState, useMemo, useEffect } from '@wordpress/element'
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { TableSkeleton } from '@rank-math/components'
import { TranscriptViewer, EmptyState } from '../shared/components'
import './RunDetail.scss'

/**
 * Map an insights query result to the shape TranscriptViewer expects.
 *
 * The id is qualified by platform — the backend reuses the same query_id
 * across platforms, so an unqualified id would collide.
 *
 * @param {Object} result   Query result from the insights payload.
 * @param {number} index    Fallback index.
 * @param {Object} analysis Analysis run (matched by platform) this result belongs to.
 * @return {Object} Normalised entry.
 */
const toEntry = ( result, index, analysis ) => {
	const baseId = result.query_id ?? index
	const platform = result.platform ?? 'default'

	return {
		...result,
		id: `${ baseId }-${ platform }`,
		query: result.query_text ?? '',
		response: result.response ?? '',
		created_at: analysis?.finished_at ?? null,
		duration_seconds: analysis?.duration_seconds ?? null,
		status: result.found ? 'success' : 'partial',
		model: null,
	}
}

/**
 * @param {Object}      props
 * @param {Object|null} props.insights Latest-analysis insights payload.
 * @param {boolean}     props.loading  Whether insights are loading.
 * @return {JSX.Element} Transcript viewer or loading/empty state.
 */
const RunDetail = ( { insights = null, loading = false } ) => {
	const entries = useMemo(
		() => {
			const analyses = insights?.analyses ?? []
			const findAnalysis = ( platform ) => analyses.find( ( analysis ) => analysis.platform === platform ) ?? analyses[ 0 ] ?? null

			return ( insights?.query_results ?? [] ).map( ( result, i ) => toEntry( result, i, findAnalysis( result.platform ) ) )
		},
		[ insights ]
	)

	const [ selectedId, setSelectedId ] = useState( null )

	useEffect( () => {
		setSelectedId( entries[ 0 ]?.id ?? null )
	}, [ entries ] )

	const ns = 'rank-math-ai-visibility-run-detail'

	if ( loading ) {
		return <TableSkeleton columns={ 3 } rows={ 4 } />
	}

	if ( ! entries.length ) {
		return (
			<div className={ ns }>
				<EmptyState
					heading={ __( 'No transcripts found', 'seo-by-rank-math' ) }
					description={ __( 'Transcripts will appear here once the first analysis completes.', 'seo-by-rank-math' ) }
				/>
			</div>
		)
	}

	return (
		<div className={ ns }>
			<TranscriptViewer
				entries={ entries }
				selectedEntryId={ selectedId }
				onSelectEntry={ setSelectedId }
			/>
		</div>
	)
}

RunDetail.displayName = 'RunDetail'

export default RunDetail
