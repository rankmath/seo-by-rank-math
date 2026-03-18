/**
 * Links tab component.
 *
 * Shows links with URL, source post, and type.
 * PRO-only filters (anchor type, nofollow status) appear in the filter row
 * but open an upgrade CTA when applied.
 */

/**
 * External dependencies
 */
import { get } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState, useCallback, useMemo } from '@wordpress/element'
import { Modal } from '@wordpress/components'

/**
 * Internal dependencies
 */
import useTableData from '../hooks/useTableData'
import { parseHashParams, URL_PARAMS, setLinkTypeInHash } from '../urlState'
import ErrorCTA from '@components/ErrorCTA'

const { ListTable, TableSkeleton } = window.rankMathComponents

/**
 * PRO upgrade modal.
 *
 * @param {Function} onClose Callback to close the modal.
 */
const ProCTAModal = ( { onClose } ) => (
	<Modal
		onRequestClose={ onClose }
		className="rank-math-contentai-modal rank-math-modal rank-math-error-modal"
		shouldCloseOnClickOutside={ true }
	>
		<ErrorCTA showProNotice={ true } medium="Links" />
	</Modal>
)

/**
 * Read link filters from the URL hash (set when navigating from Posts tab counts).
 *
 * @return {Object} Initial filter values from URL.
 */
const getLinksFiltersFromURL = () => {
	const { searchParams } = parseHashParams()
	const linkType = searchParams.get( URL_PARAMS.LINK_TYPE ) || ''
	let isInternal = ''
	if ( linkType === 'internal' ) {
		isInternal = '1'
	} else if ( linkType === 'external' ) {
		isInternal = '0'
	}
	return {
		source_id: parseInt( searchParams.get( URL_PARAMS.SOURCE_ID ), 10 ) || 0,
		target_post_id: parseInt( searchParams.get( URL_PARAMS.TARGET_POST_ID ), 10 ) || 0,
		is_internal: isInternal,
	}
}

/**
 * Link type badge.
 *
 * @param {string} type 'internal' or 'external'
 */
const LinkTypeBadge = ( { type } ) => (
	<span className={ `rank-math-link-type-badge ${ type }` }>
		{ type === 'internal' ? __( 'Internal', 'rank-math' ) : __( 'External', 'rank-math' ) }
	</span>
)

/**
 * Links tab.
 *
 * @return {JSX.Element} Links tab content.
 */
