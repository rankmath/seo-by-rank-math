/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element'
import { Button, Dropdown, DatePicker } from '@wordpress/components'

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

					<Button
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
