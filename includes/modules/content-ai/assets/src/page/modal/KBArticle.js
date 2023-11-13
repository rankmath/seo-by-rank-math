/**
 * External dependencies
 */
import { includes } from 'lodash'

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'

export default ( { title, helpLink } ) => {
	// Translators: %s is replaced with the tool title.
	const description = sprintf( __( 'Learn how to use this %s Tool effectively.', 'rank-math' ), `<strong>${ title }</strong>` )
	const hasVideo = includes( helpLink, 'play-video' )
	return (
		<div
			key="title"
			className="rank-math-video-tutorial"
		>
			<div className="info">
				<p>
					<span
						dangerouslySetInnerHTML={
							{
								__html: description,
							}
						}
					/>
				</p>
				<a className={ ! hasVideo ? 'button button-primary is-red' : '' } href={ helpLink } target="_blank" rel="noreferrer">
					{ hasVideo && <span className="rm-icon-youtube"></span> }
					{ ! hasVideo && __( 'Click Here', 'rank-math' ) }
				</a>
			</div>
		</div>
	)
}
