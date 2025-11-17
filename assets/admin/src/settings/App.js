/**
 * Internal Dependencies
 */
import { DashboardHeader } from '@rank-math/components'
import UnlockProNotice from './components/UnlockProNotice'
import SettingsPanel from './components/SettingsPanel'

export default () => {
	return (
		<>
			<DashboardHeader page={ rankMath.optionPage } />

			<UnlockProNotice />
			<SettingsPanel />
		</>
	)
}
