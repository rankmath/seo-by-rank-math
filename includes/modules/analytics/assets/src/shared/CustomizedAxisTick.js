/**
 * External dependencies
 */
import moment from 'moment'

const CustomizedAxisTick = ( props ) => {
	const { width, height, x, y, dy, payload, index, visibleTicksCount, isFormat = true } = props

	let anchor = 'middle'
	if ( 0 === index ) {
		anchor = 'start'
	}
	if ( index === visibleTicksCount - 1 ) {
		anchor = 'end'
	}

	return (
		<g className="recharts-layer recharts-cartesian-axis-tick">
			<text
				width={ width }
				height={ height }
				x={ x }
				y={ y }
				stroke="none"
				fill="#7f868d"
				fontSize="14"
				textAnchor={ anchor }
			>
				<tspan x={ x } dy={ dy }>
					{ isFormat ? moment( payload.value ).format( 'D MMM, YYYY' ) : payload.value }
				</tspan>
			</text>
		</g>
	)
}

export default CustomizedAxisTick
