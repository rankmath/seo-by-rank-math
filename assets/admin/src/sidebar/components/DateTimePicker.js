/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element'
import { Dropdown, DateTimePicker } from '@wordpress/components'

/**
 * Internal dependencies
 */
import { IconButton } from '@helpers/deprecated'

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

					<IconButton
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
