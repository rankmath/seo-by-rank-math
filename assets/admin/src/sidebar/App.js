/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { dispatch } from '@wordpress/data'
import { applyFilters } from '@wordpress/hooks'
import { createElement, Fragment } from '@wordpress/element'

/**
 * Internal dependencies
 */
import TabPanel from '@components/TabPanel'
import Social from '@components/Social/Social'
import General from '@components/General/General'
import Advanced from '@components/Advanced/Advanced'

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

const getTabs = () => {
	const tabs = []

	if ( rankMath.canUser.general ) {
		tabs.push( {
			name: 'general',
			title: (
				<Fragment>
					<i
						className="rm-icon rm-icon-settings"
						title={ __( 'General', 'rank-math' ) }
					></i>
					<span>{ __( 'General', 'rank-math' ) }</span>
				</Fragment>
			),
			view: General,
			className: 'rank-math-general-tab',
		} )
	}

	if ( rankMath.canUser.advanced ) {
		tabs.push( {
			name: 'advanced',
			title: (
				<Fragment>
					<i
						className="rm-icon rm-icon-toolbox"
						title={ __( 'Advanced', 'rank-math' ) }
					></i>
					<span>{ __( 'Advanced', 'rank-math' ) }</span>
				</Fragment>
			),
			view: Advanced,
			className: 'rank-math-advanced-tab',
		} )
	}

	if ( rankMath.canUser.social ) {
		tabs.push( {
			name: 'social',
			title: (
				<Fragment>
					<i
						className="rm-icon rm-icon-social"
						title={ __( 'Social', 'rank-math' ) }
					></i>
					<span>{ __( 'Social', 'rank-math' ) }</span>
				</Fragment>
			),
			view: Social,
			className: 'rank-math-social-tab',
		} )
	}

	return applyFilters( 'rank_math_sidebar_tabs', tabs )
}

const SidebarTabPanel = () => {
	return (
		<TabPanel
			className="rank-math-tabs"
			activeClass="is-active"
			tabs={ getTabs() }
			onSelect={ TabonSelect }
		>
			{ ( tab ) => (
				<div className={ 'rank-math-tab-content-' + tab.name }>
					{ createElement( tab.view ) }
				</div>
			) }
		</TabPanel>
	)
}

export default SidebarTabPanel
