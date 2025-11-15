/*
* WordPress dependencies
*/
import { applyFilters } from '@wordpress/hooks'

/*
* Internal dependencies
*/
import Breadcrumbs from './Breadcrumbs'
import Logo from './Logo'
import Title from './Title'
import ModeSelector from './ModeSelector'
import Help from './Help'
import Search from './search/index'

export default ( { onTabChange, page = '' } ) => {
	return (
		<>
			<div className="rank-math-header">
				<Logo />
				<Title />
				<Search page={ page } onTabChange={ onTabChange } />
				<ModeSelector page={ page } />
				{ applyFilters( 'rank_math_before_help_link', '' ) }
				<Help page={ page } />
			</div>
			<Breadcrumbs page={ page } />
		</>
	)
}
