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

// Free plan Notice text.
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

/**
 * Add a Notice when site is on a Free Content AI Plan
 *
 * @param {Object}  props                 Component props.
 * @param {boolean} props.isContentAIPage Whether the page is a Content AI page
 * @param {boolean} props.addNotice       Whether to add a notice with Notice component. On Write tab we want to show a notice without a Notice compoennt.
 */
export default ( { isContentAIPage = false, addNotice = true } ) => {
	if ( ! rankMath.contentAI.plan || 'free' !== rankMath.contentAI.plan ) {
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
		'is-page': isContentAIPage,
	} )

	return (
		<Notice status={ isContentAIPage ? 'success' : 'warning' } className={ className } isDismissible={ false }>
			{ getNoticeText() }
		</Notice>
	)
}
