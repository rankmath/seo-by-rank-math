/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { dispatch } from '@wordpress/data'
import { createElement, Fragment } from '@wordpress/element'

/**
 * Internal dependencies
 */
import TabPanel from '@components/TabPanel'
import Keywords from './components/Keywords'
import Questions from './components/Questions'
import Links from './components/Links'

/**
 * @description Tab on select
 *
 * @param {string} tabName Tab name.
 */
const TabonSelect = ( tabName ) => {
	if ( 'social' === tabName ) {
		dispatch( 'rank-math' ).toggleSnippetEditor( true )
	}
}

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

const ContentAIPanel = ( props ) => {
	return (
		<TabPanel
			className="rank-math-contentai-tabs"
			activeClass="is-active"
			tabs={ getTabs() }
			onSelect={ TabonSelect }
			data={ props }
		>
			{ ( tab ) => (
				<div className={ 'rank-math-contentai-tab-content-' + tab.name }>
					{ createElement( tab.view, props ) }
				</div>
			) }
		</TabPanel>
	)
}

export default ContentAIPanel
