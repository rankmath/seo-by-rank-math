/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data'

/**
 * Internal dependencies
 */
import TermPicker from './TermPicker'

/**
 * Primary Term Picker
 *
 * Class inspiration taken from Yoast (https://github.com/Yoast/wordpress-seo/)
 */
class PrimaryTermPicker extends TermPicker {
}

export default withSelect( ( select, { slug } ) => {
	const taxonomy = select( 'core' ).getTaxonomy( slug )
	const { getEditedPostAttribute } = select( 'core/editor' )

	return {
		taxonomy,
		selectedTermIds: taxonomy
			? getEditedPostAttribute( taxonomy.rest_base )
			: [],
		primaryTermID: select( 'rank-math' ).getPrimaryTermID(),
	}
} )( PrimaryTermPicker )
