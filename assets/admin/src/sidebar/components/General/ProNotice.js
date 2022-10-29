/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { withSelect } from '@wordpress/data'
import { Notice } from '@wordpress/components'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'
import Interpolate from '@components/Interpolate'

const ProNotice = ( { isPro } ) => {
	if ( ! isPro ) {
		return (
			<Notice status="warning" isDismissible={ false }>
				<Interpolate
					components={ {
						link: (
							<a
								href={ getLink( 'pro', 'Gutenberg General Tab Notice' ) }
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
							href={ getLink( 'score-100', 'General Tab Score Notice' ) }
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
