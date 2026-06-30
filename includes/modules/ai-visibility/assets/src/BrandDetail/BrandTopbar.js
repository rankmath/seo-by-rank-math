/**
 * BrandTopbar — header section of the Brand Detail page.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { Button } from '../shared/components'
import PageTopbar from '../shared/components/PageTopbar'

/**
 * @param {Object}   props
 * @param {Function} props.onBack
 * @param {Function} [props.onExport]
 * @return {JSX.Element} Rendered component.
 */
const BrandTopbar = ( {
	onBack,
	onExport = () => {},
} ) => (
	<PageTopbar
		onBack={ onBack }
		title={ __( 'Brand detail', 'seo-by-rank-math' ) }
		actions={
			<Button variant="secondary" onClick={ onExport }>
				{ __( 'Export Report', 'seo-by-rank-math' ) }
			</Button>
		}
	/>
)

export default BrandTopbar
