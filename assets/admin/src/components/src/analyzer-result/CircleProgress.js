export default ( { size, strokeWidth, value, max } ) => {
	const radius = ( size - strokeWidth ) / 2
	const viewBox = `0 0 ${ size } ${ size }`
	const dashArray = radius * Math.PI * 2
	const dashOffset = dashArray - ( ( dashArray * value ) / max )

	const strokeColor = () => {
		if ( value > 70 ) {
			return '#10ac84'
		}

		if ( value > 50 ) {
			return '#ff9f43'
		}

		return '#ed5e5e'
	}

	return (
		<svg width={ size } height={ size } viewBox={ viewBox }>
			<circle
				fill={ 'none' }
				stroke={ '#e9e9ea' }
				cx={ size / 2 }
				cy={ size / 2 }
				r={ radius }
				strokeWidth={ `${ strokeWidth }px` }
			/>
			<circle
				className="circle-progress"
				fill={ 'none' }
				stroke={ strokeColor() }
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeDasharray={ dashArray }
				strokeDashoffset={ dashOffset }
				cx={ size / 2 }
				cy={ size / 2 }
				r={ radius }
				strokeWidth={ `${ strokeWidth }px` }
				transform={ `rotate(-90 ${ size / 2 } ${ size / 2 })` }
				style={ {
					animation: `dashAnimation 1s ease-in-out forwards`,
				} }
			/>
			<style>
				{ `
					@keyframes dashAnimation {
						from {
							stroke-dashoffset: ${ dashArray };
						}
						to {
							stroke-dashoffset: ${ dashOffset };
						}
					}
				` }
			</style>
		</svg>
	)
}
