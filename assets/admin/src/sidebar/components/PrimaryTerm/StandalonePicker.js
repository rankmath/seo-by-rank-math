/**
 * External dependencies
 */
import jQuery from 'jquery'

/**
 * WordPress dependencies
 */
import { dispatch, withSelect } from '@wordpress/data'

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
	handleSelectedTermsChange() {
		const { selectedTerms } = this.state
		const primaryTermID = parseInt( this.props.primaryTermID )
		const selectedTerm = selectedTerms.find(
			( term ) => term.id === primaryTermID
		)

		if ( ! selectedTerm ) {
			/**
			 * If the selected term is no longer available, set the primary term ID to
			 * the first term, and to -1 if no term is available.
			 */
			this.onChange( selectedTerms.length ? selectedTerms[ 0 ].id : '' )
		}
	}

	onChange( termId ) {
		termId = parseInt( termId )
		rankMath.assessor.serpData.primaryTerm = termId
		jQuery( '#rank_math_primary_' + this.props.taxonomy.slug ).val( termId )
		dispatch( 'core/editor' ).editPost( {
			meta: { refreshMe: 'refreshUI' },
		} )
	}
}

export default withSelect( ( select, { slug } ) => {
	const { getEditedPostAttribute } = select( 'core/editor' )
	const meta = getEditedPostAttribute( 'meta' )
	const taxonomy = select( 'core' ).getTaxonomy( slug )

	return {
		taxonomy,
		meta,
		selectedTermIds: taxonomy
			? getEditedPostAttribute( taxonomy.rest_base )
			: [],
		primaryTermID: jQuery( '#rank_math_primary_' + taxonomy.slug ).val(),
	}
} )( PrimaryTermPicker )
