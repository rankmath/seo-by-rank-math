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
 * @copyright Copyright (C) 2008-2019, Yoast BV
 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
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
