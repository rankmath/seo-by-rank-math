/**
 * External dependencies
 */
import classnames from 'classnames'
import { get as _get } from 'lodash'

/**
 * Internal dependencies
 */
import lengthIndicator from '@helpers/LengthIndicator'

class LengthIndicator {
	check( elem, settings ) {
		this.elem = elem
		if ( ! this.elem.closest('.cmb-td').find( '.length-indicator-wrapper' ).length ) {
			this.wrapIndicator( settings )
		}

		this.updateLength( settings )
	}

	wrapIndicator( settings ) {
		this.elem
			.closest('.cmb-td')
			.append(
				'<span class="' + classnames( 'length-indicator-wrapper', {
					'width-indicator': _get( settings, 'pixelWidth', false ) !== false,
				} ) + '"><span class="length-count">0 / ' +
					settings.max +
					'</span><span class="length-indicator"><span></span></span></span>'
			)
	}

	updateLength( settings ) {
		const indicator = this.elem.closest('.cmb-td').find( '.length-indicator' ),
			progress = indicator.find( '>span' ),
			counter = this.elem.closest('.cmb-td').find( '.length-count' )

		const lengthData = lengthIndicator( settings.text, settings )
		indicator.removeClass( 'invalid short' )
		progress.css( 'left', lengthData.left )
		counter.text( lengthData.count )
		if ( _get( settings, 'pixelWidth', false ) !== false ) {
			counter.text( counter.text() + ' (' + lengthData.pixelWidth + ')' )
		}

		// Short and Long
		if ( lengthData.isInvalid || lengthData.isInvalidWidth ) {
			indicator.addClass( 'invalid' )
		}
	}
}

export default LengthIndicator
