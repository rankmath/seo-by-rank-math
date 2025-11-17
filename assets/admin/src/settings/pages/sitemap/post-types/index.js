/**
 * External dependencies
 */
import { forEach, lowerCase, values } from 'lodash'

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'
import sitemapPostTypesFields from './fields'

const { accessiblePostTypes, postTypes, choicesPostTypeIcons } = rankMath.choices

/**
 * Add post type tabs in the Sitemap Settings options panel.
 */
const postTypeSettings = () => {
	const icons = choicesPostTypeIcons

	const names = {
		attachment: __( 'attachments', 'rank-math' ),
		product: __( 'your product pages', 'rank-math' ),
	}

	const links = {
		post: getLink( 'sitemap-post', 'Options Panel Sitemap Posts Tab' ),
		page: getLink( 'sitemap-page', 'Options Panel Sitemap Page Tab' ),
		attachment: getLink(
			'sitemap-media',
			'Options Panel Sitemap Attachments Tab'
		),
		product: getLink( 'sitemap-product', 'Options Panel Sitemap Product Tab' ),
	}

	const postTypeFields = {}

	const postTypePages = {
		p_types: {
			// Post type label seprator.
			name: 'p_types',
			title: __( 'Post Types:', 'rank-math' ),
			className: 'separator',
			disabled: true,
		},
	}

	forEach( values( accessiblePostTypes ), ( postType ) => {
		const label = postTypes[ postType ]

		const fields = sitemapPostTypesFields( postType )
		postTypeFields[ postType ] = fields

		const icon = icons[ postType ] || icons.default
		const title = 'attachment' === postType ? __( 'Attachments', 'rank-math' ) : label
		const link = links[ postType ] || getLink( 'configure-sitemaps' )
		const postTypeName =
			names[ postType ] ||
			sprintf(
				/* translators: Post Type label */
				__( 'single %s', 'rank-math' ),
				lowerCase( label )
			)

		postTypePages[ 'sitemap-post-type-' + postType ] = {
			fields,
			name: 'sitemap-post-type-' + postType,
			title: (
				<>
					<i className={ icon }></i>
					{ title }
				</>
			),
			header: {
				title,
				description: sprintf(
					/* translators: 1. post type name */
					__( 'Change Sitemap settings of %s.', 'rank-math' ),
					postTypeName
				),
				link,
			},
			className: `rank-math-sitemap-post-type-${ postType }-tab rank-math-tab`,
		}
	} )

	return postTypePages
}

export default postTypeSettings()
