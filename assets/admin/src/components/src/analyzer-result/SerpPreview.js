/**
 * SEO Analyzer Google Preview.
 *
 * @param {Object} data SERP data
 */
export default ( data ) => {
	return (
		<div className="serp-preview">
			<div className="serp-preview-body">
				<div className="serp-url-wrapper">
					<img
						src={ data.favicon }
						width={ 16 }
						height={ 16 }
						className="serp-favicon"
						alt="favicon"
					/>

					<span className="serp-url">{ data.url }</span>
				</div>

				<h5 className="serp-title">{ data.title }</h5>

				<p className="serp-description">{ data.description }</p>
			</div>
		</div>
	)
}
