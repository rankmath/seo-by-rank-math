/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Breadcrumbs component
 *
 * @param {Object} props      Component props.
 * @param {string} props.page Current page.
 */
export default ( { page } ) => {
	const pageTitles = {
		modules: __( 'Modules', 'rank-math' ),
		help: __( 'Help', 'rank-math' ),
		version_control: __( 'Version Control', 'rank-math' ),
		general: __( 'SEO Settings', 'rank-math' ),
		titles: __( 'SEO Titles & Meta', 'rank-math' ),
		sitemap: __( 'Sitemap Settings', 'rank-math' ),
		instantIndexing: __( 'Instant Indexing', 'rank-math' ),
		tools: __( 'Tools', 'rank-math' ),
		status: __( 'Status', 'rank-math' ),
		import_export: __( 'Import & Export', 'rank-math' ),
		role_manager: __( 'Role Manager', 'rank-math' ),
		analytics: __( 'Analytics', 'rank-math' ),
		content_ai: __( 'Content AI', 'rank-math' ),
		seo_analyzer: __( 'SEO Analyzer', 'rank-math' ),
		competitor_analyzer: __( 'Competitor Analyzer', 'rank-math' ),
		side_by_side: __( 'Competitor Analyzer', 'rank-math' ),
		'instant-indexing': __( 'Instant Indexing', 'rank-math' ),
	}
	return (
		<div className="rank-math-breadcrumbs-wrap">
			<div className="rank-math-breadcrumbs">
				<span>{ __( 'Dashboard', 'rank-math' ) }</span>
				<span className="divider">/</span>
				<span className="active">{ pageTitles[ page ] }</span>
			</div>
		</div>
	)
}
