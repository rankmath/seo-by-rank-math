/**
 * External dependencies
 */
import { sum, round, inRange } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Component } from '@wordpress/element'
import { compose } from '@wordpress/compose'
import { withSelect } from '@wordpress/data'

class ContentAIScore extends Component {
	/**
	 * Constructor.
	 */
	constructor() {
		super( ...arguments )
		this.state = { score: 0 }
		this.setState = this.setState.bind( this )
	}

	/**
	 * Renders the component.
	 *
	 * @return {Component} ContentAIScore.
	 */
	render() {
		setTimeout( () => {
			this.setState( { score: this.props.score } )
		}, 1000 )
		return (
			<div className="rank-math-ca-score">
				<div className="score-text">{ __( 'Score:', 'rank-math' ) } { this.state.score }<span> / { __( '100', 'rank-math' ) }</span></div>
				<div className="score-wrapper">
					<span className="score-dot" style={ { left: ( inRange( this.state.score, 0, 5 ) ? 5 : this.state.score ) + '%' } }></span>
				</div>
			</div>
		)
	}
}

export default compose(
	withSelect( ( select ) => {
		const score = Object.values( select( 'rank-math' ).getContentAIScore() )
		return {
			score: round( sum( score ) / score.length ),
		}
	} )
)( ContentAIScore )
