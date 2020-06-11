/**
 * External dependencies
 */
import { isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { withSelect } from '@wordpress/data'
import { Fragment } from '@wordpress/element'
import { Dashicon } from '@wordpress/components'

const PostPublish = ( { title, permalink } ) => {
	const encodedUrl = encodeURI( permalink )
	const encodedTitle = encodeURI( title )

	return (
		<Fragment>
			<p>{ __( 'Notify your readers by sharing!', 'rank-math' ) }</p>
			<div className="rank-math-social-share-buttons">
				<div className="rank-math-share-button rm-facebook">
					<a
						href={
							'https://www.facebook.com/sharer/sharer.php?u=' +
							encodedUrl
						}
						target="_blank" rel="noopener noreferrer"
					>
						<Dashicon icon="facebook-alt" />
					</a>
				</div>
				<div className="rank-math-share-button rm-twitter">
					<a
						href={
							'https://twitter.com/share?url=' +
							encodedUrl +
							'&text=' +
							encodedTitle
						}
						target="_blank"
						rel="noopener noreferrer"
					>
						<Dashicon icon="twitter" />
					</a>
				</div>
				<div className="rank-math-share-button rm-email">
					<a
						href={
							'mailto:?subject=' +
							encodedTitle +
							'&body=' +
							encodedUrl
						}
						target="_blank"
						rel="noopener noreferrer"
					>
						<Dashicon icon="email" />
					</a>
				</div>
			</div>
		</Fragment>
	)
}

export default withSelect( ( select ) => {
	const repo = select( 'rank-math' )
	const useFacebook = repo.getTwitterUseFacebook()
	let title = useFacebook ? repo.getFacebookTitle() : repo.getTwitterTitle()
	if ( isEmpty( title ) ) {
		title = repo.getSerpTitle()
	}

	return {
		title,
		permalink: select( 'core/editor' ).getPermalink(),
	}
} )( PostPublish )
