/**
 * TranscriptViewer — two-panel transcript viewer.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { useState, useMemo } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { SelectControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import TranscriptModal from './TranscriptModal'
import StatusBadge from './StatusBadge'
import DateInput from './DateInput'
import { formatDateTime } from '../../utils/formatDate'
import './TranscriptViewer.scss'

/**
 * Format ISO timestamp as "Apr 15, 2026 · 10:00 AM".
 *
 * @param {string} isoStr ISO date string.
 * @return {string} Formatted timestamp or '—'.
 */
const formatTimestamp = ( isoStr ) => formatDateTime( isoStr, ' · ' )

/**
 * @param {Object}   props
 * @param {Object}   props.entry            Transcript entry data.
 * @param {boolean}  props.isSelected       Whether this card is active.
 * @param {Function} props.onSelect         Called with entry.id on left panel click.
 * @param {Function} props.onViewTranscript Called with full entry object when "View >" is clicked.
 * @return {JSX.Element} Rendered component.
 */
const EntryCard = ( { entry, isSelected, onSelect, onViewTranscript = () => {} } ) => {
	const ns = 'rank-math-ai-visibility-transcript-viewer'
	const modelName = entry.model || entry.platform || ''

	return (
		<div
			role="button"
			tabIndex={ 0 }
			className={ [
				`${ ns }__entry-card`,
				isSelected ? `${ ns }__entry-card--selected` : '',
			].filter( Boolean ).join( ' ' ) }
			onClick={ () => onSelect( entry.id ) }
			onKeyDown={ ( e ) => ( e.key === 'Enter' || e.key === ' ' ) && onSelect( entry.id ) }
			aria-current={ isSelected ? 'true' : undefined }
		>
			<div className={ `${ ns }__entry-meta` }>
				<span className={ `${ ns }__entry-timestamp` }>
					{ formatTimestamp( entry.created_at ) }
					{ modelName && ` · ${ modelName }` }
				</span>
				<StatusBadge status={ entry.status } />
			</div>

			<p className={ `${ ns }__entry-query-text` }>
				{ entry.query || '—' }
			</p>

			<div className={ `${ ns }__entry-footer` }>
				{ entry.duration_seconds !== null && entry.duration_seconds !== undefined && (
					<span className={ `${ ns }__entry-duration` }>
						<span className="dashicons dashicons-clock" aria-hidden="true" />
						{ entry.duration_seconds }s
					</span>
				) }
				<span
					role="button"
					tabIndex={ 0 }
					className={ `${ ns }__entry-view-link` }
					onClick={ ( e ) => {
						e.stopPropagation(); onViewTranscript( entry )
					} }
					onKeyDown={ ( e ) => ( e.key === 'Enter' || e.key === ' ' ) && onViewTranscript( entry ) }
				>
					{ __( 'View', 'seo-by-rank-math' ) }
					<span className="dashicons dashicons-arrow-right-alt2" aria-hidden="true" />
				</span>
			</div>
		</div>
	)
}

/**
 * @param {Object}      props
 * @param {Object|null} props.entry Selected entry, or null.
 * @return {JSX.Element} Right-panel transcript preview.
 */
const TranscriptDetail = ( { entry } ) => {
	const ns = 'rank-math-ai-visibility-transcript-viewer'

	return (
		<div className={ `${ ns }__detail` }>

			<div className={ `${ ns }__detail-header` }>
				<span className="dashicons rm-icon-comments" aria-hidden="true" />
				<span className={ `${ ns }__detail-header-title` }>
					{ __( 'Transcript preview', 'seo-by-rank-math' ) }
				</span>
			</div>

			{ entry ? (
				<div className={ `${ ns }__detail-content` }>

					<div className={ `${ ns }__section` }>
						<div className={ `${ ns }__section-left` }>
							<span className="dashicons dashicons-admin-users" aria-hidden="true" />
						</div>
						<div className={ `${ ns }__section-right` }>
							<div className={ `${ ns }__section-label` }>
								{ __( 'Original Query', 'seo-by-rank-math' ) }
							</div>
							<div className={ `${ ns }__query-box` }>
								{ entry.query || '—' }
							</div>
						</div>
					</div>

					<div className={ `${ ns }__section` }>
						<div className={ `${ ns }__section-left` }>
							<span className="dashicons dashicons-superhero-alt" aria-hidden="true" />
						</div>
						<div className={ `${ ns }__section-right` }>
							<div className={ `${ ns }__section-label` }>
								{ __( 'Model Response', 'seo-by-rank-math' ) }
							</div>
							<div className={ `${ ns }__response-box` }>
								{ entry.response || entry.excerpt || '—' }
							</div>
						</div>
					</div>

				</div>
			) : (
				<div className={ `${ ns }__detail-empty` }>
					<p>{ __( 'Select a query to preview its transcript.', 'seo-by-rank-math' ) }</p>
				</div>
			) }

		</div>
	)
}

