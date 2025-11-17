/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal Dependencies
 */
import { Table } from '@rank-math/components'

/**
 * RSS variables table
 */
export default () => {
	const fields = [
		[
			__( 'Variable', 'rank-math' ),
			__( 'Description', 'rank-math' ),
		],
		[
			__( '%AUTHORLINK%', 'rank-math' ),
			__( 'A link to the archive for the post author, with the authors name as anchor text.', 'rank-math' ),
		],
		[
			__( '%POSTLINK%', 'rank-math' ),
			__( 'A link to the post, with the title as anchor text.', 'rank-math' ),
		],
		[
			__( '%BLOGLINK%', 'rank-math' ),
			__( 'A link to your site, with your site\'s name as anchor text.', 'rank-math' ),
		],
		[
			__( '%BLOGDESCLINK%', 'rank-math' ),
			__( 'A link to your site, with your site\'s name and description as anchor text.', 'rank-math' ),
		],
		[
			__( '%FEATUREDIMAGE%', 'rank-math' ),
			__( 'Featured image of the article.', 'rank-math' ),
		],
	]

	return (
		<div className="field-row rank-math-rss-variables rank-math-exclude-from-search">
			<h3>{ __( 'Available variables', 'rank-math' ) }</h3>

			<Table fields={ fields } />
		</div>
	)
}
