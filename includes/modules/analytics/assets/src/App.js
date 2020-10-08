/**
 * External dependencies
 */
import { map } from 'lodash'
import classnames from 'classnames'
import { HashRouter as Router, NavLink, Route, Switch } from 'react-router-dom'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'
import { Fragment } from '@wordpress/element'

/**
 * Internal dependencies
 */
import Analytics from './Analytics/Analytics'
import Dashboard from './Dashboard/Dashboard'
// import Sitemap from './Sitemap/Sitemap'
import Performance from './Performance/Performance'
import Keywords from './Keywords/Keywords'
import Single from './Single/Single'
// import CrawlErrors from './CrawlErrors/CrawlErrors'

const getTabs = () => {
	const tabs = []

	tabs.push( {
		path: '/',
		title: (
			<Fragment>
				<i
					className="rm-icon rm-icon-acf"
					title={ __( 'Dashboard', 'rank-math' ) }
				></i>
				<span>{ __( 'Dashboard', 'rank-math' ) }</span>
			</Fragment>
		),
		view: Dashboard,
		className: 'rank-math-dashboard-tab',
	} )

	tabs.push( {
		path: '/analytics/:paged',
		link: '/analytics/1',
		exact: false,
		title: (
			<Fragment>
				<i
					className="rm-icon rm-icon-analytics"
					title={ __( 'Site Analytics', 'rank-math' ) }
				></i>
				<span>{ __( 'Site Analytics', 'rank-math' ) }</span>
			</Fragment>
		),
		view: Analytics,
		className: 'rank-math-analytics-tab',
	} )

	tabs.push( {
		path: '/performance/:paged',
		link: '/performance/1',
		exact: false,
		title: (
			<Fragment>
				<i
					className="rm-icon rm-icon-analyzer"
					title={ __( 'SEO Performance', 'rank-math' ) }
				></i>
				<span>{ __( 'SEO Performance', 'rank-math' ) }</span>
			</Fragment>
		),
		view: Performance,
		className: 'rank-math-performance-tab',
	} )

	tabs.push( {
		path: '/keywords/:paged',
		link: '/keywords/1',
		exact: false,
		title: (
			<Fragment>
				<i
					className="rm-icon rm-icon-faq"
					title={ __( 'Keywords', 'rank-math' ) }
				></i>
				<span>{ __( 'Keywords', 'rank-math' ) }</span>
			</Fragment>
		),
		view: Keywords,
		className: 'rank-math-keywords-tab',
	} )

	tabs.push( {
		path: '/single/:id',
		view: Single,
		className: 'rank-math-single-tab',
	} )

	/* Temporary tab disabled for styling
	tabs.push( {
		name: 'sitemaps',
		title: (
			<Fragment>
				<i
					className="rm-icon rm-icon-toolbox"
					title={ __( 'Sitemap', 'rank-math' ) }
				></i>
				<span>{ __( 'Sitemap', 'rank-math' ) }</span>
			</Fragment>
		),
		view: Sitemap,
		className: 'rank-math-sitemaps-tab',
	} )

	tabs.push( {
		name: 'crawlErrors',
		title: (
			<Fragment>
				<i
					className="rm-icon rm-icon-toolbox"
					title={ __( 'Crawl Errors', 'rank-math' ) }
				></i>
				<span>{ __( 'Crawl Errors', 'rank-math' ) }</span>
			</Fragment>
		),
		view: CrawlErrors,
		className: 'rank-math-crawl-errors-tab',
	} )*/

	return applyFilters( 'rank_math_search_console_tabs', tabs )
}

const App = () => {
	const tabs = getTabs()

	return (
		<Router>
			<div className="rank-math-tabs horizontal">
				<div
					className="rank-math-tab-nav"
					role="tablist"
					aria-orientation="horizontal"
				>
					{ map(
						tabs,
						( {
							path,
							title = false,
							exact = true,
							link = false,
						} ) => {
							if ( false === title ) {
								return null
							}

							return (
								<NavLink
									exact={ exact }
									className="rank-math-tab"
									activeClassName="is-active"
									key={ path }
									to={ link ? link : path }
								>
									{ title }
								</NavLink>
							)
						}
					) }

					{ '' !== rankMath.lastUpdated && (
						<div className="rank-math-updated">{ __( 'Last updated on:', 'rank-math' ) } { rankMath.lastUpdated }</div>
					) }
				</div>
				<Switch>
					{ map(
						tabs,
						( {
							path,
							view: Component,
							exact = true,
							className,
						} ) => {
							return (
								<Route
									exact={ exact }
									key={ path }
									path={ path }
									render={ ( props ) => {
										const wrapper = classnames(
											'rank-math-tab-content',
											className
										)
										return (
											<div className={ wrapper }>
												<Component { ...props } />
												<p className="rank-math-footnote"><strong>{ __( 'Note:', 'rank-math' ) }</strong> { __( 'The statistics that appear in the Rank Math Analytics module won’t match with the data from the Google Search Console as we only track posts and keywords that rank in the top 100 positions and have at least 1 click in the selected timeframe. We do this to help make decision-making easier and for faster data processing since this is the data you really need to prioritize your SEO efforts.', 'rank-math' ) }</p>
											</div>
										)
									} }
								/>
							)
						}
					) }
				</Switch>
			</div>
		</Router>
	)
}

export default App
