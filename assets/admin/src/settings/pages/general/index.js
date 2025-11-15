/**
 * Internal Dependencies
 */
import links from './links'
import breadcrumbs from './breadcrumbs'
import images from './images'
import webmaster from './webmaster'
import robots from './robots'
import others from './others'
import blocks from './blocks'
import woocommerce from './woocommerce'
import htaccess from './htaccess'
import redirections from './redirections'
import contentAI from './content-ai'
import Monitor from './404-monitor'
import analytics from './analytics'
import llms from './llms'

export default {
	links,
	breadcrumbs,
	images,
	webmaster,
	robots,
	llms,
	others,
	blocks,
	woocommerce,
	htaccess,
	'content-ai': contentAI,
	redirections,
	'404-monitor': Monitor,
	analytics,
}
