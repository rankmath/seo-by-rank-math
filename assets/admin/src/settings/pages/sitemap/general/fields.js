/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

const { sitemapUrl, isWPMLActive } = rankMath

export default [
	{
		type: 'notice',
		status: 'info',
		children: (
			<>
				{ __( 'Your sitemap index can be found here: ', 'rank-math' ) }
				<a href={ sitemapUrl } target="_blank" rel="noreferrer">
					{ sitemapUrl }
				</a>
			</>
		),
	},
	...( isWPMLActive
		? [
			{
				id: 'multilingual_sitemap_notice',
				type: 'notice',
				status: 'warning',
				children: (
					<>
						{ __(
							'Rank Math generates the default Sitemaps only and WPML takes care of the rest. When a search engine bot visits any post/page, it is shown hreflang tag that helps it crawl the translated pages. This is one of the recommended methods by Google. Please ',
							'rank-math'
						) }
						<a
							href="https://support.google.com/webmasters/answer/189077?hl=en"
							target="blank"
						>
							{ __( 'read here', 'rank-math' ) }
						</a>
					</>
				),
			},
		]
		: []
	),
	{
		id: 'items_per_page',
		type: 'text',
		name: __( 'Links Per Sitemap', 'rank-math' ),
		desc: __( 'Max number of links on each sitemap page.', 'rank-math' ),
		attributes: {
			type: 'number',
		},
		default: '200',
		classes: 'rank-math-advanced-option',
	},
	{
		id: 'include_images',
		type: 'toggle',
		name: __( 'Images in Sitemaps', 'rank-math' ),
		desc: __(
			'Include reference to images from the post content in sitemaps. This helps search engines index the important images on your pages.',
			'rank-math'
		),
		default: true,
	},
	{
		id: 'include_featured_image',
		type: 'toggle',
		name: __( 'Include Featured Images', 'rank-math' ),
		desc: __(
			'Include the Featured Image too, even if it does not appear directly in the post content.',
			'rank-math'
		),
		default: false,
		dep: {
			include_images: true,
		},
	},
	{
		id: 'exclude_posts',
		type: 'text',
		name: __( 'Exclude Posts', 'rank-math' ),
		desc: __(
			'Enter post IDs of posts you want to exclude from the sitemap, separated by commas. This option **applies** to all posts types including posts, pages, and custom post types.',
			'rank-math'
		),
		classes: 'rank-math-advanced-option',
	},
	{
		id: 'exclude_terms',
		type: 'text',
		name: __( 'Exclude Terms', 'rank-math' ),
		desc: __(
			'Add term IDs, separated by comma. This option is applied for all taxonomies.',
			'rank-math'
		),
		classes: 'rank-math-advanced-option',
	},
]
