/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element'
import { Button, Dropdown, DateTimePicker } from '@wordpress/components'

const DatePicker = ( {
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
				<DateTimePicker
					is12Hour={ true }
					currentDate={ value }
					onChange={ onChange }
				/>
			) }
		/>
	)
}

export default DatePicker
