/**
 * BrandsTableToolbar — search + add-brand controls above the brands table.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { SearchControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import { Button } from '../shared/components'
import './BrandsTableToolbar.scss'

/**
 * @param {Object}   props
 * @param {string}   [props.searchValue='']
 * @param {Function} [props.onSearchChange]
 * @param {Function} [props.onAdd]
 * @return {JSX.Element} Rendered component.
 */
const BrandsTableToolbar = ( {
	searchValue = '',
	onSearchChange = () => {},
	onAdd = () => {},
} ) => {
	const ns = 'rank-math-ai-visibility-brands-toolbar'

	return (
		<div className={ ns }>
			<Button
				variant="primary"
				onClick={ onAdd }
			>
				{ __( '+ Add Brand / Product', 'seo-by-rank-math' ) }
			</Button>

			<SearchControl
				className={ `${ ns }__search` }
				placeholder={ __( 'Search name or URL', 'seo-by-rank-math' ) }
				value={ searchValue }
				onChange={ onSearchChange }
				__nextHasNoMarginBottom={ true }
			/>
		</div>
	)
}

BrandsTableToolbar.displayName = 'BrandsTableToolbar'

export default BrandsTableToolbar
