/**
 * Posts tab component.
 *
 * Shows posts with their internal/external/incoming link counts and SEO score.
 * Uses the shared ListTable component from @rank-math/components.
 */

/**
 * External dependencies
 */
import { entries, map, reject } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState, useCallback, useMemo } from '@wordpress/element'

/**
 * Internal dependencies
 */
import useTableData from '../hooks/useTableData'
import {
	parseHashParams,
	getPostsFiltersFromURL,
	setPostsFiltersInHash,
	clearPostsFiltersFromHash,
	navigateToLinksTab,
} from '../urlState'

const { ListTable, TableSkeleton } = window.rankMathComponents

/**
 * Get available post type options from localized data.
 *
 * @return {Array} Array of {label, value} options.
 */
const getPostTypeOptions = () => {
	const postTypes = map(
		entries( window.rankMath.links?.postTypes ),
		( [ value, label ] ) => ( { value, label } )
	)
	return reject( postTypes, { value: 'attachment' } )
}

/**
 * Render SEO score badge.
 *
 * @param {number} score SEO score value (0-100).
 */
const SeoScoreBadge = ( { score } ) => {
	if ( ! score ) {
		return (
			<span className="rank-math-seo-score na">
				<strong>{ __( 'Not Set', 'rank-math' ) }</strong>
			</span>
		)
	}
	let scoreClass = 'bad'
	if ( score > 80 ) {
		scoreClass = 'great'
	} else if ( score > 50 ) {
		scoreClass = 'good'
	}
	return (
		<span className={ `rank-math-seo-score ${ scoreClass }` }>
			<strong>{ score } / 100</strong>
		</span>
	)
}

/**
 * Posts tab.
 *
 * @return {JSX.Element} Posts tab content.
 */
