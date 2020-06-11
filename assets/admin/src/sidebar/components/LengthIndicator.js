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

	return (
		<span
			className={ classnames( 'length-indicator-wrapper', {
				invalid: lengthData.isInvalid,
			} ) }
		>
			<span className="length-indicator">
				<span style={ { left: lengthData.left } }></span>
			</span>
			<span className="length-count">{ lengthData.count }</span>
		</span>
	)
}

export default LengthIndicator
