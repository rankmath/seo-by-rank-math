/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element'
import { PanelRow } from '@wordpress/components'

/**
 * Internal dependencies
 */
import PrimaryTermPicker from './StandalonePicker'

const taxonomySupportPrimaryTerm = ( props ) => {
	if ( false === rankMath.assessor.primaryTaxonomy ) {
		return false
	}

	return props.slug === rankMath.assessor.primaryTaxonomy.name
}

const PrimaryTermSelector = ( props ) => {
	const { TermComponent } = props

	if ( ! taxonomySupportPrimaryTerm( props ) ) {
		return <TermComponent { ...props } />
	}

	return (
		<Fragment>
			<TermComponent { ...props } />

			<PanelRow className="rank-math-primary-term-picker">
				<PrimaryTermPicker { ...props } />
			</PanelRow>
		</Fragment>
	)
}

export default PrimaryTermSelector
