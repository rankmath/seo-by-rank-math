/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { Button } from '@rank-math/components'

/**
 * Connecting Rank Math benefits
 */
export default () => (
	<div
		id="setting-panel-content-ai"
		className="rank-math-tab rank-math-options-panel-content exclude"
	>
		<div className="wp-core-ui rank-math-ui connect-wrap">
			<Button href={ rankMath.activateUrl } variant="animate">
				{ __( 'Connect Your Rank Math Account', 'rank-math' ) }
			</Button>
		</div>

		<div id="rank-math-pro-cta" className="content-ai-settings">
			<div className="rank-math-cta-box width-100 no-shadow no-padding no-border">
				<h3>{ __( 'Benefits of Connecting Rank Math Account', 'rank-math' ) }</h3>
				<ul>
					<li>{ __( 'Gain Access to 40+ Advanced AI Tools.', 'rank-math' ) }</li>
					<li>
						{ __(
							'Experience the Revolutionary AI-Powered Content Editor.',
							'rank-math'
						) }
					</li>
					<li>
						{ __(
							'Engage with RankBot, Our AI Chatbot, For SEO Advice.',
							'rank-math'
						) }
					</li>
					<li>
						{ __(
							"Escape the Writer's Block Using AI to Write Inside WordPress.",
							'rank-math'
						) }
					</li>
				</ul>
			</div>
		</div>
	</div>
)