const Posts = () => {
	// Read initial filters from URL hash
	const { tabName, searchParams } = parseHashParams()
	const urlFilters = getPostsFiltersFromURL( searchParams )

	const [ appliedFilters, setAppliedFilters ] = useState( () => ( {
		post_type: urlFilters.post_type || [],
		is_orphan: urlFilters.is_orphan || '',
		seo_score_range: urlFilters.seo_score_range || '',
	} ) )

	const {
		data: posts,
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
		dataEndpoint: '/rankmath/v1/links/posts',
		statsEndpoint: '/rankmath/v1/links/posts-stats',
		initialFilters: {
			search: '',
			post_type: urlFilters.post_type || [],
			is_orphan: urlFilters.is_orphan || '',
			seo_score_range: urlFilters.seo_score_range || '',
			orderby: 'post_title',
			order: 'ASC',
		},
	} )

	// Navigate to Links tab filtered by this post's links
	const handleNavigateToLinks = useCallback( ( postId, linkType ) => {
		const linkFilters = {}
		if ( linkType === 'incoming' ) {
			linkFilters.target_post_id = postId
		} else {
			linkFilters.source_id = postId
			if ( linkType === 'internal' ) {
				linkFilters.link_type = 'internal'
			} else if ( linkType === 'external' ) {
				linkFilters.link_type = 'external'
			}
		}
		navigateToLinksTab( linkFilters )
	}, [] )

	// Table headers definition
	const headers = useMemo( () => [
		{
			key: 'post_title',
			label: __( 'Post Title', 'rank-math' ),
			isSortable: true,
			isHideable: false,
			render: ( value, row ) => (
				<>
					{ row.edit_url ? (
						<a href={ row.edit_url } target="_blank" rel="noopener noreferrer">
							{ value || __( '(no title)', 'rank-math' ) }
						</a>
					) : (
						value || __( '(no title)', 'rank-math' )
					) }
					{ row.post_url && (
						<div className="rank-math-post-url">{ row.post_url }</div>
					) }
				</>
			),
		},
		{
			key: 'internal_link_count',
			label: __( 'Internal Links', 'rank-math' ),
			isSortable: true,
			render: ( value, row ) => {
				const count = parseInt( value, 10 ) || 0
				if ( count === 0 ) {
					return <span className="rank-math-count-badge">{ count }</span>
				}
				return (
					<button
						type="button"
						className="rank-math-count-badge rank-math-count-badge-link rank-math-tooltip"
						onClick={ () => handleNavigateToLinks( row.post_id, 'internal' ) }
					>
						{ count }
						<span>{ __( 'View internal links from this post', 'rank-math' ) }</span>
					</button>
				)
			},
		},
		{
			key: 'external_link_count',
			label: __( 'External Links', 'rank-math' ),
			isSortable: true,
			render: ( value, row ) => {
				const count = parseInt( value, 10 ) || 0
				if ( count === 0 ) {
					return <span className="rank-math-count-badge">{ count }</span>
				}
				return (
					<button
						type="button"
						className="rank-math-count-badge rank-math-count-badge-link rank-math-tooltip"
						onClick={ () => handleNavigateToLinks( row.post_id, 'external' ) }
					>
						{ count }
						<span>{ __( 'View external links from this post', 'rank-math' ) }</span>
					</button>
				)
			},
		},
		{
			key: 'incoming_link_count',
			label: __( 'Incoming Links', 'rank-math' ),
			isSortable: true,
			render: ( value, row ) => {
				const count = parseInt( value, 10 ) || 0
				if ( count === 0 ) {
					return (
						<span className="rank-math-orphan-warning rank-math-tooltip" title={ __( 'Orphan post — no incoming links', 'rank-math' ) }>
							<i className="dashicons dashicons-warning" />
							<span>{ __( 'This post has no internal links. We recommend that you add links to this URL in other posts of your website.', 'rank-math' ) }</span>
						</span>
					)
				}
				return (
					<button
						type="button"
						className="rank-math-count-badge rank-math-count-badge-link rank-math-tooltip"
						onClick={ () => handleNavigateToLinks( row.post_id, 'incoming' ) }
					>
						{ count }
						<span>{ __( 'View incoming links to this post', 'rank-math' ) }</span>
					</button>
				)
			},
		},
		{
			key: 'seo_score',
			label: __( 'SEO Score', 'rank-math' ),
			isSortable: true,
			render: ( value ) => <SeoScoreBadge score={ value } />,
		},
		{
			key: 'post_type_label',
			label: __( 'Type', 'rank-math' ),
			isSortable: false,
			render: ( value, row ) => value || row.post_type,
		},
	], [ handleNavigateToLinks ] )

	// Orphan filter toggle options — statusType drives active/hover color
	const orphanOptions = [
		{ label: __( 'All Posts', 'rank-math' ), value: '', statusType: 'all' },
		{ label: __( 'Orphan Posts', 'rank-math' ), value: 'orphan', statusType: 'orphan' },
		{ label: __( 'With Incoming Links', 'rank-math' ), value: 'linked', statusType: 'linked' },
	]

	// SEO score range filter toggle options — statusType drives colored pill colors
	const seoScoreOptions = [
		{ label: __( 'All Scores', 'rank-math' ), value: '', statusType: 'all' },
		{ label: __( 'Great', 'rank-math' ), value: 'great', statusType: 'great' },
		{ label: __( 'Good', 'rank-math' ), value: 'good', statusType: 'good' },
		{ label: __( 'Bad', 'rank-math' ), value: 'bad', statusType: 'bad' },
		{ label: __( 'Not Set', 'rank-math' ), value: 'no-score', statusType: 'no-score' },
	]

	// Post type filter via FilterStaging (multi-select)
	const filterStagingFilters = useMemo( () => [
		{
			key: 'post_type',
			label: __( 'Post Type', 'rank-math' ),
			multiSelect: true,
			allLabel: __( 'All Types', 'rank-math' ),
			options: getPostTypeOptions(),
		},
	], [] )

	const handleSortChange2 = useCallback( ( key ) => {
		const newOrder = sortConfig.orderby === key && sortConfig.order === 'ASC' ? 'DESC' : 'ASC'
		handleSortChange( key, newOrder )
	}, [ sortConfig, handleSortChange ] )

	// Apply staged filters to URL hash and to API
	const handleApplyFilters = useCallback( ( pendingFilters ) => {
		setAppliedFilters( pendingFilters )
		setPostsFiltersInHash( tabName, pendingFilters )
		if ( pendingFilters.post_type !== undefined ) {
			handleFilterChange( 'post_type', pendingFilters.post_type )
		}
	}, [ tabName, handleFilterChange ] )

	const handleClearFilters = useCallback( ( clearedFilters ) => {
		setAppliedFilters( clearedFilters )
		clearPostsFiltersFromHash( tabName )
		handleFilterChange( 'post_type', [] )
	}, [ tabName, handleFilterChange ] )

	// Sync toggle filters immediately to URL hash
	const handleOrphanChange = useCallback( ( value ) => {
		handleFilterChange( 'is_orphan', value )
		const newFilters = { ...appliedFilters, is_orphan: value }
		setAppliedFilters( newFilters )
		setPostsFiltersInHash( tabName, newFilters )
	}, [ appliedFilters, tabName, handleFilterChange ] )

	const handleSeoScoreChange = useCallback( ( value ) => {
		handleFilterChange( 'seo_score_range', value )
		const newFilters = { ...appliedFilters, seo_score_range: value }
		setAppliedFilters( newFilters )
		setPostsFiltersInHash( tabName, newFilters )
	}, [ appliedFilters, tabName, handleFilterChange ] )

	// Stats for the pagination footer
	const footerStats = useMemo( () => {
		if ( ! stats ) {
			return []
		}

		return [
			{ label: __( 'Total Posts', 'rank-math' ), value: ( stats.total_posts || 0 ).toLocaleString() },
			{ label: __( 'Orphan Posts', 'rank-math' ), value: ( stats.orphan_posts || 0 ).toLocaleString() },
		]
	}, [ stats ] )

	// Controlled sort config for ListTable
	const tableSortConfig = useMemo( () => ( {
		key: sortConfig.orderby,
		direction: sortConfig.order === 'ASC' ? 'asc' : 'desc',
	} ), [ sortConfig ] )

	if ( loading && posts.length === 0 ) {
		return <TableSkeleton columns={ 6 } rows={ 10 } />
	}

	return (
		<div className="rank-math-section-card">
			<ListTable
				headers={ headers }
				rows={ posts }
				exportFilename="rank-math-posts"
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
				// Server-side filtering (search handled by ListTable's debounced search)
				activeFilters={ filters }
				onFilterChange={ ( newFilters ) => {
					Object.entries( newFilters ).forEach( ( [ key, value ] ) => {
						handleFilterChange( key, value )
					} )
				} }
				// Orphan filter as primaryToggle (tab style)
				primaryToggle={ {
					options: orphanOptions,
					value: filters.is_orphan || '',
					onChange: handleOrphanChange,
				} }
				// SEO score filter as secondaryToggle (colored pills)
				secondaryToggle={ {
					options: seoScoreOptions,
					value: filters.seo_score_range || '',
					onChange: handleSeoScoreChange,
				} }
				// Post type filter via FilterStaging (multi-select dropdown)
				filters={ filterStagingFilters }
				appliedFilters={ appliedFilters }
				onApplyFilters={ handleApplyFilters }
				onClearFilters={ handleClearFilters }
				storageKey="links-posts"
			/>

		</div>
	)
}

export default Posts
