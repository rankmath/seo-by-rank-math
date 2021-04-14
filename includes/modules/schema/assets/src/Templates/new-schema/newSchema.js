/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

export default () => (
	<div className="components-panel__body rank-math-custom-schema-wrapper">
		<img src={ rankMath.customSchemaImage } alt="" className="custom-schema" />

		<div id="rank-math-pro-cta" className="center">
			<div className="rank-math-cta-box blue-ticks width-60">
				<h3>{ __( 'Advanced Schema Builder', 'rank-math' ) }</h3>
				<ul>
					<li>{ __( 'Possibility to create 700+ Schema Types', 'rank-math' ) }</li>
					<li>{ __( 'Import Schema from ANY website', 'rank-math' ) }</li>
					<li>{ __( 'Create Advanced templates', 'rank-math' ) }</li>
				</ul>
				<a className="button button-primary is-green" href="https://rankmath.com/pricing/?utm_source=Plugin&utm_medium=Custom+Builder&utm_campaign=WP" rel="noreferrer noopener" target="_blank">{ __( 'Upgrade', 'rank-math' ) }</a>
			</div>
		</div>
	</div>
)
