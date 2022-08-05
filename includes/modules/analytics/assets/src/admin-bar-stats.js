/**
 * External dependencies
 */
import { isUndefined, get } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Component } from '@wordpress/element'
import { applyFilters } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import { isPro } from './functions'
import ItemStat from '@scShared/ItemStat'

/**
 * Analytics Stats component.
 */
class AdminBarStats extends Component {
	/**
	 * Constructor.
	 */
	constructor() {
		super( ...arguments )
		this.data = this.props.data
	}

	/**
	 * Render this component.
	 *
	 * @return {Component} Analytics stats.
	 */
	render() {
		return (
			<>
				{ this.getContentAiScore() }
				{ this.getSeoScore() }
				{ isPro() && <div id="rank-math-analytics-site-traffic" className="rank-math-item"></div> }
				{ this.getSiteImpression() }
				{ this.getAveragePosition() }
				{ this.getIndexVerdict() }
				{ ! isPro() && this.getPageSpeed() }
				{ isPro() && ( <div id="rank-math-analytics-stats-pagespeed" className="rank-math-single-tab rank-math-item"></div> ) }
			</>
		)
	}

	getContentAiScore() {
		if ( isUndefined( this.data.contentAiScore ) ) {
			return false
		}

		return (
			<div className="rank-math-item content-ai-score">
				<h3>
					{ __( 'Content AI score', 'rank-math' ) }
					<span className="rank-math-tooltip">
						<em className="dashicons-before dashicons-editor-help"></em>
						<span>
							{ __( 'Content AI Score.', 'rank-math' ) }
						</span>
					</span>
				</h3>
				<div className="score">
					<strong>{ this.data.contentAiScore } / 100</strong>
					<div className="score-wrapper">
						<span className="score-dot" style={ { left: this.data.contentAiScore < 13 ? 13 : this.data.contentAiScore + '%' } }></span>
					</div>
				</div>
			</div>
		)
	}

	getSeoScore() {
		const score = this.data.seo_score
		const className = 'rank-math-item seo-score ' + this.getScoreClass( score )
		return (
			<div className={ className }>
				<h3>
					{ __( 'SEO Score', 'rank-math' ) }
					<span className="rank-math-tooltip">
						<em className="dashicons-before dashicons-editor-help"></em>
						<span>
							{ __( 'Rank Math\'s SEO Score', 'rank-math' ) }
						</span>
					</span>
				</h3>
				<div className="score">
					<strong>
						<span>{ score }</span>
					</strong>
				</div>
			</div>
		)
	}

	getSiteImpression() {
		if ( isUndefined( this.data.impressions ) && ! rankMath.isAnalyticsConnected ) {
			return false
		}

		const impressions = get( this.data, 'impressions', 0 )
		return (
			<div className="rank-math-item">
				<h3>
					{ __( 'Search Impressions', 'rank-math' ) }
					<span className="rank-math-tooltip">
						<em className="dashicons-before dashicons-editor-help"></em>
						<span>
							{ __( 'This is how many times your site showed up in the search results.', 'rank-math' ) }
						</span>
					</span>
				</h3>
				<div className="score">
					<ItemStat { ...impressions } />
				</div>
			</div>
		)
	}

	getAveragePosition() {
		if ( isUndefined( this.data.position ) || rankMath.isAnalyticsConnected ) {
			return false
		}

		return (
			<div className="rank-math-item">
				<h3>
					{ __( 'Average Position', 'rank-math' ) }
					<span className="rank-math-tooltip">
						<em className="dashicons-before dashicons-editor-help"></em>
						<span>
							{ __( 'This is the average position of your site in the search results.', 'rank-math' ) }
						</span>
					</span>
				</h3>
				<div className="score">
					<ItemStat { ...this.data.position } revert={ true } />
				</div>
			</div>
		)
	}

	getIndexVerdict() {
		return applyFilters(
			'rank-math-analytics-stats-index-verdict',
			(
				<div className="rank-math-item blur index-status">
					<h3>
						{ __( 'Index Status', 'rank-math' ) }
						<span className="rank-math-tooltip">
							<em className="dashicons-before dashicons-editor-help"></em>
							<span>
								{ __( 'URL Inspection Status', 'rank-math' ) }
							</span>
						</span>
					</h3>
					<div className="verdict">
						<i className="indexing_state verdict indexing allowed undefined"></i>
						<span>undefined</span>
					</div>
				</div>
			),
			this.data
		)
	}

	getPageSpeed() {
		return (
			<>
				<div id="rank-math-analytics-stats-pagespeed" className="rank-math-single-tab rank-math-item blur">
					<div className="rank-math-box rank-math-pagespeed-box">
						<div className="rank-math-pagespeed-header">
							<h3>
								{ __( 'PageSpeed', 'rank-math' ) }
								<span className="rank-math-tooltip">
									<em className="dashicons-before dashicons-editor-help"></em>
								</span>
							</h3>
							<span>April 2, 2022</span>
						</div>
						<div className="grid">
							<div className="col pagespeed-desktop">
								<i className="rm-icon rm-icon-desktop"></i>
								<strong className="pagespeed interactive-good">0 s</strong>
								<small className="pagescore score-bad">0</small>
							</div>
							<div className="col pagespeed-mobile">
								<i className="rm-icon rm-icon-mobile"></i>
								<strong className="pagespeed interactive-good">0 s</strong>
								<small className="pagescore score-bad">0</small>
							</div>
						</div>
					</div>
				</div>
			</>
		)
	}

	getScoreClass( score ) {
		if ( score > 80 ) {
			return 'great'
		}

		if ( score > 50 && score < 81 ) {
			return 'good'
		}

		return 'bad'
	}
}

export default AdminBarStats
