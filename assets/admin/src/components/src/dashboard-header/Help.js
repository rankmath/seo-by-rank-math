/**
 * External Dependencies
 */
import { isUndefined, startCase } from 'lodash'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'

export default ( { page } ) => {
	const links = {
		version_control: 'version-control',
		import_export: 'import-export-settings',
		content_ai: 'content-ai-restore-credits',
		seo_analyzer: 'seo-analysis',
		competitor_analyzer: 'competitor-analyzer',
		role_manager: 'role-manager',
		general: 'general-settings',
		titles: 'titles-meta',
		sitemap: 'sitemap-general',
		'instant-indexing': 'instant-indexing',
		analytics: 'help-analytics',
		tools: 'tools',
		status: 'status',
	}
	const helpLink = ! isUndefined( links[ page ] ) ? getLink( links[ page ], 'Admin Bar ' + startCase( links[ page ] ) ) : getLink( 'knowledgebase', 'RM Header KB Icon' )
	return (
		<a href={ helpLink } title="Rank Math Knowledge Base" target="_blank" className="button rank-math-help" rel="noreferrer">
			<i className="dashicons dashicons-editor-help"></i>
		</a>
	)
}
