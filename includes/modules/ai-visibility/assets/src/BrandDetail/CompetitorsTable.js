/**
 * CompetitorsTable — Brand detail Competitors sub-tab.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { TableSkeleton } from '@rank-math/components'
import { SentimentBadge, EmptyState, CountBadge, DataTable } from '../shared/components'
import './CompetitorsTable.scss'

/**
 * @param {string} name Competitor name.
 * @return {string} Deterministic hex colour from the palette.
 */
const colorFromName = ( name ) => {
	const palette = [ '#E60012', '#107C10', '#1A1A1A', '#1B2838', '#5260B9', '#017CBA', '#F59E0B', '#42B268' ]
	let hash = 0
	for ( let i = 0; i < ( name || '' ).length; i++ ) {
		hash = ( ( hash * 31 ) + name.charCodeAt( i ) ) % palette.length
	}
	return palette[ hash ]
}

/**
 * @param {Object} props
 * @param {string} props.name
 * @return {JSX.Element} Rendered component.
 */
const BrandAvatar = ( { name } ) => (
	<span
		className="rank-math-ai-visibility-competitors-table__avatar"
		style={ { backgroundColor: colorFromName( name ) } }
		aria-hidden="true"
	>
		{ name?.charAt( 0 )?.toUpperCase() ?? '?' }
	</span>
)

/**
 * @param {Object}      props
 * @param {Object|null} props.insights
 * @param {boolean}     props.loading
 * @return {JSX.Element} Rendered component.
 */
const CompetitorsTable = ( { insights = null, loading = false } ) => {
	const competitors = insights?.competitors ?? []

	const ns = 'rank-math-ai-visibility-competitors-table'

	if ( loading ) {
		return <TableSkeleton columns={ 3 } rows={ 4 } />
	}

	if ( ! competitors.length ) {
		return (
			<div className={ ns }>
				<EmptyState
					heading={ __( 'No competitors detected yet', 'seo-by-rank-math' ) }
					description={ __( 'Competitors are automatically identified during analysis runs.', 'seo-by-rank-math' ) }
				/>
			</div>
		)
	}

	const columns = [
		{
			key: 'name',
			label: __( 'Competitor', 'seo-by-rank-math' ),
			width: '50%',
			render: ( row ) => (
				<>
					<BrandAvatar name={ row.name } />
					<span className={ `${ ns }__name` }>{ row.name }</span>
				</>
			),
		},
		{
			key: 'avg_sentiment',
			label: __( 'Avg sentiment', 'seo-by-rank-math' ),
			render: ( row ) => <SentimentBadge score={ row.avg_sentiment } />,
		},
		{
			key: 'mentions',
			label: __( 'Mentions', 'seo-by-rank-math' ),
			render: ( row ) => <CountBadge value={ row.mentions ?? null } variant="mentions" />,
		},
	]

	return (
		<div className={ ns }>
			<DataTable columns={ columns } rows={ competitors } rowKey="name" />
		</div>
	)
}

CompetitorsTable.displayName = 'CompetitorsTable'

export default CompetitorsTable
