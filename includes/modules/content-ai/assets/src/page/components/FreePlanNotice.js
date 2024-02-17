/**
 * External dependencies
 */
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Notice } from '@wordpress/components'

/**
 * Internal dependencies
 */
import Interpolate from '@components/Interpolate'

const getNoticeText = () => {
	return (
		<Interpolate components={ { strong: ( <strong /> ) } }>
			{ __(
				'Thank you for choosing Rank Math! {{strong}}Enjoy 750 Credits monthly for life{{/strong}} as a token of our appreciation! 🎁',
				'rank-math'
			) }
		</Interpolate>
	)
}

export default ( { isPage = false, addNotice = true } ) => {
	if ( ! rankMath.contentAIPlan || 'free' !== rankMath.contentAIPlan ) {
		return
	}

	if ( ! addNotice ) {
		return (
			<>
				{ getNoticeText() }
				<br />
			</>
		)
	}

	const className = classnames( 'rank-math-content-ai-notice', {
		'is-page': isPage,
	} )

	return (
		<Notice status={ isPage ? 'success' : 'warning' } className={ className } isDismissible={ false }>
			{ getNoticeText() }
		</Notice>
	)
}
