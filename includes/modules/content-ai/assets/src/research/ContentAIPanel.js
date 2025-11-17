/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { createElement, Fragment } from '@wordpress/element'

/**
 * Internal dependencies
 */
import TabPanel from '@components/TabPanel'
import Keywords from './components/keywords'
import Questions from './components/Questions'
import Links from './components/Links'

/**
 * Content AI tabs.
 */
const getTabs = () => {
	return [
		{
			name: 'keywords',
			title: (
				<Fragment>
					<span>{ __( 'Keywords', 'rank-math' ) }</span>
				</Fragment>
			),
			view: Keywords,
			className: 'rank-math-keywords-tab',
		},
		{
			name: 'questions',
			title: (
				<Fragment>
					<span>{ __( 'Questions', 'rank-math' ) }</span>
				</Fragment>
			),
			view: Questions,
			className: 'rank-math-questions-tab',
		},
		{
			name: 'links',
			title: (
				<Fragment>
					<span>{ __( 'Links', 'rank-math' ) }</span>
				</Fragment>
			),
			view: Links,
			className: 'rank-math-recommended-links-tab',
		},
	]
}

export default ( props ) => {
	return (
		<TabPanel
			className="rank-math-contentai-tabs"
			activeClass="is-active"
			tabs={ getTabs() }
			data={ props }
		>
			{ ( tab, index ) => (
				<div className={ 'rank-math-contentai-tab-content-' + tab.name } key={ index }>
					{ createElement( tab.view, props ) }
				</div>
			) }
		</TabPanel>
	)
}
