/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { withFilters } from '@wordpress/components'

/**
 * Internal dependencies
 */
import List from '@scShared/woocommerce/List'
import { convertNumbers } from '../functions'

const AutomaticKeywordTracking = () => {
	const losingKeywords = {
		'best seo plugin wordpress': {
			query: 'best seo plugin wordpress',
			position: {
				total: 88,
				difference: -45,
			},
		},
		'seo plugins for wordpress': {
			query: 'seo plugins for wordpress',
			position: {
				total: 10,
				difference: -8,
			},
		},
		'wordpress seo plugin': {
			query: 'wordpress seo plugin',
			position: {
				total: 40,
				difference: -10,
			},
		},
		'best seo plugin': {
			query: 'best seo plugin',
			position: {
				total: 89,
				difference: -22,
			},
		},
		'wordpress seo plugins': {
			query: 'wordpress seo plugins',
			position: {
				total: 69,
				difference: -20,
			},
		},
	}
	const winningKeywords = {
		'best seo plugin': {
			query: 'best seo plugin',
			position: {
				total: 23,
				difference: 1,
			},
		},
		'wordpress seo plugin': {
			query: 'wordpress seo plugin',
			position: {
				total: 89,
				difference: 40,
			},
		},
		'best seo plugin wordpress': {
			query: 'best seo plugin wordpress',
			position: {
				total: 33,
				difference: 5,
			},
		},
		'seo plugins for wordpress': {
			query: 'seo plugins for wordpress',
			position: {
				total: 54,
				difference: 7,
			},
		},
		'wordpress seo plugins': {
			query: 'wordpress seo plugins',
			position: {
				total: 65,
				difference: 18,
			},
		},
	}

	return (
		<div className="rank-math-seo-score-overview">
			<div className="rank-math-box-grid blurred">
				<div className="col">
					<h4>{ __( 'Top Winning Keywords', 'rank-math' ) }</h4>
					<List
						className="rank-math-keywords-list keywords-winning"
						items={ convertNumbers( winningKeywords ) }
					/>
				</div>
				<div className="col">
					<h4>{ __( 'Top Losing Keywords', 'rank-math' ) }</h4>
					<List
						className="rank-math-keywords-list keywords-losing"
						items={ convertNumbers( losingKeywords ) }
					/>
				</div>
			</div>
		</div>
	)
}

export default withFilters( 'rankMath.analytics.automaticKeywordTracking' )( AutomaticKeywordTracking )
