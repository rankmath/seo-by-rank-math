/*!
 * Rank Math - Wizard
 *
 * @version 0.9.0
 * @author  RankMath
 */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready'
import { createRoot } from '@wordpress/element'

/**
 * Internal dependencies
 */
import Header from './wizard/views/Header'
import Registration from './wizard/Registration'

domReady( () => {
	const root = createRoot(
		document.getElementById( 'rank-math-wizard-wrapper' )
	)

	root.render(
		<>
			<Header />
			<Registration />
		</>
	)
} )
