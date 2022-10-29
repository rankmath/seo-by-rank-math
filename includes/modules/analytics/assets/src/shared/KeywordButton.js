/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment } from '@wordpress/element'
import { Button, withFilters } from '@wordpress/components'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'

const KeywordButton = ( props ) => {
	const { sequence } = props
	return (
		<Fragment>
			{ sequence }
			<Button
				className="button button-secondary button-small add-keyword"
				href={ getLink( 'pro', 'Add KW Button' ) }
				target="_blank"
			>
				<div className="rank-math-tooltip">
					<i className="rm-icon rm-icon-plus" />
					<span>{ __( 'Pro Feature', 'rank-math' ) }</span>
				</div>
			</Button>
		</Fragment>
	)
}

export default withFilters( 'rankMath.analytics.keywordAddRemoveButton' )( KeywordButton )
