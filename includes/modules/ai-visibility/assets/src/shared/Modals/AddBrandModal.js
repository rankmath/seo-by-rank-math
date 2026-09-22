/**
 * AddBrandModal — add / edit brand form dialog.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { __, _n, sprintf } from '@wordpress/i18n'
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
import { toPlatformList, getDefaultPlatforms, getEnabledPlatformIds } from '../services/platforms'
import './AddBrandModal.scss'

/**
 * Blank form state.
 *
 * @param {Array}  platformList         Available platforms.
 * @param {string} [defaultLanguage=''] Default language for a new brand (site language).
 * @return {Object} Empty form values.
 */
const emptyForm = ( platformList, defaultLanguage = '' ) => ( {
	name: '',
	url: '',
	description: '',
	locale: '',
	language: defaultLanguage,
	interval: 'monthly',
	platforms: getDefaultPlatforms( platformList ),
} )

/**
 * Order in which fields are validated / scrolled to.
 * Used to focus the first errored field on a failed save.
 */
const FIELD_ORDER = [ 'name', 'url', 'description', 'language' ]

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
 * @param {Object|null}   [props.brand=null]         null = add mode, object = edit (pre-fills form).
 * @param {Function}      props.onSave               Called with the validated form payload.
 * @param {Function}      props.onClose              Called when the modal should close.
 * @param {boolean}       [props.isSaving=false]     Shows spinner + disables all fields.
 * @param {string | null} props.apiError             API error message to display.
 * @param {Array}         [props.locales=[]]         Locale options injected from PHP via wp_localize_script.
 * @param {Array}         [props.languages=[]]       Output language options injected from PHP via wp_localize_script.
 * @param {string}        [props.defaultLanguage=''] Default language for a new brand (site language), injected from PHP.
 * @param {Array}         [props.intervals=[]]       { label, value, disabled } options injected from PHP via wp_localize_script.
 * @param {Object}        [props.platforms={}]       AI platform registry injected from PHP.
 * @param {number}        [props.maxPlatforms=1]     Platforms selectable on the current plan.
 * @return {JSX.Element} Brand add/edit dialog.
 */
const AddBrandModal = ( {
	brand = null,
	onSave,
	onClose,
	isSaving = false,
	apiError = null,
	locales = [],
	languages = [],
	defaultLanguage = '',
	intervals = [],
	platforms = {},
	maxPlatforms = 1,
} ) => {
	const platformList = useMemo( () => toPlatformList( platforms ), [ platforms ] )
	const enabledPlatformCount = useMemo( () => getEnabledPlatformIds( platformList ).length, [ platformList ] )

	const [ form, setForm ] = useState( () => emptyForm( platformList, defaultLanguage ) )
	const [ errors, setErrors ] = useState( {} )

	const ns = 'rank-math-ai-visibility-add-brand-modal'

	// Refs to field wrappers — used to scroll/focus the first errored field.
	const fieldRefs = {
		name: useRef( null ),
		url: useRef( null ),
		description: useRef( null ),
		language: useRef( null ),
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

	const languageOptions = useMemo(
		() => languages.reduce( ( acc, { name, icon } ) => {
			acc[ name ] = icon ? `${ icon } ${ name }` : name
			return acc
		}, {} ),
		[ languages ]
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
				// Legacy brands predate the language field.
				language: brand.language ?? 'US English',
				interval: brand.interval ?? 'monthly',
				platforms: brand.platforms?.length
					? brand.platforms.slice( 0, maxPlatforms )
					: getDefaultPlatforms( platformList ),
			} )
		} else {
			setForm( emptyForm( platformList, defaultLanguage ) )
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
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
	 * Toggle a platform. Selecting past the plan cap evicts the oldest pick.
	 *
	 * @param {string} id Platform id to toggle.
	 */
	const togglePlatform = ( id ) => {
		setForm( ( prev ) => {
			if ( prev.platforms.includes( id ) ) {
				return {
					...prev,
					platforms: prev.platforms.filter( ( p ) => p !== id ),
				}
			}

			const next = [ ...prev.platforms, id ]

			return {
				...prev,
				platforms: next.slice( Math.max( 0, next.length - maxPlatforms ) ),
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
		if ( ! form.language ) {
			newErrors.language = __( 'Language is required.', 'seo-by-rank-math' )
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
			language: form.language,
			interval: form.interval,
			platforms: form.platforms,
		} )
	}

	const isEditMode = Boolean( brand )
	const isActionDisabled = isSaving || form?.platforms?.length === 0 || ! form?.language || Object.keys( errors ).length > 0

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

				<div className={ `${ ns }__field` } ref={ fieldRefs.language }>
					<span className={ `${ ns }__label` }>
						{ __( 'Output Language', 'seo-by-rank-math' ) }
					</span>
					<SelectWithSearch
						value={ form.language }
						options={ languageOptions }
						onChange={ set( 'language' ) }
						disabled={ isSaving || isEditMode }
						className={ errors.language ? `${ ns }__field--error` : '' }
					/>
					{ errors.language && (
						<p className={ `${ ns }__field-note ${ ns }__field-note--error` }>
							{ errors.language }
						</p>
					) }
					{ isEditMode && (
						<p className={ `${ ns }__field-note` }>
							{ __( 'Language can\'t be changed after a brand is created.', 'seo-by-rank-math' ) }
						</p>
					) }
				</div>

				{ /* Interval */ }
				<div className={ `${ ns }__field` }>
					<SelectControl
						label={ __( 'Frequency of analyses', 'seo-by-rank-math' ) }
						value={ form.interval }
						options={ intervals }
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
					{ maxPlatforms < enabledPlatformCount && (
						<p className={ `${ ns }__field-note` }>
							{ sprintf(
								/* translators: %d: number of AI platforms selectable on the current plan. */
								_n(
									'Your plan lets you track %d platform per brand. Upgrade to Expert to track them all at once.',
									'Your plan lets you track %d platforms per brand. Upgrade to Expert to track them all at once.',
									maxPlatforms,
									'seo-by-rank-math'
								),
								maxPlatforms
							) }
						</p>
					) }
					{ form.platforms.length === 0 && (
						<p className={ `${ ns }__field-note ${ ns }__field-note--error` }>
							{ __( 'Select at least one AI platform.', 'seo-by-rank-math' ) }
						</p>
					) }
					<div className={ `${ ns }__platforms` }>
						{ platformList.map( ( platform ) => (
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
