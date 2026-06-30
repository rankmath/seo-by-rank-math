/**
 * AddBrandModal — add / edit brand form dialog.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState, useEffect, useMemo, useRef } from '@wordpress/element'
import { Modal, TextControl, TextareaControl, SelectControl, CheckboxControl, Icon } from '@wordpress/components'
import { close } from '@wordpress/icons'

/**
 * Internal dependencies
 */
import { SelectWithSearch } from '@rank-math/components'
import Button from '../components/Button'
import LoadingButton from '../components/LoadingButton'
import getLink from '@helpers/getLink'
import './AddBrandModal.scss'

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

const EMPTY_FORM = {
	name: '',
	url: '',
	description: '',
	locale: '',
	interval: 'weekly',
	platforms: [ 'chatgpt' ],
}

/**
 * Order in which fields are validated / scrolled to.
 * Used to focus the first errored field on a failed save.
 */
const FIELD_ORDER = [ 'name', 'url', 'description' ]

/**
 * Analysis interval options.
 */
const INTERVAL_OPTIONS = [
	{ label: __( 'Weekly', 'seo-by-rank-math' ), value: 'weekly', disabled: false },
	{ label: __( 'Monthly (Coming Soon)', 'seo-by-rank-math' ), value: 'monthly', disabled: true },
	{ label: __( 'Daily (Coming Soon)', 'seo-by-rank-math' ), value: 'daily', disabled: true },
]

/**
 * AI Platform definitions.
 *
 * `enabled` — whether the platform is available for selection.
 * Disabled platforms show a "Coming Soon" badge.
 *
 * Layout: left column = index 0–2, right column = index 3–4.
 */
const AI_PLATFORMS = [
	{ id: 'chatgpt', label: 'ChatGPT', enabled: true },
	{ id: 'perplexity', label: 'Perplexity', enabled: false },
	{ id: 'google_ai_overview', label: 'Google AI Overview', enabled: false },
	{ id: 'gemini', label: 'Gemini', enabled: false },
	{ id: 'claude', label: 'Claude', enabled: false },
]

const LEFT_PLATFORMS = AI_PLATFORMS.slice( 0, 3 )
const RIGHT_PLATFORMS = AI_PLATFORMS.slice( 3 )

// ---------------------------------------------------------------------------
// Sub-components
// ---------------------------------------------------------------------------

/** "Coming Soon" pill badge. */
const ComingSoonBadge = () => (
	<span className="rank-math-ai-visibility-add-brand-modal__coming-soon">
		{ __( 'Coming Soon', 'seo-by-rank-math' ) }
	</span>
)

/**
 * Single platform row — checkbox + label + optional badge.
 *
 * @param {Object}   props
 * @param {Object}   props.platform Platform definition object.
 * @param {boolean}  props.checked  Whether the checkbox is checked.
 * @param {Function} props.onChange Toggle handler.
 * @param {boolean}  props.disabled Parent-level disabled (isSaving).
 */
const PlatformRow = ( { platform, checked, onChange, disabled } ) => {
	const ns = 'rank-math-ai-visibility-add-brand-modal'
	const isEnabled = platform.enabled
	const rowClass = [
		`${ ns }__platform-row`,
		! isEnabled ? `${ ns }__platform-row--disabled` : '',
	].filter( Boolean ).join( ' ' )

	return (
		<div className={ rowClass }>
			<CheckboxControl
				label={ platform.label }
				checked={ isEnabled ? checked : false }
				onChange={ isEnabled ? onChange : undefined }
				disabled={ ! isEnabled || disabled }
				__nextHasNoMarginBottom={ true }
			/>
			{ ! isEnabled && <ComingSoonBadge /> }
		</div>
	)
}

/**
 * AddBrandModal component.
 *
 * @param {Object}        props
 * @param {Object|null}   [props.brand=null]     null = add mode, object = edit (pre-fills form).
 * @param {Function}      props.onSave           Called with the validated form payload.
 * @param {Function}      props.onClose          Called when the modal should close.
 * @param {boolean}       [props.isSaving=false] Shows spinner + disables all fields.
 * @param {string | null} props.apiError         API error message to display.
 * @param {Array}         [props.locales=[]]     Locale options injected from PHP via wp_localize_script.
 * @return {JSX.Element} Brand add/edit dialog.
 */
