/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment } from '@wordpress/element'
import { Button, withFilters } from '@wordpress/components'

const KeywordButton = ( props ) => {
	const { sequence } = props
	return (
		<Fragment>
			{ sequence }
			<Button
				className="button button-secondary button-small add-keyword"
				href="https://rankmath.com/pricing/?utm_source=Plugin&utm_medium=Add%20KW%20Button&utm_campaign=WP"
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
