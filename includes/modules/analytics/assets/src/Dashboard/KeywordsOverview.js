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
