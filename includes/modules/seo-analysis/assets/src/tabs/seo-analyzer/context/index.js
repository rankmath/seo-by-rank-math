/**
 * WordPress Dependencies
 */
import { useContext } from '@wordpress/element'
import AnalyzerContextProvider, { AnalyzerContext } from './AnalyzerContextProvider'

const useAnalyzerContext = () => {
	return useContext( AnalyzerContext )
}

export { useAnalyzerContext, AnalyzerContextProvider }
