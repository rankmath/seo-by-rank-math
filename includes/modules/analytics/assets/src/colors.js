import { get } from 'lodash'

const colors = {
	pageviews: '#10AC84',
	impressions: '#4e8cde',
	clicks: '#EE5353',
	keywords: '#FF9F43',
	ctr: '#F368E0',
	position: '#54A0FF',
	adsense: '#54A0FF',
}

export default ( id ) => {
	return get( colors, id, '#999999' )
}
