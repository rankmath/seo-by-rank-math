/**
 * Wordpress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { StatusButton, SocialShare } from '@rank-math/components'

/**
 * Activated account UI
 */
export default () => (
	<>
		<p>
			{ __(
				'You have successfully activated Rank Math. If you find the plugin useful, ',
				'rank-math'
			) }

			<strong>
				{ __( 'feel free to recommend it to your friends or colleagues.', 'rank-math' ) }
			</strong>

			<SocialShare />
		</p>

		<div className="frm-submit">
			<StatusButton
				type="submit"
				status="disconnect"
				className="button-xlarge"
				name="button"
			>
				{ __( 'Disconnect Account', 'rank-math' ) }
			</StatusButton>
		</div>
	</>
)
