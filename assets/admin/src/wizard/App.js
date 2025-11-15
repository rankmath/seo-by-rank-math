/**
 * External dependencies
 */
import { useSearchParams, useNavigate } from 'react-router-dom'

/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data'

/**
 * Internal dependencies
 */
import Content from './views/Content'
import Footer from './views/Footer'
import getSteps from './steps/getSteps'

export default () => {
	const [ searchParams, setSearchParams ] = useSearchParams( {
		step: 'compatibility',
	} )
	const navigate = useNavigate()
	const currentStep = searchParams.get( 'step' )
	const data = useSelect( ( select ) =>
		select( 'rank-math-setup-wizard' ).getStepData( currentStep )
	)
	const steps = getSteps( currentStep, data )

	const contentProps = {
		steps,
		searchParams,
		setSearchParams,
		navigate,
	}

	return (
		<>
			<Content { ...contentProps } />
			<Footer searchParams={ searchParams } />
		</>
	)
}