const AddBrandModal = ( {
	brand = null,
	onSave,
	onClose,
	isSaving = false,
	apiError = null,
	locales = [],
} ) => {
	const [ form, setForm ] = useState( EMPTY_FORM )
	const [ errors, setErrors ] = useState( {} )

	const ns = 'rank-math-ai-visibility-add-brand-modal'

	// Refs to field wrappers — used to scroll/focus the first errored field.
	const fieldRefs = {
		name: useRef( null ),
		url: useRef( null ),
		description: useRef( null ),
	}

	// Locale options — object map expected by SelectWithSearch: { value: label, … }
	// Memoized so the object is only rebuilt when the locales prop changes.
	const localeOptions = useMemo(
		() => locales.reduce( ( acc, { value, label } ) => {
			acc[ value ] = label
			return acc
		}, { '': __( 'Select country (optional)', 'seo-by-rank-math' ) } ),
		[ locales ]
	)

	// Pre-fill when editing.
	useEffect( () => {
		if ( brand ) {
			setForm( {
				id: brand.id,
				name: brand.name ?? '',
				url: brand.url ?? '',
				description: brand.description ?? '',
				locale: brand.locale ?? '',
				interval: brand.interval ?? 'weekly',
				platforms: brand.platforms ?? [ 'chatgpt' ],
			} )
		} else {
			setForm( EMPTY_FORM )
		}
	}, [ brand ] )

	// ── Field helpers ────────────────────────────────────────────────────────

	const set = ( field ) => ( value ) => {
		setForm( ( prev ) => ( { ...prev, [ field ]: value } ) )
		setErrors( ( prev ) => {
			if ( ! prev[ field ] ) {
				return prev
			}
			const next = { ...prev }
			delete next[ field ]
			return next
		} )
	}

	/**
	 * Toggle a platform ID in the platforms array.
	 *
	 * @param {string} id Platform id to toggle.
	 */
	const togglePlatform = ( id ) => {
		setForm( ( prev ) => {
			const exists = prev.platforms.includes( id )
			return {
				...prev,
				platforms: exists
					? prev.platforms.filter( ( p ) => p !== id )
					: [ ...prev.platforms, id ],
			}
		} )
	}

	/**
	 * On blur: if the user typed a bare domain or a protocol-relative URL,
	 * silently prepend `https://` so validation always sees a full URL.
	 */
	const handleUrlBlur = () => {
		const val = form.url.trim()
		if ( ! val || /^https?:\/\//i.test( val ) ) {
			return
		}
		const normalized = val.startsWith( '//' ) ? 'https:' + val : 'https://' + val
		set( 'url' )( normalized )
	}

	// ── Validation ───────────────────────────────────────────────────────────

	const validate = () => {
		const newErrors = {}

		if ( ! form.name.trim() ) {
			newErrors.name = __( 'Brand name is required.', 'seo-by-rank-math' )
		}
		if ( ! form.url.trim() ) {
			newErrors.url = __( 'Website URL is required.', 'seo-by-rank-math' )
		} else if ( ! /^https?:\/\/.+\..+/.test( form.url.trim() ) ) {
			newErrors.url = __( 'Please enter a valid URL (must start with https://).', 'seo-by-rank-math' )
		}
		if ( ! form.description.trim() ) {
			newErrors.description = __( 'Description is required.', 'seo-by-rank-math' )
		}

		setErrors( newErrors )
		return newErrors
	}

	/**
	 * Scroll to (and focus) the first field that has a validation error,
	 * so the user is taken straight to what needs fixing.
	 *
	 * @param {Object} errs Errors keyed by field name.
	 */
	const scrollToFirstError = ( errs ) => {
		const firstField = FIELD_ORDER.find( ( field ) => errs[ field ] )
		const node = firstField ? fieldRefs[ firstField ]?.current : null
		if ( ! node ) {
			return
		}
		node.scrollIntoView( { behavior: 'smooth', block: 'center' } )
		const input = node.querySelector( 'input, textarea' )
		if ( input ) {
			input.focus( { preventScroll: true } )
		}
	}

	// ── Save ─────────────────────────────────────────────────────────────────

	const handleSave = () => {
		const newErrors = validate()
		if ( Object.keys( newErrors ).length > 0 ) {
			scrollToFirstError( newErrors )
			return
		}
		onSave( {
			name: form.name.trim(),
			url: form.url.trim(),
			description: form.description.trim(),
			locale: form.locale,
			interval: form.interval,
			platforms: form.platforms,
		} )
	}

	const isEditMode = Boolean( brand )
	const isActionDisabled = isSaving || form?.platforms?.length === 0 || Object.keys( errors ).length > 0

	return (
		<Modal
			onRequestClose={ onClose }
			className={ ns }
			__experimentalHideHeader
		>
			{ /* ── Custom header: title + subtitle + close button ── */ }
			<div className={ `${ ns }__header` }>
				<div className={ `${ ns }__header-text` }>
					<h1 className={ `${ ns }__title` }>
						{ isEditMode ? __( 'Edit Brand or Product', 'seo-by-rank-math' ) : __( 'Add Brand or Product', 'seo-by-rank-math' ) }
					</h1>
					<p className={ `${ ns }__subtitle` }>
						{ __( 'Share the basics so we can start tracking this brand or a product.', 'seo-by-rank-math' ) }
						<a href={ getLink( 'ai-visibility', 'AI Visibility Add Brand Modal' ) } target="_blank" rel="noopener noreferrer">
							{ __( 'Learn more', 'seo-by-rank-math' ) }
						</a>
					</p>
				</div>

				<Button
					variant=""
					onClick={ onClose }
					disabled={ isSaving }
					className={ `${ ns }__close` }
				>
					<Icon icon={ close } size={ 24 } />
				</Button>
			</div>

			{ /* ── Divider between header and body ── */ }
			<hr className={ `${ ns }__divider` } />

			{ /* ── Body: form fields ── */ }
			<div className={ `${ ns }__body` }>

				{ /* Brand / Product Name */ }
				<div className={ `${ ns }__field` } ref={ fieldRefs.name }>
					<TextControl
						label={ __( 'Brand / Product Name', 'seo-by-rank-math' ) }
						value={ form.name }
						onChange={ set( 'name' ) }
						placeholder={ __( 'Enter your brand or product name', 'seo-by-rank-math' ) }
						disabled={ isSaving }
						help={ errors.name }
						className={ errors.name ? `${ ns }__field--error` : '' }
						__next40pxDefaultSize={ true }
						__nextHasNoMarginBottom={ true }
					/>
				</div>

				{ /* Brand / Product URL */ }
				<div className={ `${ ns }__field` } ref={ fieldRefs.url }>
					<TextControl
						label={ __( 'Brand / Product URL', 'seo-by-rank-math' ) }
						value={ form.url }
						onChange={ set( 'url' ) }
						onBlur={ handleUrlBlur }
						placeholder="https://example.com"
						type="url"
						disabled={ isSaving }
						help={ errors.url }
						className={ errors.url ? `${ ns }__field--error` : '' }
						__next40pxDefaultSize={ true }
						__nextHasNoMarginBottom={ true }
					/>
				</div>

				{ /* Description */ }
				<div className={ `${ ns }__field` } ref={ fieldRefs.description }>
					<TextareaControl
						label={ __( 'How would you describe your brand/product?', 'seo-by-rank-math' ) }
						value={ form.description }
						onChange={ set( 'description' ) }
						placeholder={ __( 'Describe what your brand/product does, who it\'s for, and what makes it unique. This helps AI models identify and accurately represent you.', 'seo-by-rank-math' ) }
						disabled={ isSaving }
						rows={ 3 }
						help={ errors.description }
						className={ errors.description ? `${ ns }__field--error` : '' }
						__nextHasNoMarginBottom={ true }
					/>
				</div>

				{ /* Target Country (optional) */ }
				<div className={ `${ ns }__field` }>
					<span className={ `${ ns }__label` }>
						{ __( 'Target Country (Optional)', 'seo-by-rank-math' ) }
					</span>
					<SelectWithSearch
						value={ form.locale }
						options={ localeOptions }
						onChange={ set( 'locale' ) }
						disabled={ isSaving }
					/>
				</div>

				{ /* Interval */ }
				<div className={ `${ ns }__field` }>
					<SelectControl
						label={ __( 'Frequency of analyses', 'seo-by-rank-math' ) }
						value={ form.interval }
						options={ INTERVAL_OPTIONS }
						onChange={ set( 'interval' ) }
						disabled={ isSaving }
						__next40pxDefaultSize={ true }
						__nextHasNoMarginBottom={ true }
					/>
				</div>

				{ /* AI Platforms */ }
				<div className={ `${ ns }__field` }>
					<span className={ `${ ns }__label` }>
						{ __( 'AI Platforms', 'seo-by-rank-math' ) }
					</span>
					<div className={ `${ ns }__platforms` }>
						<div className={ `${ ns }__platforms-col` }>
							{ LEFT_PLATFORMS.map( ( platform ) => (
								<PlatformRow
									key={ platform.id }
									platform={ platform }
									checked={ form.platforms.includes( platform.id ) }
									onChange={ () => togglePlatform( platform.id ) }
									disabled={ isSaving }
								/>
							) ) }
						</div>
						<div className={ `${ ns }__platforms-col` }>
							{ RIGHT_PLATFORMS.map( ( platform ) => (
								<PlatformRow
									key={ platform.id }
									platform={ platform }
									checked={ form.platforms.includes( platform.id ) }
									onChange={ () => togglePlatform( platform.id ) }
									disabled={ isSaving }
								/>
							) ) }
						</div>
					</div>
				</div>

			</div>

			{ /* ── API error message ── */ }
			{ apiError && (
				<div className={ `${ ns }__api-error` } role="alert">
					{ apiError }
				</div>
			) }

			{ /* ── Footer ── */ }
			<div className={ `${ ns }__footer` }>
				<Button
					variant="secondary"
					onClick={ onClose }
					disabled={ isSaving }
				>
					{ __( 'Cancel', 'seo-by-rank-math' ) }
				</Button>

				<LoadingButton
					variant="primary"
					onClick={ handleSave }
					isLoading={ isSaving }
					loadingLabel={ isEditMode
						? __( 'Saving…', 'seo-by-rank-math' )
						: __( 'Adding…', 'seo-by-rank-math' )
					}
					disabled={ isActionDisabled }
				>
					{
						isEditMode
							? __( 'Save Changes', 'seo-by-rank-math' )
							: __( 'Add', 'seo-by-rank-math' )
					}
				</LoadingButton>
			</div>
		</Modal>
	)
}

AddBrandModal.displayName = 'AddBrandModal'

export default AddBrandModal
