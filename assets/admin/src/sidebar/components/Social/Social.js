/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { BaseControl, PanelBody } from '@wordpress/components'

/**
 * Internal dependencies
 */
import SnippetEditor from '@components/Editor/Editor'

const SocialTab = () => (
	<PanelBody initialOpen={ true }>
		<BaseControl className="rank-math-social">
			<span className="components-base-control__label">
				{ __( 'Social Media Preview', 'rank-math' ) }
			</span>

			<p>
				{ __(
					'Here  you can view and edit the thumbnail, title and description that will be displayed when your site is shared on social media.',
					'rank-math'
				) }
			</p>
			<p>
				{ __(
					'Click on the button below to view and edit the preview.',
					'rank-math'
				) }
			</p>

			<SnippetEditor
				buttonLabel={ __( 'Preview & Edit Social Media', 'rank-math' ) }
				initialTab="social"
			/>
		</BaseControl>
	</PanelBody>
)

export default SocialTab
