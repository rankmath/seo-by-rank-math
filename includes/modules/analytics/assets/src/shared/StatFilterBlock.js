/**
 * External dependencies
 */
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components'

/**
 * Internal dependencies
 */
import ItemStat from '@scShared/ItemStat'
import Tooltip from '@scShared/Tooltip'

const StatFilterBlock = ( {
	type,
	title,
	tooltip,
	onClick,
	selected,
	data,
	className,
	tooltipClassName
} ) => {
	const classes = classnames( 'stat-filter-' + type, className, {
		'is-active': selected[ type ],
	} )
	return (
		<Button
			className={ classes }
			onClick={ () => {
				onClick( {
					...selected,
					[ `${ type }` ]: ! selected[ type ],
				} )
			} }
		>
			<h4>
				{ title }
				<Tooltip className={ tooltipClassName }>{ tooltip }</Tooltip>
			</h4>
			<ItemStat { ...data } />
		</Button>
	)
}

export default StatFilterBlock
