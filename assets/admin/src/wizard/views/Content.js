/**
 * External dependencies
 */
import { includes, map, findIndex, fromPairs, isEmpty, isBoolean, isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose'
import { withSelect, withDispatch } from '@wordpress/data'
import { useEffect } from '@wordpress/element'
import apiFetch from '@wordpress/api-fetch'
import { TabPanel } from '@wordpress/components'

/**
 * Internal dependencies
 */
import Footer from '../components/Footer'
import Header from '../components/Header'
import StepProgressSkeleton from '../components/StepProgressSkeleton'

const Content = ( { currentStep, data, steps, getStepData, onStepChange, saveData, updateData, skipStep, getNextStep } ) => {
	useEffect( () => {
		if ( isEmpty( data ) ) {
			getStepData()
		}

		// Fetch next step data.
		const nextStep = getNextStep()
		if ( nextStep ) {
			getStepData( nextStep )
		} else {
			// If there's no next step (last step), fetch current step data to ensure tracking.
			getStepData( currentStep )
		}
	}, [ currentStep ] )

	return (
		<div className="rank-math-steps-progress-wrapper">
			<TabPanel
				tabs={ steps }
				key={ currentStep }
				initialTabName={ currentStep }
				className="rank-math-steps-progress header"
				onSelect={ onStepChange }
			>
				{ ( step ) => {
					const { name, slug, heading, view: View } = step

					if ( isEmpty( data ) ) {
						return <StepProgressSkeleton />
					}

					return (
						<div className="wrapper">
							<div className={ `main-content wizard-content--${ slug || name }` }>
								{ heading && <Header { ...step } /> }
								<View data={ data } saveData={ saveData } updateData={ updateData } skipStep={ skipStep } />
							</div>
							{
								! includes( [ 'ready', 'compatibility', 'import' ], currentStep ) &&
								<Footer data={ data } saveData={ saveData } skipStep={ skipStep } currentStep={ currentStep } />
							}
						</div>
					)
				} }
			</TabPanel>
		</div>
	)
}

export default compose(
	withSelect( ( select, props ) => {
		const currentStep = props.searchParams.get( 'step' ) || 'compatibility'
		const store = select( 'rank-math-setup-wizard' )
		const data = store.getStepData( currentStep )

		return {
			...props,
			data,
			currentStep,
		}
	} ),
	withDispatch( ( dispatch, props ) => {
		const { steps, data, currentStep, navigate, searchParams, setSearchParams } = props

		return {
			onStepChange( tabName ) {
				tabName = tabName === 'ready1' ? 'ready' : tabName

				if ( tabName !== currentStep ) {
					setSearchParams( ( params ) => fromPairs( [ ...params, [ 'step', tabName ] ] ) )
				}
			},
			getStepData( step ) {
				step = step || currentStep

				apiFetch( {
					method: 'POST',
					path: '/rankmath/v1/setupWizard/getStepData',
					data: { step },
				} )
					.catch( ( error ) => {
						alert( error.message )
					} )
					.then( ( response ) => {
						dispatch( 'rank-math-setup-wizard' ).updateStepData( step, response )
					} )
			},
			getNextStep() {
				let nextStep = 'role'
				if ( currentStep !== 'ready' ) {
					const wizardSteps = map( steps, ( { name } ) => name )
					const stepIndex = findIndex( wizardSteps, ( index ) => index === currentStep )
					nextStep = wizardSteps[ stepIndex + 1 ]
				}

				if ( ! nextStep ) {
					return
				}

				return nextStep
			},
			skipStep() {
				const nextStep = this.getNextStep()

				const page = searchParams.get( 'page' )
				const adminUrl = rankMath.adminurl.replace( window.location.origin, '' )
				navigate( `${ adminUrl }?page=${ page }&step=${ nextStep }` )

				window.scrollTo( {
					top: 0,
					behavior: 'auto',
				} )
			},
			updateData( key, value ) {
				if ( isUndefined( data ) ) {
					return
				}

				data[ key ] = value
				dispatch( 'rank-math-setup-wizard' ).updateStepData( currentStep, data )
			},
			saveData( value = {} ) {
				// This is needed to update the Setup mode.
				if ( ! isEmpty( value ) ) {
					dispatch( 'rank-math-setup-wizard' ).updateStepData( currentStep, value )
				}

				apiFetch( {
					method: 'POST',
					path: '/rankmath/v1/setupWizard/updateStepData',
					data: {
						step: currentStep,
						value: data,
					},
				} )
					.catch( ( error ) => {
						alert( error.message )
					} )
					.then( ( response ) => {
						// Early Bail if current step is ready to prevent it from navigating to the next step.
						if ( currentStep === 'ready' ) {
							return
						}

						if ( ! isBoolean( response ) ) {
							// Redirects from last step (Schema Markup) to Dashboard page
							window.location = response
							return
						}
						this.skipStep()
					} )
			},
		}
	} )
)( Content )
