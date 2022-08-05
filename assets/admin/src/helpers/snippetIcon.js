/**
 * External dependencies
 */
import { get } from 'lodash'

export function getSnippetIcon( type ) {
	const iconHash = {
		off: 'rm-icon rm-icon-schema',
		Article: 'rm-icon rm-icon-post',
		Book: 'rm-icon rm-icon-book',
		Course: 'rm-icon rm-icon-course',
		Dataset: 'rm-icon rm-icon-dataset',
		Event: 'rm-icon rm-icon-calendar',
		FactCheck: 'rm-icon rm-icon-fact-check',
		JobPosting: 'rm-icon rm-icon-job',
		Local: 'rm-icon rm-icon-local-seo',
		Movie: 'rm-icon rm-icon-movie',
		Music: 'rm-icon rm-icon-music',
		Product: 'rm-icon rm-icon-cart',
		Products: 'rm-icon rm-icon-cart',
		WooCommerceProduct: 'rm-icon rm-icon-cart',
		Recipe: 'rm-icon rm-icon-recipe',
		Restaurant: 'rm-icon rm-icon-restaurant',
		Video: 'rm-icon rm-icon-video',
		Videos: 'rm-icon rm-icon-video',
		VideoObject: 'rm-icon rm-icon-video',
		Person: 'rm-icon rm-icon-users',
		Review: 'rm-icon rm-icon-star',
		'Review snippets': 'rm-icon rm-icon-star',
		Service: 'rm-icon rm-icon-service',
		Software: 'rm-icon rm-icon-software',
		SoftwareApplication: 'rm-icon rm-icon-software',
		'Sitelinks searchbox': 'rm-icon rm-icon-search',
		FAQ: 'rm-icon rm-icon-faq',
		FAQPage: 'rm-icon rm-icon-faq',
		HowTo: 'rm-icon rm-icon-howto',
		Breadcrumbs: 'rm-icon rm-icon-redirection',
		PodcastEpisode: 'rm-icon rm-icon-podcast',
	}

	return get( iconHash, type, 'rm-icon rm-icon-schema' )
}
