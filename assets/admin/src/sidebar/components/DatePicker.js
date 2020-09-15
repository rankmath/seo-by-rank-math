/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element'
import { Dropdown, DatePicker } from '@wordpress/components'

/**
 * Internal dependencies
 */
import { IconButton } from '@helpers/deprecated'

const EnhancedDatePicker = ( {
	position = 'middle right',
	value,
	onChange,
	children,
} ) => {
	return (
		<Dropdown
			position={ position }
			className="rank-math-datepicker"
			contentClassName="rank-math-datepicker__dialog"
			renderToggle={ ( { onToggle, isOpen } ) => (
				<Fragment>
					{ children }

					<IconButton
						icon="calendar-alt"
						onClick={ onToggle }
						aria-expanded={ isOpen }
					/>
				</Fragment>
			) }
			renderContent={ () => (
				<DatePicker
					currentDate={ value.split( 'T' )[ 0 ] }
					onChange={ ( date ) => {
						onChange( date.split( 'T' )[ 0 ] )
					} }
				/>
			) }
		/>
	)
}

export default EnhancedDatePicker
