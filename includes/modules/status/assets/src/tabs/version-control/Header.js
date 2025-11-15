/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

export default ( { title, description, warning = '' } ) => {
	return (
		<>
			<header>
				<h3>{ title }</h3>
			</header>
			<p>{ description }</p>
			{
				warning &&
				<p className="description warning">
					<strong>
						<span className="warning">{ __( 'Warning: ', 'rank-math' ) }</span>

						{ warning }
					</strong>
				</p>
			}
		</>
	)
}
