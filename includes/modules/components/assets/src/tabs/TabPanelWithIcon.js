export default ({ title, icon }) => {
	return (
		<div className="panel-has-icon">
			<i className={icon}></i>
			<h1 className="title">{title}</h1>
		</div>
	)
}