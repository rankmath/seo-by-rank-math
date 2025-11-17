/**
 * Internal Dependencies
 */
import general from './general'
import HTMLSitemap from './html_sitemap'
import authors from './authors'
import postTypes from './post-types'
import taxonomies from './taxonomies'

export default {
	general,
	html_sitemap: HTMLSitemap,
	authors,
	...postTypes,
	...taxonomies,
}
