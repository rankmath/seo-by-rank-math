/**
 * WordPress dependencies
 */
import { SelectControl } from '@wordpress/components'
import { dispatch, withSelect } from '@wordpress/data'
import { __ } from '@wordpress/i18n'

const PrimaryTermSelect = ( {
	taxonomySlug,
	primaryTermId,
	options,
} ) => {
	if ( options.length < 2 || ! taxonomySlug ) {
		return null
	}
	const doChange = ( value ) => {
		dispatch( 'rank-math' ).updatePrimaryTermID( parseInt( value ), taxonomySlug )
	}
	return (
		<SelectControl
			className="rank-math-primary-term-select"
			label={ __( 'Select Primary Term', 'rank-math' ) }
			value={ primaryTermId }
			options={ options }
			onChange={ doChange }
		/>
	)
}

export default withSelect( ( select ) => {
	return {
		primaryTermId: select( 'rank-math' ).getPrimaryTermID(),
	}
} )( PrimaryTermSelect )
