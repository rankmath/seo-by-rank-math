export default ({ status = 'off' }) => {
	const iconClasses = {
		green: 'is-green',
		red: 'is-red',
		yellow: 'is-yellow',
		off: 'is-off',
	};
	const labelNames = {
		green: 'Green',
		red: 'Red',
		yellow: 'Yellow',
		off: 'Off',
	};

	const lightIndicatorIconClass = iconClasses[status];
	const lightIndicatorLabel = labelNames[status];
	const groupedIconClassNames = `rm-icon-trash light-indicator__icon ${lightIndicatorIconClass}`

	return (
		<div className='light-indicator'>
			<i className={groupedIconClassNames}></i>

			<span className='light-indicator__label'>{lightIndicatorLabel}</span>
		</div>
	)
}
