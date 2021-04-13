/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { withSelect } from '@wordpress/data'
import { Notice } from '@wordpress/components'

/**
 * Internal dependencies
 */
import Interpolate from '@components/Interpolate'

const ProNotice = ( { isPro } ) => {
	if ( ! isPro ) {
		return (
			<Notice status="warning" isDismissible={ false }>
				<Interpolate
					components={ {
						link: (
							<a
								href={ rankMath.assessor.futureSeo }
								target="_blank"
								rel="noopener noreferrer"
							/>
						),
						strong: (
							<strong />
						),
					} }
				>
					{ __(
						'Want more? {{link}}{{strong}}Upgrade today to the PRO{{/strong}}{{/link}} version.',
						'rank-math'
					) }
				</Interpolate>
			</Notice>
		)
	}

	if ( ! rankMath.isUserRegistered ) {
		return (
			<Notice status="warning" isDismissible={ false }>
				<Interpolate
					components={ {
						link: (
							<a
								href={ rankMath.adminurl + '?page=rank-math&view=help' }
								target="_blank"
								rel="noopener noreferrer"
							/>
						),
					} }
				>
					{ __(
						'Activate your account by {{link}}connecting to Rank Math!{{/link}} ',
						'rank-math'
					) }
				</Interpolate>
			</Notice>
		)
	}

	return (
		<Notice status="warning" isDismissible={ false }>
			<Interpolate
				components={ {
					link: (
						<a
							href="https://rankmath.com/kb/score-100-in-tests/?utm_source=Plugin&utm_medium=Gutenberg%20General%20Tab%20Score%20Notice&utm_campaign=WP"
							target="_blank"
							rel="noopener noreferrer"
						/>
					),
				} }
			>
				{ __(
					'Read here to {{link}}Score 100/100{{/link}} ',
					'rank-math'
				) }
			</Interpolate>
		</Notice>
	)
}

export default withSelect( ( select ) => {
	return { isPro: select( 'rank-math' ).isPro() }
} )( ProNotice )
