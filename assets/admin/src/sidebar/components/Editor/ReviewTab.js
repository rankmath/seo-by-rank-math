/**
 * External dependencies
 */

import jQuery from 'jquery'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment, Component } from '@wordpress/element'
import { CheckboxControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import Interpolate from '@components/Interpolate'

class ReviewTab extends Component {
	/**
	 * Class constructor
	 */
	constructor() {
		super()
		this._handleRef = this._handleRef.bind( this )
	}

	componentDidMount() {
		const ratingWrapper = jQuery( this.component ),
			ratingStars = ratingWrapper.find( '.stars a' ),
			ratingSmiley = ratingWrapper.find( '.smiley' )

		ratingStars.on( 'mouseenter', function() {
			const pos = jQuery( this ).index()

			ratingStars.removeClass( 'highlighted' )
			ratingStars.slice( 0, pos + 1 ).addClass( 'highlighted' )

			if ( pos < 2 ) {
				ratingSmiley.removeClass( 'normal happy' ).addClass( 'angry' )
			} else if ( pos > 3 ) {
				ratingSmiley.removeClass( 'normal angry' ).addClass( 'happy' )
			} else {
				ratingSmiley.removeClass( 'happy angry' ).addClass( 'normal' )
			}
		} )
	}

	shouldComponentUpdate() {
		return false
	}

	_handleRef( component ) {
		this.component = component
	}

	render() {
		const stars = []
		for ( let i = 1; i <= 5; i++ ) {
			stars.push(
				<a
					key={ i }
					href="https://s.rankmath.com/wpreview"
					target="_blank"
					rel="noopener noreferrer"
				>
					<span className="dashicons dashicons-star-filled"></span>
				</a>
			)
		}

		return (
			<div className="ask-review" ref={ this._handleRef }>
				<h3>{ __( 'Rate Rank Math SEO', 'rank-math' ) }</h3>

				<p>
					<Interpolate tags="em">
						{ __(
							"Hey, we noticed you are using Rank Math SEO plugin for more than a week now –{{em}}that's awesome!{{/em}} Could you please do us a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation?",
							'rank-math'
						) }
					</Interpolate>
				</p>

				<div className="stars-wrapper">
					<div className="face">
						<div className="smiley happy">
							<div className="eyes">
								<div className="eye"></div>
								<div className="eye"></div>
							</div>
							<div className="mouth"></div>
						</div>
					</div>

					<div className="stars">{ stars }</div>
				</div>

				<CheckboxControl
					label={
						<Fragment>
							<span>
								{ __(
									"I already did. Please don't show this message again.",
									'rank-math'
								) }
							</span>
						</Fragment>
					}
					onChange={ () => this.alreadyReviewed() }
				/>
			</div>
		)
	}

	alreadyReviewed() {
		jQuery.ajax( {
			url: rankMath.ajaxurl,
			data: { action: 'rank_math_already_reviewed', security: rankMath.security,
			},
		} )

		rankMath.pluginReviewed = true
		const wrapper = jQuery( this.component )
		wrapper.animate( { opacity: 0.01 }, 1500, function() {
			const buttons = jQuery(
				'.rank-math-editor > .components-tab-panel__tabs > button'
			)
			buttons.first().click()
			buttons.last().remove()
			wrapper.remove()
		} )
	}
}

export default ReviewTab
