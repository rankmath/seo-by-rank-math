/**
 * External Dependencies
 */
import { useSearchParams } from 'react-router-dom'

/**
 * Internal Dependencies
 */
import tabs from './tabs'
import App from './App'

export default () => {
	const [ searchParams, setSearchParams ] = useSearchParams( {
		view: tabs[ 0 ].name,
	} )

	return <App searchParams={ searchParams } setSearchParams={ setSearchParams } />
}
