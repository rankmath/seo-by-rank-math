/**
 * External dependencies
 */
import classnames from 'classnames'

/**
 * Internal dependencies
 */
import lengthIndicator from '@helpers/LengthIndicator'

const LengthIndicator = ( props ) => {
	const lengthData = lengthIndicator( props.source, props )
	const lengthpixelWidth = lengthData.pixelWidth ? ` (${ lengthData.pixelWidth })` : ''

	return (
		<span
			className={ classnames( 'length-indicator-wrapper', {
				invalid: lengthData.isInvalid || lengthData.isInvalidWidth,
			} ) }
		>
			<span className="length-count">
				{ lengthData.count }
				{ lengthpixelWidth }
			</span>
			<span className="length-indicator">
				<span style={ { left: lengthData.left } }></span>
			</span>
		</span>
	)
}

export default LengthIndicator
