/**
 * Internal Dependencies
 */
import AutoUpdatePanel from './AutoUpdatePanel'
import BetaOptInPanel from './BetaOptInPanel'
import VersionControlPanel from './VersionControlPanel'

export default ( props ) => (
	<>
		<VersionControlPanel { ...props } />
		<BetaOptInPanel { ...props } />
		<AutoUpdatePanel { ...props } />
	</>
)
