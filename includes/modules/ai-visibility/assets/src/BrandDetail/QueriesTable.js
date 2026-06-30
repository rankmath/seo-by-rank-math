/**
 * QueriesTable — Brand detail Queries sub-tab.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState, useEffect, useCallback } from '@wordpress/element'

/**
 * Internal dependencies
 */
import { TableSkeleton } from '@rank-math/components'
import { getQueries, updateQuery, generateQueries } from '../shared/services/api/aiVisibilityApi'
import { LoadingButton, StatusToggle, EmptyState, DataTable } from '../shared/components'
import { ConfirmModal } from '../shared/Modals'
import { formatLongDate } from '../utils/formatDate'
import './QueriesTable.scss'

/**
 * @param {Object} props
 * @param {number} props.brandId
 * @return {JSX.Element} Queries sub-tab content.
 */
const QueriesTable = ( { brandId } ) => {
	const ns = 'rank-math-ai-visibility-queries-table'

	const [ queries, setQueries ] = useState( [] )
	const [ queryOrder, setQueryOrder ] = useState( [] )
	const [ loading, setLoading ] = useState( true )
	const [ togglingId, setTogglingId ] = useState( null )
	const [ regenConfirm, setRegenConfirm ] = useState( false )
	const [ isRegenerating, setIsRegenerating ] = useState( false )

	const fetchQueries = useCallback( async () => {
		if ( ! brandId ) {
			setQueries( [] )
			setLoading( false )
			return
		}

		setLoading( true )
		try {
			const data = await getQueries( brandId )
			const fetched = Array.isArray( data?.queries ) ? data.queries : []
			setQueryOrder( fetched.map( ( q ) => q.id ) )
			setQueries( fetched )
		} catch {
			setQueries( [] )
		} finally {
			setLoading( false )
		}
	}, [ brandId ] )

	useEffect( () => {
		fetchQueries()
	}, [ fetchQueries ] )

	const handleToggleStatus = useCallback( async ( query, newValue ) => {
		if ( ! brandId ) {
			return
		}

		setTogglingId( query.id )
		setQueries( ( prev ) => {
			const updated = prev.map( ( q ) => q.id === query.id ? { ...q, enabled: newValue } : q )
			return [ ...updated ].sort( ( a, b ) => queryOrder.indexOf( a.id ) - queryOrder.indexOf( b.id ) )
		} )

		try {
			await updateQuery( brandId, query.id, { enabled: newValue } )
		} catch {
			setQueries( ( prev ) => {
				const reverted = prev.map( ( q ) => q.id === query.id ? { ...q, enabled: query.enabled } : q )
				return [ ...reverted ].sort( ( a, b ) => queryOrder.indexOf( a.id ) - queryOrder.indexOf( b.id ) )
			} )
		} finally {
			setTogglingId( null )
		}
	}, [ brandId ] )

	const handleRegenerateQueries = useCallback( async () => {
		if ( ! brandId ) {
			return
		}

		setRegenConfirm( false )
		setIsRegenerating( true )
		setLoading( true )
		try {
			const data = await generateQueries( brandId )
			setQueries( Array.isArray( data?.queries ) ? data.queries : [] )
		} catch {
			await fetchQueries()
		} finally {
			setIsRegenerating( false )
			setLoading( false )
		}
	}, [ brandId, fetchQueries ] )

	return (
		<div className={ ns }>

			{ /* Toolbar */ }
			<div className={ `${ ns }__toolbar` }>
				<LoadingButton
					variant="secondary"
					onClick={ () => queries.length > 0 ? setRegenConfirm( true ) : handleRegenerateQueries() }
					isLoading={ isRegenerating }
					loadingLabel={ __( 'Regenerating…', 'seo-by-rank-math' ) }
					iconLeft={ <span className="dashicons dashicons-update" aria-hidden="true" /> }
					disabled={ loading }
				>
					{ __( 'Regenerate baseline queries', 'seo-by-rank-math' ) }
				</LoadingButton>
			</div>

			{ loading && <TableSkeleton columns={ 4 } rows={ 5 } /> }

			{ ! loading && queries.length === 0 && (
				<EmptyState
					heading={ __( 'No queries yet', 'seo-by-rank-math' ) }
					description={ __(
						'Regenerate baseline queries to start tracking how AI answers questions about this brand.',
						'seo-by-rank-math'
					) }
				/>
			) }

			{ /* Table */ }
			{ ! loading && queries.length > 0 && (
				<DataTable
					columns={ [
						{
							key: 'text',
							label: __( 'Query text', 'seo-by-rank-math' ),
							width: '56%',
							render: ( row ) => row.text,
						},
						{
							key: 'created_at',
							label: __( 'Created', 'seo-by-rank-math' ),
							render: ( row ) => formatLongDate( row.created_at ),
						},
						{
							key: 'updated_at',
							label: __( 'Last updated', 'seo-by-rank-math' ),
							render: ( row ) => formatLongDate( row.updated_at ?? row.created_at ),
						},
						{
							key: 'status',
							label: __( 'Status', 'seo-by-rank-math' ),
							width: '120px',
							render: ( row ) => (
								<StatusToggle
									value={ row.enabled !== false }
									onChange={ ( val ) => handleToggleStatus( row, val ) }
									isLoading={ togglingId === row.id }
								/>
							),
						},
					] }
					rows={ queries }
					rowKey="id"
				/>
			) }

			{ regenConfirm && (
				<ConfirmModal
					title={ __( 'Regenerate queries?', 'seo-by-rank-math' ) }
					message={ __(
						'This will replace all existing baseline queries with new AI-generated ones. Continue?',
						'seo-by-rank-math'
					) }
					confirmLabel={ __( 'Regenerate', 'seo-by-rank-math' ) }
					isDestructive={ false }
					onConfirm={ handleRegenerateQueries }
					onCancel={ () => setRegenConfirm( false ) }
				/>
			) }

		</div>
	)
}

export default QueriesTable
