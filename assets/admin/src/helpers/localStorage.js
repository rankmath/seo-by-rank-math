/* global localStorage */

const localCache = {
	set( key, value, expires ) {
		if ( ! key ) {
			return false
		}

		localStorage.setItem(
			key,
			JSON.stringify( {
				value,
				expires: this.expiry( expires ),
			} )
		)

		return true
	},

	get( key ) {
		if ( ! key ) {
			return false
		}

		let item = localStorage.getItem( key )
		if ( ! item ) {
			return false
		}

		item = JSON.parse( item )
		if ( item.expires && Date.now() > item.expires ) {
			localStorage.removeItem( key )
			return false
		}

		return item.value
	},

	remove( key ) {
		if ( ! key ) {
			return false
		}

		localStorage.removeItem( key )

		return true
	},

	expiry( val ) {
		if ( ! val ) {
			return false
		}

		if ( -1 === val ) {
			const d = new Date()
			d.setYear( 1970 )
			return d.getTime()
		}

		let interval = parseInt( val )
		const unit = val.replace( interval, '' )

		if ( 'd' === unit ) {
			interval = interval * 24 * 60 * 60 * 1000
		}

		if ( 'h' === unit ) {
			interval = interval * 60 * 60 * 1000
		}

		if ( 'm' === unit ) {
			interval = interval * 60 * 1000
		}

		if ( 's' === unit ) {
			interval = interval * 1000
		}

		return Date.now() + interval
	},
}

export default localCache
