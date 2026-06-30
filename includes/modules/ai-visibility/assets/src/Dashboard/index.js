/**
 * Dashboard tab
 *
 * @since 1.0.273
 */

/**
 * External dependencies
 */
import { get, toLower, isNil } from 'lodash'

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { Notice } from '@wordpress/components'
import { useState, useEffect, useRef } from '@wordpress/element'
import { trendingUp, store } from '@wordpress/icons'

/**
 * Internal dependencies
 */
import useDashboard from '../shared/hooks/useDashboard'
import { getBrand } from '../shared/services/api/aiVisibilityApi'
import { StatCard, EmptyState } from '../shared/components'
import { ConfirmModal, AddBrandModal, UpgradePlanModal } from '../shared/Modals'
import BrandsTableToolbar from './BrandsTableToolbar'
import BrandsTable from './BrandsTable'
import BrandDetail from '../BrandDetail'
import { getActiveBrandId, navigateToBrand, navigateBackToDashboard } from '../utils/urlState'
import './Dashboard.scss'

// Plans not listed here are treated as unlimited.
const BRAND_LIMITS = {
	starter: 1,
	creator: 10,
	expert: 50,
}

const TOP_TIER_PLAN = 'expert' // no upgrade path — show limit-reached messaging

/**
 * @param {Object|null} confirmBrand
 * @param {string}      confirmAction 'disable' | 'enable'
 * @return {string} Localised confirmation message.
 */
const getConfirmMessage = ( confirmBrand, confirmAction ) => {
	if ( ! confirmBrand ) {
		return ''
	}

	if ( confirmAction === 'enable' ) {
		return sprintf(
			/* translators: %s is the brand name, e.g. "Nike". */
			__( `Resume tracking "%s"? Analysis will run on the next scheduled cycle.`, 'seo-by-rank-math' ),
			confirmBrand.name
		)
	}

	return sprintf(
		/* translators: %s is the brand name, e.g. "Nike". */
		__( `Stop tracking "%s"? You can re-enable it later.`, 'seo-by-rank-math' ),
		confirmBrand.name
	)
}

/**
 * Resolve the display value for a summary StatCard.
 *
 * @param {Object}    options
 * @param {boolean}   options.loading   Whether the dashboard is still loading.
 * @param {boolean}   options.hasBrands Whether any brands are tracked.
 * @param {*}         options.raw       Raw summary value (may be null/undefined).
 * @param {Function} [options.format]   Optional formatter for a present value.
 * @return {string|number|null} Placeholder, null (empty), 0 (missing) or formatted value.
 */
const getStatValue = ( { loading, hasBrands, raw, format } ) => {
	if ( loading ) {
		return '—'
	}
	if ( ! hasBrands ) {
		return null
	}
	if ( isNil( raw ) ) {
		return 0
	}
	return format ? format( raw ) : raw
}

/**
 * @param {Object}   props
 * @param {Array}    [props.locales]        Locale options for the brand modals.
 * @param {Function} [props.onBrandCreated] Callback when a new brand is successfully created.
 * @return {JSX.Element} Rendered component.
 */