/**
 * @param {Object}   props
 * @param {Array}    [props.entries=[]]      Transcript entry objects.
 * @param {*}        [props.selectedEntryId] ID shown in the detail panel.
 * @param {Function} [props.onSelectEntry]   Called with entry ID on selection.
 * @return {JSX.Element} Two-panel viewer.
 */
const TranscriptViewer = ( {
	entries = [],
	selectedEntryId = null,
	onSelectEntry = () => {},
} ) => {
	const ns = 'rank-math-ai-visibility-transcript-viewer'

	const [ dateFilter, setDateFilter ] = useState( '' )
	const [ queryFilter, setQueryFilter ] = useState( '' )
	const [ modalEntry, setModalEntry ] = useState( null )

	const queryOptions = useMemo( () => [
		{ label: __( 'All queries', 'seo-by-rank-math' ), value: '' },
		...entries.map( ( e ) => {
			let label = `Entry ${ e.id }`
			if ( e.query ) {
				label = e.query.length > 60 ? e.query.slice( 0, 60 ) + '…' : e.query
			}
			return { label, value: String( e.id ) }
		} ),
	], [ entries ] )

	const filteredEntries = useMemo( () => {
		let result = entries
		if ( dateFilter ) {
			result = result.filter( ( e ) => e.created_at?.startsWith( dateFilter ) )
		}
		if ( queryFilter ) {
			result = result.filter( ( e ) => String( e.id ) === queryFilter )
		}
		return result
	}, [ entries, dateFilter, queryFilter ] )

	const effectiveId = selectedEntryId ?? filteredEntries[ 0 ]?.id ?? null
	const selectedEntry = entries.find( ( e ) => e.id === effectiveId ) ?? null

	return (
		<div className={ ns }>
			<div className={ `${ ns }__left` }>
				<div className={ `${ ns }__filter-bar` }>
					<div className={ `${ ns }__date-filter` }>
						<DateInput
							value={ dateFilter }
							onChange={ ( e ) => setDateFilter( e.target.value ) }
							ariaLabel={ __( 'Filter by date', 'seo-by-rank-math' ) }
							className={ `${ ns }__date-input` }
						/>
					</div>
					<SelectControl
						value={ queryFilter }
						options={ queryOptions }
						onChange={ setQueryFilter }
						className={ `${ ns }__query-select` }
						__nextHasNoMarginBottom={ true }
					/>
				</div>

				<div className={ `${ ns }__entry-list` }>
					{ filteredEntries.length === 0 ? (
						<p className={ `${ ns }__empty-list` }>
							{ __( 'No entries match the current filters.', 'seo-by-rank-math' ) }
						</p>
					) : (
						filteredEntries.map( ( entry ) => (
							<EntryCard
								key={ entry.id }
								entry={ entry }
								isSelected={ entry.id === effectiveId }
								onSelect={ onSelectEntry }
								onViewTranscript={ setModalEntry }
							/>
						) )
					) }
				</div>

			</div>

			<TranscriptDetail entry={ selectedEntry } />

			{ modalEntry && (
				<TranscriptModal
					entry={ modalEntry }
					onClose={ () => setModalEntry( null ) }
				/>
			) }

		</div>
	)
}

TranscriptViewer.displayName = 'TranscriptViewer'

export default TranscriptViewer
