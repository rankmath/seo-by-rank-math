/**
 * External dependencies
 */
import { get } from 'lodash'

const colors = {
	pageviews: '#10AC84',
	impressions: '#4e8cde',
	keywords: '#ed5e5e',
	clicks: '#FF9F43',
	ctr: '#F368E0',
	position: '#0bbde3',
	adsense: '#00A3A4',
}

export default ( id ) => {
	return get( colors, id, '#999999' )
}
