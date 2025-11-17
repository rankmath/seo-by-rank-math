/**
 * External dependencies
 */
import jQuery from 'jquery'

/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import PrimaryTermSelector from '@components/PrimaryTerm/StandaloneSelector'

class RankMathGutenberg {
	constructor() {
		addFilter(
			'editor.PostTaxonomyType',
			'rank-math',
			( PostTaxonomies ) => ( props ) => (
				<PrimaryTermSelector
					TermComponent={ PostTaxonomies }
					{ ...props }
				/>
			)
		)
	}
}

jQuery( document ).ready( function() {
	new RankMathGutenberg()
} )
