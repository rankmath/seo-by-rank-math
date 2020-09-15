/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

export default () => (
	<div className="components-panel__body rank-math-custom-schema-wrapper">
		<img src={ rankMath.customSchemaImage } alt="" className="custom-schema" />

		<div className="rank-math-pro-cta center">
			<div className="rank-math-cta-box">
				<h3>{ __( 'Extend or Create Advanced Schema', 'rank-math' ) }</h3>
				<ul>
					<li>{ __( 'Import from other websites or source code', 'rank-math' ) }</li>
					<li>{ __( 'Create advanced Schema templates', 'rank-math' ) }</li>
					<li>{ __( 'Save Schema templates for reuse', 'rank-math' ) }</li>
				</ul>
				<button className="button button-primary">{ __( 'Pro Coming Soon', 'rank-math' ) }</button>
			</div>
		</div>
	</div>
)
