/**
 * External dependencies
 */
import { forEach } from 'lodash'

/**
 * WordPress dependencies
 */
import { TabPanel } from '@wordpress/components'
import { useEffect, useRef, forwardRef, Ref } from '@wordpress/element'

/**
 * Internal dependencies
 */
import './scss/StepsProgress.scss'

/**
 * Steps Progress Component
 *
 * @param {Object}   props           Component props.
 * @param {Array}    props.tabs      Array of tab objects. Each tab object should contain at least a name and a title.
 * @param {Function} props.children  Renders the tabviews.
 * @param {string}   props.className Class name for additional styling.
 * @param {Ref}      ref             Ref object for accessing an instance of the component.
 */
const StepsProgress = ( {
	tabs = [],
	children = () => {},
	className = '',
	...additionalProps
}, ref ) => {
	const refInstance = useRef( null )
	const tabRef = ref ?? refInstance

	const props = {
		...additionalProps,
		tabs,
		children,
		ref: tabRef,
		className: `rank-math-steps-progress ${ className }`,
	}

	useEffect( () => {
		const tabItems = Array.from(
			tabRef.current.querySelectorAll( '.components-tab-panel__tabs-item' )
		)
		const activeTab = tabRef.current.querySelector(
			'.components-tab-panel__tabs-item.is-active'
		)

		if ( activeTab ) {
			let isActiveFound = false

			forEach( tabItems, ( tab ) => {
				// Check if the current tab is the active tab
				if ( ! isActiveFound ) {
					isActiveFound = tab === activeTab
				}

				// Toggle the 'is-done' class based on whether the tab is before or after the active tab
				tab.classList.toggle( 'is-done', ! isActiveFound )
			} )
		}
	} )

	return <TabPanel { ...props } />
}

export default forwardRef( StepsProgress )
