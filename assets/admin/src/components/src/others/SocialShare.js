/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { addQueryArgs } from '@wordpress/url'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'

export default ( { isWhitelabel } ) => {
	if ( isWhitelabel ) {
		return
	}

	// Get Twitter and Facebook share links.
	const twLink = getLink( 'logo', 'Setup Wizard Tweet Button' )
	const fbLink = getLink( 'logo', 'Facebook' )

	// Construct Twitter and Facebook share messages.
	const twMessage = sprintf(
		/* translators: sitename */
		__(
			'I just installed @RankMathSEO #WordPress Plugin. It looks great! %s',
			'rank-math'
		),
		twLink
	)
	const fbMessage = __(
		'I just installed Rank Math SEO WordPress Plugin. It looks promising!',
		'rank-math'
	)

	// Construct Facebook share URL with parameters.
	const fbShareUrl = addQueryArgs(
		'https://www.facebook.com/sharer/sharer.php',
		{
			u: fbLink,
			quote: fbMessage,
			caption: __( 'SEO by Rank Math', 'rank-math' ),
		}
	)
	const tweetUrl = addQueryArgs( 'https://twitter.com/intent/tweet', {
		text: twMessage,
		hashtags: 'SEO',
	} )

	// Open Facebook or Twitter share window
	const openShare = ( event, url ) => {
		event.preventDefault()
		window.open( url, 'sharewindow', 'resizable,width=600,height=300' )
	}

	return (
		<span className="wizard-share">
			<a
				href="##"
				onClick={ ( event ) => openShare( event, tweetUrl ) }
				className="share-twitter"
			>
				<span className="dashicons dashicons-twitter" />{ ' ' }
				{ __( 'Tweet', 'rank-math' ) }
			</a>
			<a
				href="##"
				onClick={ ( event ) => openShare( event, fbShareUrl ) }
				className="share-facebook"
			>
				<span className="dashicons dashicons-facebook-alt" />{ ' ' }
				{ __( 'Share', 'rank-math' ) }
			</a>
		</span>
	)
}
