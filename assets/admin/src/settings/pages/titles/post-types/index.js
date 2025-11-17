/**
 * External dependencies
 */
import { forEach, values } from 'lodash'

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'
import titlesPostTypesFields from './fields'

const { accessiblePostTypes, postTypes } = rankMath.choices

/**
 * Add post type tabs in the Title Settings panel.
 */
const postTypeSettings = () => {
	const icons = rankMath.choices.choicesPostTypeIcons

	const links = {
		post: getLink( 'post-settings', 'Options Panel Meta Posts Tab' ),
		page: getLink( 'page-settings', 'Options Panel Meta Pages Tab' ),
		product: getLink( 'product-settings', 'Options Panel Meta Products Tab' ),
		attachment: getLink( 'media-settings', 'Options Panel Meta Attachments Tab' ),
	}

	const getNames = ( name ) => {
		return {
			post: 'single ' + name,
			page: 'single ' + name,
			product: 'product pages',
			attachment: 'media ' + name,
		}
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
		const fields = titlesPostTypesFields( postType )
		postTypeFields[ postType ] = fields

		const link = links[ postType ] || ''
		const postTypeName = getNames( postType )[ postType ] || postType
		const icon = icons[ postType ] || icons.default
		const title = 'attachment' === postType ? __( 'Attachments', 'rank-math' ) : postTypes[ postType ]

		postTypePages[ 'post-type-' + postType ] = {
			fields,
			name: 'post-type-' + postType,
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
					__(
						'Change Global SEO, Schema, and other settings for %s.',
						'rank-math'
					),
					postTypeName
				),
				link,
			},
		}
	} )

	return postTypePages
}

export default postTypeSettings()
