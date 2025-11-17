/**
 * External dependencies
 */
import classNames from 'classnames'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import BetaBadge from './components/BetaBadge'
import Upgradable from './components/Upgradable'
import Footer from './components/Footer'
import getLink from '@helpers/getLink'

const goToPricingPage = () => {
	window.open(
		getLink( 'pro', 'Unlock Pro Module Box' )
	)
}

export default ( { id, module } ) => {
	const { isHidden, isPro, icon, title, desc } = module
	const classes = classNames( 'rank-math-box', {
		hidden: isHidden,
		'is-pro': isPro,
	} )

	return (
		<div
			key={ id }
			className={ classes }
			{ ...( isPro && ! rankMath.isPro
				? {
					onClick: goToPricingPage,
					'aria-hidden': true,
				}
				: {}
			) }
		>
			<i className={ 'rm-icon rm-icon-' + icon }></i>
			{ id === 'content-ai' && rankMath.contentAiPlan === 'free' && (
				<div className="rank-math-free-badge">{ __( 'Free', 'rank-math' ) }</div>
			) }

			<header>
				<h3>
					{ title }
					<BetaBadge { ...module } />
					<Upgradable { ...module } />
				</h3>
				<p
					dangerouslySetInnerHTML={ {
						__html: desc,
					} }
				/>
			</header>
			<Footer module={ module } />
		</div>
	)
}
