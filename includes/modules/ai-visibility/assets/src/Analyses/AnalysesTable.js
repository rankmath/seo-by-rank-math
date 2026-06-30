/**
 * AnalysesTable — paginated analyses runs table.
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
import { Button, StartTime, StatusIcon } from '../shared/components'
import './AnalysesTable.scss'

/**
 * AnalysesTable component.
 *
 * @param {Object}   props
 * @param {Array}    [props.items=[]]        Analysis run rows.
 * @param {boolean}  [props.loading=false]   Show skeleton when true and items is empty.
 * @param {Object}   [props.pagination={}]   `{ page, perPage, total, pages }`.
 * @param {Function} [props.onViewDetail]    Handler for "View Run Detail" — opens transcript.
 * @param {Function} [props.onPageChange]    Called with new page number.
 * @param {Function} [props.onPerPageChange] Called with new per-page value.
 * @return {JSX.Element} Paginated analyses runs table with section header.
 */
const AnalysesTable = ( {
	items = [],
	loading = false,
	pagination = {},
	onViewDetail = () => {},
	onPageChange = () => {},
	onPerPageChange = () => {},
} ) => {
	const headers = useMemo( () => [
		{
			key: 'brand_name',
			label: __( 'Brand', 'seo-by-rank-math' ),
		},
		{
			key: 'started_at',
			label: __( 'Last analyzed', 'seo-by-rank-math' ),
			render: ( value, row ) => {
				const hasStatus = row.status === 'error' || row.status === 'running'
				return (
					<span className="rank-math-ai-visibility-analyses-table__time-cell">
						{ value ? <StartTime value={ value } /> : ( ! hasStatus && '−' ) }
						{ hasStatus && <StatusIcon variant={ row.status } /> }
					</span>
				)
			},
		},
		{
			key: 'id',
			label: __( 'Action', 'seo-by-rank-math' ),
			render: ( _, row ) => {
				// A completed run exists (and thus viewable transcripts) if
				// last_analyzed is set. analysis_status can read `pending` for
				// the next scheduled cycle even when prior data exists.
				if ( ! row.started_at ) {
					return null
				}

				return (
					<Button
						className="rank-math-ai-visibility-view-transcript"
						iconLeft={ <span className="rm-icon-eye" /> }
						onClick={ () => onViewDetail( row ) }
					>
						{ __( 'View Run Detail', 'seo-by-rank-math' ) }
					</Button>
				)
			},
		},
	], [ onViewDetail ] )

	if ( loading && ! items.length ) {
		return <TableSkeleton columns={ 3 } rows={ 10 } />
	}

	return (
		<Table
			storageKey="ai-visibility-analyses"
			headers={ headers }
			rows={ items }
			currentPage={ get( pagination, 'page', 1 ) }
			rowsPerPage={ get( pagination, 'perPage', 10 ) }
			totalItems={ get( pagination, 'total', items.length ) }
			totalPages={ get( pagination, 'pages', 1 ) }
			onPageChange={ onPageChange }
			onRowsPerPageChange={ onPerPageChange }
		/>
	)
}

export default memo( AnalysesTable )