const Dashboard = ( { locales = [], onBrandCreated = () => {} } ) => {
	const [ activeBrandId, setActiveBrandId ] = useState( getActiveBrandId )

	useEffect( () => {
		const handlePopState = () => setActiveBrandId( getActiveBrandId() )
		window.addEventListener( 'popstate', handlePopState )
		return () => window.removeEventListener( 'popstate', handlePopState )
	}, [] )

	const {
		brands,
		summary,
		loading,
		error,
		handleSearch,
		handleAddBrand,
		handleUpdateBrand,
		handleDisableBrand,
		handleEnableBrand,
	} = useDashboard()

	// Modal state
	const [ isAddModalOpen, setAddModalOpen ] = useState( false )
	const [ editBrand, setEditBrand ] = useState( null )
	const [ isSaving, setIsSaving ] = useState( false )
	const [ saveError, setSaveError ] = useState( null )

	// ConfirmModal state
	const [ confirmBrand, setConfirmBrand ] = useState( null )
	const [ confirmAction, setConfirmAction ] = useState( 'delete' )
	const [ isProcessing, setIsProcessing ] = useState( false )
	const [ actionError, setActionError ] = useState( null )

	const [ isUpgradeModalOpen, setUpgradeModalOpen ] = useState( false )

	// Search: debounced 300 ms, proxy filters cached rows server-side.
	const [ searchQuery, setSearchQuery ] = useState( '' )
	const isFirstSearchRender = useRef( true )

	useEffect( () => {
		if ( isFirstSearchRender.current ) {
			isFirstSearchRender.current = false
			return
		}

		const timer = setTimeout( () => {
			handleSearch( searchQuery.trim() )
		}, 300 )
		return () => clearTimeout( timer )
	}, [ searchQuery, handleSearch ] )

	const hasActiveSearch = !! searchQuery.trim()
	const isEmpty = ! loading && brands.length === 0 && ! hasActiveSearch

	const openDisableConfirm = ( brand ) => {
		setConfirmBrand( brand )
		setConfirmAction( brand?.status === 'inactive' ? 'enable' : 'disable' )
	}
	const closeConfirm = () => {
		setConfirmBrand( null )
		setActionError( null )
	}

	const confirmTitle = confirmAction === 'enable'
		? __( 'Activate brand?', 'seo-by-rank-math' )
		: __( 'Deactivate brand?', 'seo-by-rank-math' )

	const confirmMessage = getConfirmMessage( confirmBrand, confirmAction )

	const closeAddModal = () => {
		setAddModalOpen( false )
		setEditBrand( null )
		setSaveError( null )
	}

	const plan = toLower( get( window, 'rankMath.aiVisibility.plan', '' ) )
	const brandLimit = get( BRAND_LIMITS, plan, null )

	const openAddBrand = () => {
		if ( null !== brandLimit && brands.length >= brandLimit ) {
			setUpgradeModalOpen( true )
			return
		}
		setEditBrand( null )
		setAddModalOpen( true )
	}

	const handleSaveBrand = async ( data ) => {
		setSaveError( null )
		setIsSaving( true )
		try {
			if ( editBrand ) {
				await handleUpdateBrand( editBrand.id, data )
			} else {
				await handleAddBrand( data )
				onBrandCreated( __( 'Brand added! The first analysis is running — data will appear in approximately 10 minutes.', 'seo-by-rank-math' ) )
			}
			closeAddModal()
		} catch ( err ) {
			setSaveError( err?.message || __( 'Something went wrong. Please try again.', 'seo-by-rank-math' ) )
		} finally {
			setIsSaving( false )
		}
	}

	const onConfirmAction = async () => {
		if ( ! confirmBrand ) {
			return
		}
		setIsProcessing( true )
		setActionError( null )
		try {
			if ( confirmAction === 'disable' ) {
				await handleDisableBrand( confirmBrand.id )
			} else if ( confirmAction === 'enable' ) {
				await handleEnableBrand( confirmBrand.id )
			}
			closeConfirm()
		} catch ( err ) {
			setActionError( err?.message ?? __( 'Action failed. Please try again.', 'seo-by-rank-math' ) )
		} finally {
			setIsProcessing( false )
		}
	}

	const handleViewBrand = ( brand ) => {
		navigateToBrand( brand.id )
		setActiveBrandId( brand.id )
	}

	const handleBackToDashboard = () => {
		navigateBackToDashboard()
		setActiveBrandId( null )
	}

	if ( activeBrandId ) {
		return (
			<BrandDetail
				brandId={ activeBrandId }
				onBack={ handleBackToDashboard }
				locales={ locales }
			/>
		)
	}

	const ns = 'rank-math-ai-visibility-dashboard'

	return (
		<div className={ ns }>

			{ error && (
				<Notice status="error" isDismissible={ false }>
					{ error }
				</Notice>
			) }

			<div className={ `${ ns }__stats` }>
				<StatCard
					className="rank-math-ai-visibility-stat-card--active-brands"
					icon=" rm-icon rm-icon-ai-visibility"
					label={ __( 'Global AI Visibility Score', 'seo-by-rank-math' ) }
					value={ getStatValue( { loading, hasBrands: brands.length > 0, raw: summary?.ai_visibility_score, format: ( v ) => v + '/100' } ) }
					tooltip={ __( 'Average rank score across all tracked brands.', 'seo-by-rank-math' ) }
				/>
				<StatCard
					className="rank-math-ai-visibility-stat-card--analyses"
					icon={ trendingUp }
					label={ __( 'Analyses in last 24h', 'seo-by-rank-math' ) }
					value={ getStatValue( { loading, hasBrands: brands.length > 0, raw: summary?.analyses_last_24h } ) }
					tooltip={ __( 'Total analysis runs completed in the past 24 hours.', 'seo-by-rank-math' ) }
				/>
				<StatCard
					className="rank-math-ai-visibility-stat-card--mentions"
					icon="rm-icon rm-icon-comments"
					label={ __( 'Avg mentions per brand', 'seo-by-rank-math' ) }
					value={ getStatValue( { loading, hasBrands: brands.length > 0, raw: summary?.avg_mentions_per_brand } ) }
					tooltip={ __( 'Average number of AI-generated mentions across all tracked brands.', 'seo-by-rank-math' ) }
				/>
			</div>

			{ isEmpty && (
				<div className={ `${ ns }__empty` }>
					<EmptyState
						icon={ store }
						heading={ __( 'No brands or products tracked yet', 'seo-by-rank-math' ) }
						description={ __(
							'Add a brand or a specific product to see how you rank against competitors. Monitor your AI visibility, discover where you stand, and uncover opportunities to grow.',
							'seo-by-rank-math'
						) }
						ctaLabel={ __( '+ Add Your First Brand', 'seo-by-rank-math' ) }
						onCta={ openAddBrand }
					/>
				</div>
			) }

			{ ! isEmpty && (
				<div className={ `${ ns }__table-card` }>
					<BrandsTableToolbar
						searchValue={ searchQuery }
						onSearchChange={ setSearchQuery }
						onAdd={ openAddBrand }
					/>

					<BrandsTable
						brands={ brands }
						loading={ loading }
						onView={ handleViewBrand }
						onEdit={ async ( brand ) => {
							setEditBrand( brand )
							setAddModalOpen( true )
							try {
								const result = await getBrand( brand.id )
								if ( result?.brand ) {
									setEditBrand( result.brand )
								}
							} catch {
								// keep the partial brand already set
							}
						} }
						onDisable={ ( brand ) => openDisableConfirm( brand ) }
					/>
				</div>
			) }

			{ isAddModalOpen && (
				<AddBrandModal
					brand={ editBrand }
					onSave={ handleSaveBrand }
					onClose={ closeAddModal }
					isSaving={ isSaving }
					apiError={ saveError }
					locales={ locales }
				/>
			) }

			{ isUpgradeModalOpen && (
				<UpgradePlanModal
					plan={ plan }
					isTopTier={ TOP_TIER_PLAN === plan }
					onClose={ () => setUpgradeModalOpen( false ) }
				/>
			) }

			{ confirmBrand && (
				<ConfirmModal
					title={ confirmTitle }
					message={ confirmMessage }
					confirmLabel={ confirmAction === 'enable'
						? __( 'Activate', 'seo-by-rank-math' )
						: __( 'Deactivate', 'seo-by-rank-math' )
					}
					isDestructive={ confirmAction === 'disable' }
					isProcessing={ isProcessing }
					error={ actionError }
					onConfirm={ onConfirmAction }
					onCancel={ closeConfirm }
				/>
			) }

		</div>
	)
}

export default Dashboard
