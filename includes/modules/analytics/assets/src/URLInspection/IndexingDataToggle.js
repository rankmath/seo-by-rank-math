/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { withFilters } from '@wordpress/components'
import { Component } from '@wordpress/element'

class IndexingDataToggle extends Component {
	render() {
		return (
			<div className="inner-elements hidden">
				<table>
					<tbody>
						<tr>
							<td colSpan="8">
								<div className="indexing-data-wrapper">
									{ this.getStatusResult() }
									{ this.getReferringURLs() }
									{ this.getMobileData() }
									{ this.richResultsData() }

									<div id="rank-math-pro-cta" className="center">
										<div className="rank-math-cta-box blue-ticks top-20 width-50">
											<h3>{ __( 'PRO Version offers Advanced Indexing Stats', 'rank-math' ) }</h3>
											<ul>
												<li>{ __( 'Monitor metrics like Index Status, Last Crawl date, etc', 'rank-math' ) }</li>
												<li>{ __( 'All the Indexing statistics about your content in one place', 'rank-math' ) }</li>
												<li>{ __( 'Use data provided by Google instead of 3rd party tools', 'rank-math' ) }</li>
											</ul>
											<a href="https://rankmath.com/pricing/?utm_source=Plugin&utm_medium=Index%20Status%20Tab%20Toggle&utm_campaign=WP" target="_blank" rel="noreferrer" className="button button-primary is-green">{ __( 'Upgrade', 'rank-math' ) }</a>
										</div>
									</div>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		)
	}

	getStatusResult() {
		const results = [
			{
				label: 'Verdict',
				value: 'NEUTRAL',
			},
			{
				label: 'Robots Text State',
				value: 'ROBOTS_TXT_STATE_UNSPECIFIED',
			},
			{
				label: 'Indexing State',
				value: 'INDEXING_STATE_UNSPECIFIED',
			},
			{
				label: 'Last Crawl Time',
				value: '2022-01-09 05:46:12',
			},
			{
				label: 'Page Fetch State',
				value: 'PAGE_FETCH_STATE_UNSPECIFIED',
			},
			{
				label: 'Google Canonical',
				value: 'https://example.com/',
			},
			{
				label: 'User Canonical',
				value: 'https://example.com/',
			},
			{
				label: 'Sitemap',
				value: 'https://example.com/sitemap_index.xml',
			},
		]
		return (
			<div className="indexing-data status">
				<h4>Index Status Result</h4>
				{ Array.from( results ).map( ( result ) => {
					return (
						<div key={ result.label }>
							<span className="label">{ result.label }</span>
							<span className="result">{ result.value }</span>
						</div>
					)
				} ) }
			</div>
		)
	}

	getReferringURLs() {
		const referringURLs = [
			'https://example.com/test1',
			'https://example.com/test2',
		]

		return (
			<div className="indexing-data referring-urls">
				<h4>Referring URLs</h4>
				<ul>
					{ referringURLs.map( ( url, index ) => {
						return (
							<li key={ index }>
								{ url }
							</li>
						)
					} ) }
				</ul>
			</div>
		)
	}

	getMobileData() {
		return (
			<>
				<div className="indexing-data crawled">
					<h4>Crawled As</h4>
					<div>UNSPECIFIED</div>
				</div>

				<div className="indexing-data status">
					<h4>Mobile Usability Result</h4>
					<div>
						<span>Verdict</span>
						<span>Unspecified</span>
					</div>
				</div>
			</>
		)
	}

	richResultsData() {
		return (
			<div className="indexing-data detected-items">
				<h4>detectedItems</h4>
				<div className="rich-results-wrapper">
					<div className="rich-results-header">
						<h4>Rich Result Types</h4>
						<h4>Items</h4>
					</div>
					<div className="rich-results-data">
						<div className="inner-wrapper">
							<h4>Breadcrumbs</h4>
							<div className="schema-data">
								<strong>Name</strong>
								<span>Unnamed item</span>
							</div>
						</div>

						<div className="inner-wrapper">
							<h4>Review snippets</h4>
							<div className="schema-data">
								<strong>Name</strong>
								<span>Issues</span>

								<div className="sub-issues">
									<span>Unnamed item</span>
									<span className="schema-issues">
										<strong>Issue Message</strong>
										<strong>Severity</strong>
										<div className="issue-details">
											<span className="error">
												<span>Item does not support reviews</span>
												<span>ERROR</span>
											</span>
											<span className="warning">
												<span>Missing reviewed item name</span>
												<span>WARNING</span>
											</span>
										</div>
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		)
	}
}

export default withFilters( 'rankMath.analytics.IndexingDataToggle' )( IndexingDataToggle )
