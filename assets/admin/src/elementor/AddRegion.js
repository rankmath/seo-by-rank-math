/**
 * Internal dependencies
 */
import PanelSeoView from './PanelSeoView'

export default ( regions ) => {
	regions[ 'rank-math' ] = {
		region: regions.global.region,
		view: PanelSeoView,
		options: {},
	}

	return regions
}
