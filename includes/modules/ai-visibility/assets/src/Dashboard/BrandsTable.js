/**
 * BrandsTable — brands list table for the Dashboard tab.
 *
 * @since 1.0.273
 */

/**
 * External dependencies
 */
import { get } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useMemo, memo } from '@wordpress/element'

/**
 * Internal dependencies
 */
import { ListTable as Table, TableSkeleton } from '@rank-math/components'
import { formatLongDate } from '../utils/formatDate'
import { getAnalysisState } from '../utils/analysisState'
import {
	RankBadge,
	SentimentBadge,
	CountryFlag,
	ActionButtons,
	ScoreBadge,
	CountBadge,
	StatusIcon,
} from '../shared/components'
import './BrandsTable.scss'

/**
 * @param {Object}   props
 * @param {Array}    [props.brands=[]]
 * @param {boolean}  [props.loading=false]
 * @param {Object}   [props.pagination={}]   `{ page, perPage, total, pages }`
 * @param {Function} [props.onView]
 * @param {Function} [props.onEdit]
 * @param {Function} [props.onDisable]
 * @param {Function} [props.onPageChange]
 * @param {Function} [props.onPerPageChange]
 * @return {JSX.Element} Rendered component.
 */
const BrandsTable = ( {
	brands = [],
	loading = false,
	pagination = {},
	onView = () => {},
	onEdit = () => {},
	onDisable = () => {},
	onPageChange = () => {},
	onPerPageChange = () => {},
} ) => {
	const ns = 'rank-math-ai-visibility-brands-table'

	const headers = useMemo( () => [
		{
			key: 'name',
			label: __( 'Brand', 'seo-by-rank-math' ),
			render: ( value, row ) => (
				<div className={ `${ ns }__brand-cell` }>
					{ /* eslint-disable-next-line jsx-a11y/anchor-is-valid */ }
					<a href="#" className={ `${ ns }__brand-name` } onClick={ ( event ) => {
						event.preventDefault()
						onView( row )
					} }>
						{ value }
					</a>
					<div className={ `${ ns }__brand-link` }>
						<a
							className={ `${ ns }__brand-url` }
							href={ row.url }
							target="_blank"
							rel="noreferrer"
						>
							{ row.url }
						</a>
						{ row.locale && <CountryFlag locale={ row.locale } /> }
					</div>
				</div>
			),
		},
		{
			key: 'score',
			label: __( 'AI Visibility Score', 'seo-by-rank-math' ),
			render: ( value ) => <ScoreBadge score={ value ?? null } />,
		},
		{
			key: 'rank',
			label: __( 'Avg Rank', 'seo-by-rank-math' ),
			render: ( value ) => <RankBadge rank={ value } />,
		},
		{
			key: 'avg_sentiment',
			label: __( 'Avg sentiment', 'seo-by-rank-math' ),
			render: ( value ) => <SentimentBadge score={ value } />,
		},
		{
			key: 'mentions',
			label: __( 'Mentions', 'seo-by-rank-math' ),
			render: ( value ) => <CountBadge value={ value } variant="mentions" />,
		},
		{
			key: 'citations',
			label: __( 'Citations', 'seo-by-rank-math' ),
			render: ( value ) => <CountBadge value={ value } variant="citations" />,
		},
		{
			key: 'last_analyzed',
			label: __( 'Last analyzed', 'seo-by-rank-math' ),
			render: ( value, row ) => {
				const state = getAnalysisState( row )
				return (
					<span className={ `${ ns }__last-analyzed` }>
						{ value ? formatLongDate( value ) : ( ! state && '-' ) }
						{ state && <StatusIcon variant={ state } /> }
					</span>
				)
			},
		},
		{
			key: 'id',
			label: __( 'Actions', 'seo-by-rank-math' ),
			render: ( _, row ) => (
				<ActionButtons
					status={ row?.status }
					onView={ () => onView( row ) }
					onEdit={ () => onEdit( row ) }
					onDisable={ () => onDisable( row ) }
				/>
			),
		},
	], [ onView, onEdit, onDisable ] )

	if ( loading && ! brands.length ) {
		return <TableSkeleton columns={ 7 } rows={ 10 } />
	}

	return (
		<div className={ ns }>
			<Table
				storageKey="ai-visibility-brands"
				headers={ headers }
				rows={ brands }
				currentPage={ get( pagination, 'page', 1 ) }
				rowsPerPage={ get( pagination, 'perPage', 10 ) }
				totalItems={ get( pagination, 'total', brands.length ) }
				totalPages={ get( pagination, 'pages', 1 ) }
				onPageChange={ onPageChange }
				onRowsPerPageChange={ onPerPageChange }
				getRowClass={ ( row ) => row?.status === 'inactive' ? 'is-disabled' : '' }
			/>
		</div>
	)
}

export default memo( BrandsTable )
