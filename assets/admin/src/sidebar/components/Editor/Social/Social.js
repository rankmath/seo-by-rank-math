/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { createElement, Fragment } from '@wordpress/element'
import { TabPanel, Dashicon } from '@wordpress/components'

/**
 * Internal dependencies
 */
import FacebookTab from './FacebookTab'
import TwitterTab from './TwitterTab'

const EditorSocialTab = () => (
	<Fragment>
		<TabPanel
			className="rank-math-editor-social"
			activeClass="is-active"
			tabs={ [
				{
					name: 'facebook',
					title: (
						<Fragment>
							<Dashicon icon="facebook-alt" />
							{ __( 'Facebook', 'rank-math' ) }
						</Fragment>
					),
					view: FacebookTab,
					className: 'button-facebook',
				},
				{
					name: 'twitter',
					title: (
						<Fragment>
							<Dashicon icon="twitter" />
							{ __( 'Twitter', 'rank-math' ) }
						</Fragment>
					),
					view: TwitterTab,
					className: 'button-twitter',
				},
			] }
		>
			{ ( tab ) => createElement( tab.view ) }
		</TabPanel>
	</Fragment>
)

export default EditorSocialTab
