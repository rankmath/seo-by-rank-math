/**
 * External Dependencies
 */
import { map, entries } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'

export default ( data ) => {
	const postTypes = map( entries( data.postTypes ), ( [ id, label ] ) => ( { id, label } ) )
	const taxonomies = map( entries( data.taxonomies ), ( [ id, label ] ) => ( { id, label } ) )

	return applyFilters(
		'rank_math_setup_wizard_sitemap_fields',
		[
			{
				id: 'sitemap',
				type: 'toggle',
				name: __( 'Sitemaps', 'rank-math' ),
				desc: __(
					'XML Sitemaps help search engines index your website&#039;s content more effectively.',
					'rank-math'
				),
			},
			{
				id: 'include_images',
				type: 'toggle',
				name: __( 'Include Images', 'rank-math' ),
				desc: __(
					'Include reference to images from the post content in sitemaps. This helps search engines index your images better.',
					'rank-math'
				),
				classes: 'features-child',
				dep: { sitemap: true },
			},
			{
				id: 'sitemap_post_types',
				type: 'multicheck',
				name: __( 'Public Post Types', 'rank-math' ),
				desc: __(
					'Select post types to enable SEO options for them and include them in the sitemap.',
					'rank-math'
				),
				options: postTypes,
				classes: 'features-child field-multicheck-inline multicheck-checked',
				dep: { sitemap: true },
				toggleAll: true,
			},
			{
				id: 'sitemap_taxonomies',
				type: 'multicheck',
				name: __( 'Public Taxonomies', 'rank-math' ),
				desc: __(
					'Select taxonomies to enable SEO options for them and include them in the sitemap.',
					'rank-math'
				),
				options: taxonomies,
				classes: 'features-child field-multicheck-inline multicheck-checked',
				dep: { sitemap: true },
				toggleAll: true,
			},
		],
		postTypes
	)
}
