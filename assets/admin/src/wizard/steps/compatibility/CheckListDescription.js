/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

export default ( { allGood, isWhitelabel } ) => {
	if ( allGood ) {
		return (
			<p className="description checklist-ok">
				{ isWhitelabel
					? __(
						'Your server is correctly configured to use Rank Math.',
						'rank-math'
					)
					: __(
						'Your server is correctly configured to use this plugin.',
						'rank-math'
					) }
			</p>
		)
	}

	return (
		<p className="description checklist-not-ok">
			{ isWhitelabel
				? __(
					'Please resolve the issues above to be able to use all features of Rank Math plugin. If you are not sure how to do it, please contact your hosting provider.',
					'rank-math'
				)
				: __(
					'Please resolve the issues above to be able to use all SEO features. If you are not sure how to do it, please contact your hosting provider.',
					'rank-math'
				) }
		</p>
	)
}
