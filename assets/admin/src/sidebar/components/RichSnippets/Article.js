/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { withDispatch, withSelect } from '@wordpress/data'
import { PanelBody, RadioControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import Interpolate from '@components/Interpolate'

const ArticleSnippet = ( props ) => (
	<PanelBody initialOpen={ true }>
		<RadioControl
			label={ __( 'Article Type', 'rank-math' ) }
			selected={ props.articleType }
			options={ [
				{
					value: 'Article',
					label: __( 'Article', 'rank-math' ),
				},
				{
					value: 'BlogPosting',
					label: __( 'Blog Post', 'rank-math' ),
				},
				{
					value: 'NewsArticle',
					label: __( 'News Article', 'rank-math' ),
				},
			] }
			onChange={ props.updateArticleType }
		/>

		{ 'person' === props.knowledgegraphType && (
			<div className="components-base-control__help rank-math-notice notice notice-alt notice-warning">
				<p>
					<Interpolate components={ { link: <a href={ rankMath.assessor.articleKBLink } target="_blank" rel="noopener noreferrer" /> } }>
						{ __(
							'Google does not allow Person as the Publisher for articles. Organization will be used instead. You can read more about this {{link}}here{{/link}}.',
							'rank-math'
						) }
					</Interpolate>
				</p>
			</div>
		) }
	</PanelBody>
)

export default compose(
	withSelect( ( select ) => {
		const data = select( 'rank-math' ).getRichSnippets()

		return {
			articleType: data.articleType,
			knowledgegraphType: data.knowledgegraphType,
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updateArticleType( type ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'articleType',
					'article_type',
					type
				)
			},
		}
	} )
)( ArticleSnippet )
