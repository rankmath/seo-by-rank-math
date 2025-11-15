/**
 * External dependencies
 */
import { round, isUndefined } from 'lodash'
import classnames from 'classnames'

/**
 * Internal dependencies
 */
import humanNumber from '@helpers/humanNumber'

const ItemStat = ( { total = 'n/a', difference = 'n/a', revert = false } ) => {
	let diffClass = 'rank-math-item-difference'
	let title = ''
	let diffTitle = ''
	if ( 'n/a' !== total && 'n/a' !== difference ) {
		total = isUndefined( total ) ? 0 : total
		title = round( total, 2 )
		total = humanNumber( total )

		difference = isUndefined( difference ) ? 0 : difference

		diffTitle = round( difference, 2 )

		revert = isUndefined( revert ) ? false : revert
		const isNegative = Math.abs( difference ) !== difference
		diffClass = classnames( diffClass, {
			up: ( ! revert && ! isNegative && difference > 0 ) || ( revert && isNegative ),
			down: ( ! revert && isNegative ) || ( revert && ! isNegative && difference > 0 ),
		} )
	}

	return (
		<div className="rank-math-item-numbers">
			<strong className="text-large" title={ title }>
				{ total }
			</strong>

			{
				'n/a' !== total && total != difference && (
					<span className={ diffClass } title={ diffTitle }>
						{ humanNumber( difference ) }
					</span>
				)
			}
		</div>
	)
}

export default ItemStat
