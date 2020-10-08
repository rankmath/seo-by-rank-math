const Tooltip = ( { children } ) => {
	return (
		<span className="rank-math-tooltip">
			<em className="dashicons-before dashicons-editor-help" />
			<span>{ children }</span>
		</span>
	)
}

export default Tooltip
