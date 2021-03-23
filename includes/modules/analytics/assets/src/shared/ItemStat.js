/**
 * External dependencies
 */
import { round, isUndefined } from 'lodash'
import classnames from 'classnames'

/**
 * Internal dependencies
 */
import humanNumber from '@helpers/humanNumber'

const ItemStat = ( { total = 0, difference = 0, revert = false } ) => {
	total = isUndefined( total ) ? 0 : total
	difference = isUndefined( difference ) ? 0 : difference
	revert = isUndefined( revert ) ? false : revert
	const isNegative = Math.abs( difference ) !== difference
	const diffClass = classnames( 'rank-math-item-difference', {
		up: ( ! revert && ! isNegative && difference > 0 ) || ( revert && isNegative ),
		down: ( ! revert && isNegative ) || ( revert && ! isNegative && difference > 0 ),
	} )

	return (
		<div className="rank-math-item-numbers">
			<strong className="text-large" title={ round( total, 2 ) }>
				{ humanNumber( total ) }
			</strong>
			<span className={ diffClass } title={ round( difference, 2 ) }>
				{ humanNumber( difference ) }
			</span>
		</div>
	)
}

export default ItemStat
