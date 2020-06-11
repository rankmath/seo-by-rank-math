/**
 * External dependencies
 */
import $ from 'jquery'

/**
 * Internal dependencies
 */
import DataCollector from './DataCollector'

class UserCollector extends DataCollector {
	setup() {
		this.elemSlug = $( '#rank_math_permalink' )
		this.elemTitle = $( '#display_name' )
		this.elemDescription = $( '#description' )
	}

	getContent() {
		return this.elemDescription.val()
	}
}

export default UserCollector
