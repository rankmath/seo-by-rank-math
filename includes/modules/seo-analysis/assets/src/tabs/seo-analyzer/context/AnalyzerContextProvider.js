/**
 * WordPress Dependencies
 */
import { createContext, useState } from '@wordpress/element'
import { withSelect, withDispatch } from '@wordpress/data'
import { compose } from '@wordpress/compose'

/**
 * Internal Dependencies
 */
import ajax from '@helpers/ajax'

export const AnalyzerContext = createContext()

const AnalyzerContextProvider = ( { children, startAudit, ...remainingProps } ) => {
	const [ startProgress, setStartProgress ] = useState( false )
	const [ analysisError, setAnalysisError ] = useState( '' )

	/**
	 * Start SEO Analysis
	 */
	const startAnalysis = () => {
		startAudit( setStartProgress, setAnalysisError )
	}

	return (
		<AnalyzerContext.Provider
			value={ {
				...remainingProps,
				startProgress,
				setStartProgress,
				startAnalysis,
				analysisError,
			} }
		>
			{ children }
		</AnalyzerContext.Provider>
	)
}

export default compose(
	withSelect( ( select ) => {
		const { getResults, getUrl } = select( 'rank-math-seo-analysis' )

		return {
			results: getResults(),
			url: getUrl(),
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			/**
			 * Updates the URL based on the new value entered.
			 *
			 * @param {string} url - The new URL value.
			 */
			updateUrl( url ) {
				dispatch( 'rank-math-seo-analysis' ).updateUrl( url )
			},
			/**
			 * Updates the Results based on the new value entered.
			 *
			 * @param {string} results - The new Results value.
			 */
			updateResults( results ) {
				dispatch( 'rank-math-seo-analysis' ).updateResults( results )
			},
			/**
			 * Perform an SEO audit for the given URL.
			 *
			 * @param {Function} setStartProgress Function to update the state indicating the progress of the request.
			 * @param {Function} setAnalysisError Function to update the state with any error received from the request.
			 */
			startAudit( setStartProgress, setAnalysisError ) {
				setStartProgress( true )

				ajax( 'analyze' ).always( ( response ) => {
					if ( response.error ) {
						setAnalysisError( response.error )
					} else {
						dispatch( 'rank-math-seo-analysis' ).updateResults( response )
					}
					setStartProgress( false )
				} )
			},
		}
	} )
)( AnalyzerContextProvider )
