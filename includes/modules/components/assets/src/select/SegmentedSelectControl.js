/**
 * Wordpress dependencies
 */
import {
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
	Disabled
} from '@wordpress/components';

export default ({
	disabled = false,
	toggleOptions,
	value,
	onChange
}) => {
	const toggleGroupControlProps = {
		value,
		onChange,
		__nextHasNoMarginBottom: true,
		isBlock: true,
		"aria-disabled": disabled,
	}

	return (
		<Disabled isDisabled={disabled}>
			<div className="segemented-select-control">
				<ToggleGroupControl
					{...toggleGroupControlProps}
				>
					{toggleOptions.map(({ label, value }) => (
						<React.Fragment key={value}>
							<ToggleGroupControlOption
								label={label}
								value={value}
							/>
						</React.Fragment>
					))}
				</ToggleGroupControl>
			</div>
		</Disabled>
	);
}
