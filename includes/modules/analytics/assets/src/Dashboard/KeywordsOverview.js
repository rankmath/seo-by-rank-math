/**
 * External dependencies
 */
import { useHistory } from 'react-router-dom'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { createElement } from '@wordpress/element'
import { Button, TabPanel } from '@wordpress/components'

/**
 * Internal dependencies
 */
import AutomaticKeywordTracking from './AutomaticKeywordTracking'
import ManualKeywordTracking from './ManualKeywordTracking'

const KeywordsOverview = () => {
	const history = useHistory()
	const tabs = [
		{
			name: 'manualKeywordTracking',
			title: __( 'All Keywords', 'rank-math' ),
			view: ManualKeywordTracking,
			className: 'rank-math-tab rank-math-keywordTracking-tab',
		},
		{
			name: 'automaticKeywordTracking',
			title: __( 'Tracked Keywords', 'rank-math' ),
			view: AutomaticKeywordTracking,
			className: 'rank-math-tab rank-math-keywordTracking-tab',
		},
	]

	return (
		<div className="rank-math-box no-padding rank-math-keywords-overview">
			<a href="https://rankmath.com/kb/analytics/?utm_source=Plugin&utm_medium=Keywords%20Tab%20Dashboard%20KB&utm_campaign=WP#top-5-winning-and-losing-posts-pro" target="_blank" rel="noopener noreferrer" className="rank-math-tooltip">
				<em className="dashicons-before dashicons-editor-help analytics-dashicon"></em>
			</a>
			<TabPanel
				className="rank-math-tabs"
				activeClass="is-active"
				tabs={ tabs }
			>
				{ ( tab ) => (
					<div className={ 'rank-math-tab-content-' + tab.name }>
						{ createElement( tab.view ) }
					</div>
				) }
			</TabPanel>
			<Button isLink onClick={ () => history.push( '/keywords/1' ) }>
				{ __( 'Open Report', 'rank-math' ) }
			</Button>
		</div>
	)
}

export default KeywordsOverview
