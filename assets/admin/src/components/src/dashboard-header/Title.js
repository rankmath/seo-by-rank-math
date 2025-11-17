export default () => {
	return (
		<h1 className="rank-math-logo-text">
			Rank Math SEO
			<span
				dangerouslySetInnerHTML={ {
					__html: rankMath.dashboardHeader.proBadge,
				} }
			/>
		</h1>
	)
}
