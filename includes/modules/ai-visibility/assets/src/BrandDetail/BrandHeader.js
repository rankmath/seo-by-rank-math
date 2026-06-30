/**
 * BrandHeader — brand detail page header.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { external } from '@wordpress/icons'
import { Icon } from '@wordpress/components'

/**
 * Internal dependencies
 */
import { Button, CountryFlag } from '../shared/components'
import { formatDateTime } from '../utils/formatDate'
import './BrandHeader.scss'

/**
 * @param {string} url Brand URL from the API.
 * @return {string} URL with https:// prepended if missing.
 */
const getBrandUrl = ( url ) => {
	if ( ! url ) {
		return '#'
	}

	return url.startsWith( 'http' ) ? url : `https://${ url }`
}

/**
 * @param {Object}   props
 * @param {Object}   props.brand
 * @param {Function} [props.onEdit]
 * @return {JSX.Element} Rendered component.
 */
const BrandHeader = ( {
	brand,
	onEdit = () => {},
} ) => {
	const ns = 'rank-math-ai-visibility-brand-header'

	const isActive = brand?.status === 'active'

	return (
		<div className={ ns }>
			<div className={ `${ ns }__card` }>
				<div className={ `${ ns }__content` }>
					<div className={ `${ ns }__identity` }>

						<div className={ `${ ns }__name-row` }>
							<h1 className={ `${ ns }__name` }>{ brand.name }</h1>
							<Button
								variant="secondary"
								onClick={ onEdit }
								className={ `${ ns }__edit-btn` }
							>
								<span className="rm-icon rm-icon-edit" />
								{ __( 'Edit Brand', 'seo-by-rank-math' ) }
							</Button>
						</div>

						<div className={ `${ ns }__url-row` }>
							<a
								className={ `${ ns }__url` }
								href={ getBrandUrl( brand.url ) }
								target="_blank"
								rel="noreferrer"
							>
								{ brand.url }
							</a>
							{ brand.locale && <CountryFlag locale={ brand.locale } /> }
							<a
								className={ `${ ns }__external` }
								href={ getBrandUrl( brand.url ) }
								target="_blank"
								rel="noreferrer"
								aria-label={ __( 'Open brand URL', 'seo-by-rank-math' ) }
							>
								<Icon icon={ external } size={ 14 } />
							</a>
						</div>

						<div className={ `${ ns }__meta-row` }>
							<span className={ `${ ns }__status ${ ns }__status--${ isActive ? 'active' : 'inactive' }` }>
								{ isActive ? __( 'Active', 'seo-by-rank-math' ) : __( 'Inactive', 'seo-by-rank-math' ) }
							</span>
							{ brand.last_analyzed && (
								<span className={ `${ ns }__last-analysis` }>
									<span className="dashicons dashicons-clock" />
									{ __( 'Last analysis:', 'seo-by-rank-math' ) }
									{ ' ' }
									{ formatDateTime( brand.last_analyzed ) }
								</span>
							) }
						</div>

					</div>

					{ brand.description && (
						<div className={ `${ ns }__description-panel` }>
							<strong className={ `${ ns }__description-label` }>
								{ __( 'Description', 'seo-by-rank-math' ) }
							</strong>
							<p className={ `${ ns }__description-text` }>
								{ brand.description }
							</p>
						</div>
					) }

				</div>
			</div>
		</div>
	)
}

export default BrandHeader
