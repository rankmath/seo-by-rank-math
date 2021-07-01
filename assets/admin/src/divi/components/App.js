/**
 * WordPress dependencies
 */
import { SlotFillProvider, withFilters } from '@wordpress/components'

/**
 * Internal dependencies
 */
import Modal from './Modal'

const FilteredModal = withFilters( 'rankMath.diviAppModal' )( Modal )

const App = () => {
	return (
		<SlotFillProvider>
			<div className="rank-math-rm-app">
				<FilteredModal />
			</div>
		</SlotFillProvider>
	)
}

export default App
