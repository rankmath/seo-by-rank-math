/**
 * Internal dependencies
 */
import lengthIndicator from '@helpers/LengthIndicator'

class LengthIndicator {
	check( elem, settings ) {
		this.elem = elem
		if ( ! this.elem.parent().hasClass( 'length-indicator-wrapper' ) ) {
			this.wrapIndicator( settings )
		}

		this.updateLength( settings )
	}

	wrapIndicator( settings ) {
		this.elem.wrap( '<span class="length-indicator-wrapper"/>' )
		this.elem
			.parent()
			.append(
				'<span class="length-indicator"><span/></span><span class="length-count">0 / ' +
					settings.max +
					'</span>'
			)
	}

	updateLength( settings ) {
		const indicator = this.elem.parent().find( '.length-indicator' ),
			progress = indicator.find( '>span' ),
			counter = this.elem.parent().find( '.length-count' )

		const lengthData = lengthIndicator( settings.text, settings )
		indicator.removeClass( 'invalid short' )
		progress.css( 'left', lengthData.left )
		counter.text( lengthData.count )

		// Short and Long
		if ( lengthData.isInvalid ) {
			indicator.addClass( 'invalid' )
		}
	}
}

export default LengthIndicator
