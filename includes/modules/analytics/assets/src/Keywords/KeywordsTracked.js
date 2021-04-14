/**
 * External dependencies
 */
import { map } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment } from '@wordpress/element'
import { withFilters } from '@wordpress/components'

/**
 * Internal dependencies
 */
import TableCard from '@scShared/woocommerce/Table'
import { processRows } from '../functions'

const KeywordsTracked = () => {
	const headers = [
		{
			key: 'sequenceDelete',
			label: __( '#', 'rank-math' ),
			required: true,
			cellClassName: 'rank-math-col-index',
		},
		{
			key: 'query',
			label: __( 'Keywords', 'rank-math' ),
			required: true,
			cellClassName: 'rank-math-col-title',
		},
		{
			key: 'impressions',
			label: __( 'Impressions', 'rank-math' ),
			cellClassName: 'rank-math-col-impressions',
		},
		{
			key: 'clicks',
			label: __( 'Clicks', 'rank-math' ),
			cellClassName: 'rank-math-col-click',
		},
		{
			key: 'ctr',
			label: __( 'Avg. CTR', 'rank-math' ),
			cellClassName: 'rank-math-col-click',
		},
		{
			key: 'position',
			label: __( 'Position', 'rank-math' ),
			cellClassName: 'rank-math-col-position',
		},
		{
			key: 'positionHistory',
			label: __( 'Position History', 'rank-math' ),
			cellClassName: 'rank-math-col-position-history',
		},
	]

	const tableSummary = [
		{ label: __( 'Used', 'rank-math' ), value: 5 },
		{
			label: __( 'Remaining', 'rank-math' ),
			value: 45,
		},
		{ label: __( 'Allowed', 'rank-math' ), value: 50 },
	]

	const graph1 = [
		{
			date: '2020-07-15',
			position: 3,
		},
		{
			date: '2020-07-25',
			position: 5,
		},
		{
			date: '2020-07-31',
			position: 1,
		},
		{
			date: '2020-08-13',
			position: 1,
		},
		{
			date: '2020-08-20',
			position: 7,
		},
		{
			date: '2020-08-28',
			position: 7,
		},
	]

	const graph2 = [
		{
			date: '2020-07-03',
			position: 8,
		},
		{
			date: '2020-08-06',
			position: 2,
		},
		{
			date: '2020-08-28',
			position: 2,
		},
	]

	const rows = {
		'best seo plugin wordpress': {
			query: 'best seo plugin wordpress',
			clicks: {
				total: 21,
				difference: 10,
			},
			impressions: {
				total: 2030,
				difference: -200,
			},
			position: {
				total: 3,
				difference: 1,
			},
			ctr: {
				total: 0.63157894736842102,
				difference: 0.23,
			},
			graph: graph1,
		},
		'seo plugins for wordpress': {
			query: 'seo plugins for wordpress',
			clicks: {
				total: 40,
				difference: -15,
			},
			impressions: {
				total: 125,
				difference: 22,
			},
			position: {
				total: 4,
				difference: -1,
			},
			ctr: {
				total: 3.25,
				difference: -0.2,
			},
			graph: graph2,
		},
		'wordpress seo plugin': {
			query: 'wordpress seo plugin',
			clicks: {
				total: 60,
				difference: -2,
			},
			impressions: {
				total: 2222,
				difference: -22,
			},
			position: {
				total: 5,
				difference: 15,
			},
			ctr: {
				total: 0.55,
				difference: -0.1,
			},
			graph: graph1,
		},
		'best seo plugin': {
			query: 'best seo plugin',
			clicks: {
				total: 724,
				difference: 23,
			},
			impressions: {
				total: 2159,
				difference: 329,
			},
			position: {
				total: 1,
				difference: 0,
			},
			ctr: {
				total: 20.69,
				difference: 0.07,
			},
			graph: graph2,
		},
		'wordpress seo plugins': {
			query: 'wordpress seo plugins',
			clicks: {
				total: 10000,
				difference: 5000,
			},
			impressions: {
				total: 300000,
				difference: -23300,
			},
			position: {
				total: 5,
				difference: 2,
			},
			ctr: {
				total: 0.59,
				difference: -0.1,
			},
			graph: graph1,
		},
	}

	return (
		<Fragment>
			<div className="rank-math-keyword-table keyword-manager">
				<div id="rank-math-pro-cta" className="center">
					<div className="rank-math-cta-box blue-ticks width-50">
						<h3>{ __( 'Your Own Keyword Manager', 'rank-math' ) }</h3>
						<ul>
							<li>{ __( 'Track your performance for your target keywords', 'rank-math' ) }</li>
							<li>{ __( 'Monitor impressions, clicks, and position history', 'rank-math' ) }</li>
							<li>{ __( 'No additional monthly subscriptions for third-party tools', 'rank-math' ) }</li>
						</ul>
						<a href="https://rankmath.com/pricing/?utm_source=Plugin&utm_medium=Keyword%20Manager%20Table&utm_campaign=WP" target="_blank" rel="noreferrer" className="button button-primary is-green">{ __( 'Upgrade', 'rank-math' ) }</a>
					</div>
				</div>
				<TableCard
					className="rank-math-table blurred"
					title={ __( 'Keyword Manager', 'rank-math' ) }
					headers={ headers }
					rows={ processRows( rows, map( headers, 'key' ) ) }
					rowsPerPage={ 20 }
					totalRows={ 20 }
					summary={ tableSummary }
				/>
			</div>
		</Fragment>
	)
}

export default withFilters( 'rankMath.analytics.keywordManager' )( KeywordsTracked )
