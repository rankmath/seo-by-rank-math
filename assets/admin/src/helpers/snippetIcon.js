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
		Event: 'rm-icon rm-icon-calendar',
		JobPosting: 'rm-icon rm-icon-job',
		Local: 'rm-icon rm-icon-local-seo',
		Music: 'rm-icon rm-icon-music',
		Product: 'rm-icon rm-icon-cart',
		WooCommerceProduct: 'rm-icon rm-icon-cart',
		Recipe: 'rm-icon rm-icon-recipe',
		Restaurant: 'rm-icon rm-icon-restaurant',
		Video: 'rm-icon rm-icon-video',
		Person: 'rm-icon rm-icon-users',
		Review: 'rm-icon rm-icon-star',
		Service: 'rm-icon rm-icon-service',
		Software: 'rm-icon rm-icon-software',
	}

	return get( iconHash, type, 'rm-icon rm-icon-schema' )
}
