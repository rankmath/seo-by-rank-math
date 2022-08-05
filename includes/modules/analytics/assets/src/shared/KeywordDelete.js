/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment } from '@wordpress/element'
import { Button, withFilters } from '@wordpress/components'
import { doAction } from '@wordpress/hooks'

const KeywordDelete = ( props ) => {
	const { sequence, query } = props
	return (
		<Fragment>
			{ sequence }
			<Button
				className="button button-secondary button-small add-keyword delete"
				title={ __( 'Delete from Keyword Manager', 'rank-math' ) }
				onClick={ () => doAction( 'rank_math_remove_keyword', query ) }
			>
				<i className="rm-icon rm-icon-trash" />
			</Button>
		</Fragment>
	)
}

export default withFilters( 'rankMath.analytics.keywordDelete' )( KeywordDelete )
