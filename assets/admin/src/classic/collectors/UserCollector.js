/**
 * External dependencies
 */
import jQuery from 'jquery'

/**
 * Internal dependencies
 */
import DataCollector from './DataCollector'

class UserCollector extends DataCollector {
	setup() {
		this.elemSlug = jQuery( '#rank_math_permalink' )
		this.elemTitle = jQuery( '#display_name' )
		this.elemDescription = jQuery( '#description' )
	}

	getContent() {
		return this.elemDescription.val()
	}
}

export default UserCollector
