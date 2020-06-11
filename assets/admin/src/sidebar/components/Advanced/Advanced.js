/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element'
import { PanelBody } from '@wordpress/components'

/**
 * Internal dependencies
 */
import Robots from './Robots'
import Redirect from './Redirect'
import Canonical from './Canonical'
import Breadcrumb from './Breadcrumb'
import AdvancedRobots from './AdvancedRobots'
import FrontEndScore from './FrontEndScore'

const AdvancedTab = () => (
	<Fragment>
		<PanelBody initialOpen={ true }>
			<Robots />
		</PanelBody>

		<PanelBody initialOpen={ true }>
			<AdvancedRobots />
		</PanelBody>

		<PanelBody initialOpen={ true }>
			<Canonical />
		</PanelBody>

		{ rankMath.assessor.hasBreadcrumb && (
			<PanelBody initialOpen={ true }>
				<Breadcrumb />
			</PanelBody>
		) }

		{ rankMath.assessor.hasRedirection && (
			<PanelBody initialOpen={ true } className="rank-math-redirect">
				<Redirect />
			</PanelBody>
		) }

		{ rankMath.frontEndScore && (
			<PanelBody initialOpen={ true }>
				<FrontEndScore />
			</PanelBody>
		) }
	</Fragment>
)

export default AdvancedTab
