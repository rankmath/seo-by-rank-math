/**
 * Internal Dependencies
 */
import ErrorLog from './ErrorLog'
import SystemInfo from './SystemInfo'

export default ( props ) => (
	<>
		<SystemInfo { ...props.data } />
		<ErrorLog { ...props.data } />
	</>
)

