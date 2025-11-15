/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'

export default () => {
	return (
		<div className="header">
			<div className="logo text-center">
				<a href={ getLink( 'seo-suite', 'SW Logo' ) } target="_blank" rel="noreferrer">
					<img src={ rankMath.logo } alt="Rank Math SEO" width="245" />
				</a>
			</div>
		</div>
	)
}
