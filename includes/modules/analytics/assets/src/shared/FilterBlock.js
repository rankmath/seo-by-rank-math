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
import Tooltip from '@scShared/Tooltip'

const FilterBlock = ( {
	type,
	title,
	score,
	tooltip,
	onClick,
	selected,
	tooltipClassName,
} ) => {
	const classes = classnames( 'score-filter-' + type, {
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
			<div className="filter-score text-large">{ score }</div>
		</Button>
	)
}

export default FilterBlock