const Links = () => {
	const [ upgradeModalOpen, setUpgradeModalOpen ] = useState( false )

	// Read initial filters from URL hash (set when clicking counts in Posts tab)
	const urlFilters = getLinksFiltersFromURL()

	const {
		data: links,
		stats,
		loading,
		pagination,
		filters,
		sortConfig,
		handleFilterChange,
		handlePageChange,
		handlePerPageChange,
		handleSortChange,
	} = useTableData( {
		dataEndpoint: '/rankmath/v1/links/links',
		statsEndpoint: '/rankmath/v1/links/links-stats',
		initialFilters: {
			search: '',
			source_id: urlFilters.source_id,
			target_post_id: urlFilters.target_post_id,
			is_internal: urlFilters.is_internal,
			orderby: 'id',
			order: 'DESC',
		},
	} )

	// Table headers
	const headers = useMemo( () => [
		{
			key: 'source_title',
			label: __( 'Source Post', 'rank-math' ),
			isSortable: true,
			isHideable: false,
			render: ( value, row ) => (
				<>
					{ row.source_edit_url ? (
						<a href={ row.source_edit_url } target="_blank" rel="noopener noreferrer">
							{ value || __( '(no title)', 'rank-math' ) }
						</a>
					) : (
						value || __( '(no title)', 'rank-math' )
					) }
					{ row.source_url && (
						<div className="rank-math-post-url">{ row.source_url }</div>
					) }
				</>
			),
		},
		{
			key: 'url',
			label: __( 'Destination', 'rank-math' ),
			isSortable: true,
			isHideable: false,
			render: ( value, row ) => (
				<>
					<a href={ value } target="_blank" rel="noopener noreferrer">
						{ row.target_title || value }
					</a>
					{ row.target_title && (
						<div className="rank-math-post-url">{ value }</div>
					) }
				</>
			),
		},
		{
			key: 'type',
			label: __( 'Link Type', 'rank-math' ),
			isSortable: true,
			render: ( value ) => <LinkTypeBadge type={ value } />,
		},
	], [] )

	// Link type toggle options
	const linkTypeOptions = [
		{ label: __( 'All', 'rank-math' ), value: '' },
		{ label: __( 'Internal', 'rank-math' ), value: '1', statusType: 'internal' },
		{ label: __( 'External', 'rank-math' ), value: '0', statusType: 'external' },
	]

	// Link status toggle options (PRO-only — clicking any non-All value opens upgrade modal)
	const linkStatusOptions = [
		{ label: __( 'All', 'rank-math' ), value: '', statusType: 'all' },
		{ label: __( 'Success', 'rank-math' ), value: '2xx', statusType: 'success' },
		{ label: __( 'Broken', 'rank-math' ), value: 'broken', statusType: 'error' },
		{ label: __( 'Redirects', 'rank-math' ), value: '3xx', statusType: 'warning' },
		{ label: __( 'Robots Blocked', 'rank-math' ), value: 'robots_blocked', statusType: 'blocked' },
		{ label: __( 'Marked Safe', 'rank-math' ), value: 'marked_safe', statusType: 'markedSafe' },
		{ label: __( 'Unchecked', 'rank-math' ), value: 'unchecked', statusType: 'unchecked' },
	]

	const handleStatusToggle = useCallback( ( value ) => {
		if ( value !== '' ) {
			setUpgradeModalOpen( true )
		}
	}, [ setUpgradeModalOpen ] )

	const handleSortChange2 = useCallback( ( key ) => {
		const newOrder = sortConfig.orderby === key && sortConfig.order === 'ASC' ? 'DESC' : 'ASC'
		handleSortChange( key, newOrder )
	}, [ sortConfig, handleSortChange ] )

	// Footer stats
	const footerStats = useMemo( () => {
		if ( ! stats ) {
			return []
		}

		return [
			{
				label: __( 'Total Links', 'rank-math' ),
				value: get( stats, 'total', 0 ),
			},
			{
				label: __( 'Internal Links', 'rank-math' ),
				value: get( stats, 'internal', 0 ),
			},
			{
				label: __( 'External Links', 'rank-math' ),
				value: get( stats, 'external', 0 ),
			},
		]
	}, [ stats ] )

	// Controlled sort config
	const tableSortConfig = useMemo( () => ( {
		key: sortConfig.orderby,
		direction: sortConfig.order === 'ASC' ? 'asc' : 'desc',
	} ), [ sortConfig ] )

	if ( loading && links.length === 0 ) {
		return <TableSkeleton columns={ 3 } rows={ 10 } />
	}

	return (
		<div className="rank-math-section-card">
			{ upgradeModalOpen && (
				<ProCTAModal onClose={ () => setUpgradeModalOpen( false ) } />
			) }
			<ListTable
				headers={ headers }
				rows={ links }
				stats={ footerStats }
				// Server-side pagination
				currentPage={ pagination.page }
				rowsPerPage={ pagination.perPage }
				totalItems={ pagination.total }
				totalPages={ pagination.pages }
				onPageChange={ handlePageChange }
				onRowsPerPageChange={ handlePerPageChange }
				// Server-side sorting
				sortConfig={ tableSortConfig }
				onSortChange={ handleSortChange2 }
				// Server-side filtering
				activeFilters={ filters }
				onFilterChange={ ( newFilters ) => {
					Object.entries( newFilters ).forEach( ( [ key, value ] ) => {
						handleFilterChange( key, value )
					} )
				} }
				// Link type toggle (tab style)
				primaryToggle={ {
					options: linkTypeOptions,
					value: filters.is_internal !== undefined && filters.is_internal !== '' ? String( filters.is_internal ) : '',
					filterKey: 'is_internal',
					onChange: ( value ) => {
						handleFilterChange( 'is_internal', value )
						setLinkTypeInHash( value )
					},
				} }
				// Link status toggle (colored pills — PRO-only, opens upgrade modal)
				secondaryToggle={ {
					options: linkStatusOptions,
					value: '',
					onChange: handleStatusToggle,
				} }
				storageKey="links-links"
			/>

		</div>
	)
}

export default Links
