/**
 * Internal Dependencies
 */
import global from './global'
import local from './local'
import social from './social'
import homepage from './homepage'
import author from './author'
import misc from './misc'
import postTypes from './post-types'
import taxonomies from './taxonomies'
import buddypress from './buddypress'

export default {
	global,
	local,
	social,
	homepage,
	author,
	misc,
	...postTypes,
	...taxonomies,
	...buddypress,
}
