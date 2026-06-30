/**
 * BrandDetail — brand detail page wrapper.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState, useEffect, createElement } from '@wordpress/element'
import { TabPanel } from '@wordpress/components'

/**
 * Internal dependencies
 */
import { TableSkeleton } from '@rank-math/components'
import { getBrand, getInsights, updateBrand } from '../shared/services/api/aiVisibilityApi'
import useFetch from '../shared/hooks/useFetch'
import { AddBrandModal } from '../shared/Modals'
import {
	navigateBackToDashboard,
	getActiveBrandTab,
	setActiveBrandTabInUrl,
	navigateToReportsTab,
} from '../utils/urlState'
import BrandTopbar from './BrandTopbar'
import BrandHeader from './BrandHeader'
import BrandOverview from './BrandOverview'
import QueriesTable from './QueriesTable'
import CompetitorsTable from './CompetitorsTable'
import RunDetail from './RunDetail'
import './BrandDetail.scss'

const OverviewView = ( { brand, insights, insightsLoading } ) => (
	<BrandOverview brand={ brand } insights={ insights } loading={ insightsLoading } />
)
const QueriesView = ( { brand } ) => <QueriesTable brandId={ brand?.id } />
const CompetitorsView = ( { insights, insightsLoading } ) => (
	<CompetitorsTable insights={ insights } loading={ insightsLoading } />
)
const TranscriptsView = ( { insights, insightsLoading } ) => (
	<RunDetail insights={ insights } loading={ insightsLoading } />
)

const SUB_TABS = [
	{ name: 'overview', title: __( 'Overview', 'seo-by-rank-math' ), view: OverviewView },
	{ name: 'queries', title: __( 'Queries', 'seo-by-rank-math' ), view: QueriesView },
	{ name: 'competitors', title: __( 'Competitors', 'seo-by-rank-math' ), view: CompetitorsView },
	{ name: 'transcripts', title: __( 'Raw data / Transcripts', 'seo-by-rank-math' ), view: TranscriptsView },
]

/**
 * @param {Object}   props
 * @param {number}   props.brandId
 * @param {Function} [props.onBack]
 * @param {Array}    [props.locales]
 * @return {JSX.Element} Rendered component.
 */
const BrandDetail = ( { brandId, onBack, locales = [] } ) => {
	const handleBack = onBack || navigateBackToDashboard
	const { data: brandData, loading, error, setData: setBrandData } = useFetch(
		() => getBrand( brandId ),
		[ brandId ],
		{ skip: ! brandId, errorMessage: __( 'Failed to load brand.', 'seo-by-rank-math' ) }
	)
	const brand = brandData?.brand ?? null

	const { data: insightsData, loading: insightsLoading } = useFetch(
		() => getInsights( brandId ),
		[ brandId ],
		{ skip: ! brandId, errorMessage: __( 'Failed to load insights.', 'seo-by-rank-math' ) }
	)
	const insights = insightsData?.insights ?? null

	const [ activeSubTab, setActiveSubTab ] = useState( () => getActiveBrandTab() )

	const [ isEditModalOpen, setEditModalOpen ] = useState( false )
	const [ isSaving, setIsSaving ] = useState( false )
	const [ saveError, setSaveError ] = useState( null )

	const handleSaveBrand = async ( data ) => {
		setIsSaving( true )
		setSaveError( null )
		try {
			const result = await updateBrand( brandId, data )
			setBrandData( { brand: result?.brand ?? { ...brand, ...data } } )
			setEditModalOpen( false )
		} catch ( err ) {
			setSaveError( err?.message ?? __( 'Failed to save brand.', 'seo-by-rank-math' ) )
		} finally {
			setIsSaving( false )
		}
	}

	const ns = 'rank-math-ai-visibility-brand-detail'

	const handleExport = () => navigateToReportsTab( brand?.id )

	useEffect( () => {
		const handlePopState = () => setActiveSubTab( getActiveBrandTab() )
		window.addEventListener( 'popstate', handlePopState )
		return () => window.removeEventListener( 'popstate', handlePopState )
	}, [] )

	if ( loading ) {
		return <TableSkeleton columns={ 3 } rows={ 4 } />
	}

	if ( error || ! brand ) {
		return (
			<div className={ `${ ns } ${ ns }--error` }>
				<p>{ error || __( 'Brand not found.', 'seo-by-rank-math' ) }</p>
				<button
					className="button"
					onClick={ handleBack }
				>
					{ __( '← Back to Dashboard', 'seo-by-rank-math' ) }
				</button>
			</div>
		)
	}

	return (
		<div className={ ns }>

			<BrandTopbar
				onBack={ handleBack }
				onExport={ handleExport }
			/>

			<div className={ `${ ns }__content` }>
				<BrandHeader
					brand={ brand }
					onEdit={ () => setEditModalOpen( true ) }
				/>

				<TabPanel
					key={ activeSubTab }
					className={ `rank-math-tabs ${ ns }__tabs` }
					activeClass="is-active"
					initialTabName={ activeSubTab }
					tabs={ SUB_TABS }
					onSelect={ ( tabName ) => {
						setActiveBrandTabInUrl( tabName )
						setActiveSubTab( tabName )
					} }
				>
					{ ( tab ) => (
						<div className={ `${ ns }__tab ${ ns }__tab--${ tab.name }` }>
							{ createElement( tab.view, { brand, insights, insightsLoading } ) }
						</div>
					) }
				</TabPanel>
			</div>

			{ isEditModalOpen && (
				<AddBrandModal
					brand={ brand }
					onSave={ handleSaveBrand }
					onClose={ () => {
						setEditModalOpen( false )
						setSaveError( null )
					} }
					isSaving={ isSaving }
					apiError={ saveError }
					locales={ locales }
				/>
			) }

		</div>
	)
}

export default BrandDetail
