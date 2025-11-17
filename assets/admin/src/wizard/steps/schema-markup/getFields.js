/**
 * External Dependencies
 */
import { forEach, values } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __, sprintf } from '@wordpress/i18n'

/**
 * Internal Dependencies
 */
import getLink from '@helpers/getLink'
import getFieldArgs from './helpers/getFieldArgs'

/**
 * Schema Markup fields.
 *
 * @param {Object} data
 */
export default ( data ) => {
	const fields = [
		{
			id: 'rich_snippet',
			type: 'toggle',
			name: __( 'Schema Type', 'rank-math' ),
			desc: __(
				"Use automatic structured data to mark up content, to help Google better understand your content's context for display in Search. You can set different defaults for your posts here.",
				'rank-math'
			),
		},
	]

	forEach( values( data.accessiblePostTypes ), ( postType ) => {
		if ( postType === 'attachment' ) {
			return
		}

		fields.push( getFieldArgs( postType, data.schemaTypes ) )

		const articleDep = {
			relation: 'and',
			rich_snippet: true,
			[ `pt_${ postType }_default_rich_snippet` ]: 'article',
		}

		const articleDesc =
			data.knowledgegraph_type === 'person'
				? `<div class="notice notice-warning inline rank-math-notice" style="margin-left:0;color:#242628;"><p>${ sprintf(
					/* translators: Google article snippet doc link */
					__(
						'Google does not allow Person as the Publisher for articles. Organization will be used instead. You can read more about this <a href="%s" target="_blank">here</a>.',
						'rank-math'
					),
					getLink( 'google-article-schema' )
				) }</p></div>`
				: undefined

		fields.push( {
			id: `pt_${ postType }_default_article_type`,
			type: 'radio_inline',
			name: __( 'Article Type', 'rank-math' ),
			options: {
				Article: __( 'Article', 'rank-math' ),
				BlogPosting: __( 'Blog Post', 'rank-math' ),
				NewsArticle: __( 'News Article', 'rank-math' ),
			},
			dep: articleDep,
			desc: articleDesc,
		} )
	} )

	return fields
}
